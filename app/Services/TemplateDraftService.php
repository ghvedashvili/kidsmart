<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Theme;
use App\Models\Topic;

/**
 * „ჭკვიანი ოსტატი“ — ჩვეულებრივი ტექსტით ჩასმული ამოცანებიდან შაბლონის მონახაზი, AI-ს გარეშე.
 *
 * რას აკეთებს თითოეულ ამოცანაზე:
 *  1. რიცხვებს (ციფრით და სიტყვით: „ოთხი“, „ორ-ორი“, „ხუთლარიანი“, „2 ათეული“) ცვლადებად აქცევს — A, B, C…
 *  2. არჩეული თემატიკის ცვლადებს (მაგ. სახელს) ჩასვამს {{NAME}}-ით.
 *  3. ფორმულას პოულობს გადარჩევით: ცვლადების + − × ÷ კომბინაციებიდან ირჩევს ისეთს, რომლის
 *     პასუხიც სავარაუდო პასუხებშია (ა) ბ) გ) დ) ან „პასუხი: …“), და ალაგებს საკვანძო სიტყვებით
 *     („დარჩა“ → −, „ერთად“ → +, „ნახევარი“ → ÷2, „ორ-ორი“ → ×…).
 *  4. დანარჩენ ვარიანტებს არასწორი პასუხის ფორმულებად აქცევს (ტიპური შეცდომები).
 *  5. ამატებს პირობებს (გამოკლებისას A > B, გაყოფისას A ÷ B უნაშთოდ) და დიაპაზონებს.
 *
 * შედეგი იგივე JSON-ია, რასაც AI აბრუნებს — შემდეგ TemplateImportService იგივე შემოწმებებს ატარებს.
 * „_draft“ ველში ოსტატის შენიშვნებია (ალტერნატიული ფორმულები და ა.შ.).
 */
class TemplateDraftService
{
    private const VAR_NAMES = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'K', 'L', 'M', 'P', 'Q', 'R', 'S', 'T'];
    /** ტექსტური პასუხისთვის („7 და 7“): X — გამოთვლილი რიცხვი, დანარჩენი — X ± k */
    private const PAIR_MAIN = 'X';
    private const PAIR_OFFSETS = ['Y', 'Z', 'W', 'V', 'U'];
    private const MAX_LEAVES = 6;
    private const MAX_ITEMS = 8;
    private const MAX_VALUE = 100000;

    /** ძირითადი რიცხვითი სახელები (სახელობითი) */
    private const UNITS = [
        1 => 'ერთი', 2 => 'ორი', 3 => 'სამი', 4 => 'ოთხი', 5 => 'ხუთი', 6 => 'ექვსი', 7 => 'შვიდი', 8 => 'რვა', 9 => 'ცხრა', 10 => 'ათი',
        11 => 'თერთმეტი', 12 => 'თორმეტი', 13 => 'ცამეტი', 14 => 'თოთხმეტი', 15 => 'თხუთმეტი', 16 => 'თექვსმეტი',
        17 => 'ჩვიდმეტი', 18 => 'თვრამეტი', 19 => 'ცხრამეტი',
    ];
    private const TENS = [20 => 'ოცი', 40 => 'ორმოცი', 60 => 'სამოცი', 80 => 'ოთხმოცი'];
    /** ბრუნვის ნიშნები, რომლებიც {{VAR}}-ს დეფისით შეიძლება მიეწეროს */
    private const CASE_SUFFIXES = ['', 'ს', 'მ', 'მა', 'ის', 'ით', 'ზე', 'ში', 'თან', 'ისთვის', 'ც', 'საც', 'მაც'];
    private const SUFFIXES = ['ს', 'მა', 'მ', 'ის', 'ით', 'ად', 'ზე', 'ში', 'ჯერ', 'იდან', 'მდე', 'თან'];

    /** სიტყვები, რომლებიც ფორმულაში მუდმივ რიცხვს ამატებენ (ტექსტში რჩება) */
    /** სიტყვა => [რიცხვი, მოქმედება] — მუდმივა მხოლოდ ამ მოქმედებაში გამოიყენება (ნახევარი — მხოლოდ გამყოფად) */
    private const IMPLICIT = [
        'სამივე' => [3, '*'], 'ორივე' => [2, '*'], 'ოთხივე' => [4, '*'], 'ხუთივე' => [5, '*'],
        'აორმაგ' => [2, '*'], 'ასამმაგ' => [3, '*'], 'ორჯერ მეტ' => [2, '*'], 'სამჯერ მეტ' => [3, '*'],
        'ნახევარ' => [2, '/'], 'მესამედ' => [3, '/'], 'მეოთხედ' => [4, '/'], 'ორჯერ ნაკლებ' => [2, '/'],
    ];

    /** ერთეულები: [დიდი, პატარა, რამდენი პატარაა დიდში] — ორივე თუ ტექსტშია, ფორმულას ეს მუდმივაც ემატება */
    private const UNIT_PAIRS = [
        ['/ლარ/u', '/თეთრ/u', 100],
        ['/(?<!სანტი|მილი|კილო)მეტრ/u', '/სანტიმეტრ/u', 100],
        ['/კილომეტრ/u', '/(?<!სანტი|მილი|კილო)მეტრ/u', 1000],
        ['/სანტიმეტრ/u', '/მილიმეტრ/u', 10],
        ['/კილოგრამ/u', '/(?<!კილო)გრამ/u', 1000],
        ['/(?<!მილი)ლიტრ/u', '/მილილიტრ/u', 1000],
        ['/საათ/u', '/წუთ/u', 60],
        ['/წუთ/u', '/წამ/u', 60],
        ['/დღე|დღის/u', '/საათ/u', 24],
        ['/კვირ/u', '/დღე|დღის/u', 7],
        ['/წელ|წლის|წლით/u', '/თვე|თვის/u', 12],
    ];

    /** საკვანძო სიტყვები → მოსალოდნელი მოქმედება */
    private const HINTS = [
        '+' => ['ით მეტ', 'სულ', 'ერთად', 'მივიდა', 'მოვიდა', 'მოფრინდა', 'დაემატა', 'მიუმატ', 'შეკრიბ', 'შევკრებ', 'კიდევ', 'ჯამ', 'მოიტანა', 'აჩუქ', 'მიიღო', 'დაუბრუნ', 'დააგროვ', 'მოკრიფ', 'გადააწყო', 'გადადო', 'გადაიტანა'],
        '-' => ['ით ნაკლებ', 'დარჩა', 'დარჩენ', 'წავიდა', 'ნაკლებ', 'მისცა', 'მისცეს', 'შეჭამ', 'გამოაკლ', 'დაკარგ', 'გაიყიდ', 'გაყიდ', 'აიღო', 'აუღია', 'გაფრინდა', 'დახარჯ', 'შეიძინა', 'იყიდა', 'უყიდა', 'ამოხსნა', 'გადააწყო', 'გადადო', 'გადაიტანა', 'რამდენით', 'გატყდა', 'წაიღო'],
        '*' => ['ჯერ', 'თითოეულ', 'ყოველ', 'აორმაგ', 'ასამმაგ'],
        '/' => ['თანაბრად', 'გაყო', 'გაუყო', 'ნახევარ', 'მესამედ', 'მეოთხედ', 'გაუნაწილ', 'ყიდვა', 'ასეთი', 'რამდენ ჯგუფ'],
    ];

    private ?array $numberForms = null;
    private bool $sameAgain = false;

    // ─────────────────────────── ტექსტი → JSON ───────────────────────────

    /** @return array{questions: array, skipped: array} */
    public function draft(string $raw, ?int $topicId, ?int $themeId): array
    {
        $topic = $topicId ? Topic::with('grade')->find($topicId) : null;
        $theme = $themeId ? Theme::find($themeId) : null;
        $maxLevel = (int) ($topic?->grade?->max_level ?: Grade::DEFAULT_MAX_LEVEL);

        $questions = [];
        $skipped = [];
        foreach ($this->splitBlocks($raw) as $block) {
            $q = $this->draftOne($block, $theme, $maxLevel);
            if (isset($q['skip'])) { $skipped[] = ['text' => $q['text'], 'reason' => $q['skip']]; continue; }
            if ($topic) $q['topic_id'] = $topic->id;
            if ($theme) $q['theme_id'] = $theme->id;
            $questions[] = $q;
        }
        return ['questions' => $questions, 'skipped' => $skipped];
    }

    /** ამოცანებად დაყოფა: „ამოცანა №N“ / „№N“ / „1.“ ან ცარიელი ხაზები */
    public function splitBlocks(string $raw): array
    {
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);
        $raw = preg_replace('/[ \t]*(ამოცანა\s*№\s*\d+[^\n]*|№\s*\d+[.)]?)/u', "\n\x01\n", $raw);
        if (! str_contains($raw, "\x01")) {
            $raw = preg_replace('/^\s*\d{1,2}\s*[.)]\s+/mu', "\x01", $raw);
        }
        $parts = str_contains($raw, "\x01") ? explode("\x01", $raw) : preg_split('/\n\s*\n/u', $raw);
        return array_values(array_filter(array_map('trim', $parts), fn ($p) => mb_strlen($p) >= 10));
    }

    public function draftOne(string $block, ?Theme $theme, int $maxLevel = 3): array
    {
        $notes = [];
        $warnings = [];

        // ── პასუხები: „ა) ოთხი ბ) ათი …“ და „პასუხი: 4“ ──
        [$text, $options, $answer, $rawOpts, $answerRaw] = $this->extractOptions($block);
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        if ($text === '') return ['skip' => 'ტექსტი ცარიელია.', 'text' => $block];

        // ── „სამი ყველაზე ძვირი“ → topsum ──
        $sortFn = null; $sortK = null;
        if (preg_match('/(\S+)\s+ყველაზე\s+(ძვირ|დიდ|მეტ|გრძელ|მძიმ|მაღალ|იაფ|პატარ|მცირ|მოკლე|მსუბუქ|დაბალ)/u', $text, $sm)) {
            $k = $this->wordValue($sm[1]);
            if ($k !== null) {
                $sortK = ['word' => $sm[1], 'value' => $k];
                $sortFn = in_array($sm[2], ['იაფ', 'პატარ', 'მცირ', 'მოკლე', 'მსუბუქ', 'დაბალ'], true) ? 'botsum' : 'topsum';
            }
        }

        // ── სიტყვები, რომლებიც ცვლადი არ უნდა გახდეს ──
        // „ორი ერთმანეთის ტოლი ციფრი“ — ამოცანის პირობაა (მუდმივა), „რომელი ორი ციფრი“ — კითხვის ნაწილი
        $skip = [];
        $fixedConsts = [];
        if ($sortK) $skip[] = $sortK['word'];
        if (preg_match_all('/(?<![\x{10D0}-\x{10FF}])([\x{10D0}-\x{10FF}]+)\s+(?:ერთმანეთის\s+)?ტოლი/u', $text, $tm)) {
            foreach ($tm[1] as $w) if (($v = $this->wordValue($w)) !== null) { $skip[] = $w; $fixedConsts[] = ['v' => $v, 'op' => null]; }
        }
        if (preg_match_all('/(?:რომელი|რომელ|რა)\s+([\x{10D0}-\x{10FF}]+)/u', $text, $rm)) {
            foreach ($rm[1] as $w) if ($this->wordValue($w) !== null) $skip[] = $w;
        }

        // ── რიცხვები → ცვლადები ──
        [$tpl, $vars] = $this->replaceNumbers($text, $skip);
        if (! $vars) {
            return ['skip' => 'რიცხვები ვერ ვიპოვე — ეს ამოცანა შაბლონად ვერ იქცევა (ან ჩვეულებრივ კითხვად დაამატე).', 'text' => $text];
        }

        // ── თემატიკის ცვლადები (სახელები…) ──
        [$tpl, $themeNotes, $missing] = $this->replaceThemeWords($tpl, $theme);
        array_push($notes, ...$themeNotes);

        $values = array_column($vars, 'value', 'name');
        $lower = mb_strtolower($text);

        // ── ფორმულა ──
        $formula = null; $tree = null; $alts = []; $usedOptions = [];
        if ($sortFn) {
            $names = array_column($vars, 'name');
            $formula = $sortFn . '(' . $sortK['value'] . ', ' . implode(', ', $names) . ')';
            $correct = $this->evalSort($sortFn, $sortK['value'], array_values($values));
            if ($answer !== null && $answer !== $correct) $warnings[] = "{$formula} = {$correct}, მითითებული პასუხი კი {$answer} — გადაამოწმე.";
            if ($options && ! in_array($correct, $options, true)) $warnings[] = "{$formula} = {$correct} — ეს რიცხვი პასუხის ვარიანტებში არ არის.";
            $answerValue = $answer ?? $correct;
            $notes[] = "„{$sortK['word']} ყველაზე …“ → {$sortFn}: " . ($sortFn === 'topsum' ? 'ყველაზე დიდების' : 'ყველაზე პატარების') . ' ჯამი.';
        } else {
            $consts = array_merge($fixedConsts, $this->implicitConstants($lower), $this->unitConstants($lower));
            if (count($vars) + count($consts) > self::MAX_ITEMS) {
                return ['skip' => 'ძალიან ბევრი რიცხვია ავტომატური ფორმულისთვის — ეს ამოცანა AI-ით ან ხელით შეადგინე.', 'text' => $text];
            }
            foreach ($consts as $c) if ($c['op'] === null && $c['v'] >= 10) $notes[] = "ერთეულის გადაყვანა: {$c['v']} (მაგ. 1 ლარი = 100 თეთრი) — ფორმულაში მუდმივად.";
            $this->sameAgain = (bool) preg_match('/ასეთი|ასეთ /u', $lower);
            $cands = $this->search($vars, $consts, $this->hintOps($lower, $vars), $this->questionNoun($text));

            // „7 და 7“ — ვარიანტები რიცხვების წყვილებია → ტექსტური პასუხი
            $pairs = $this->pairOptions($rawOpts, $answerRaw);
            $targets = $answer !== null ? [$answer] : $options;
            if ($pairs) {
                $targets = [];
                foreach ($pairs['list'] as [$pa, $pb]) { $targets[] = $pa; $targets[] = $pb; }
                $targets = array_values(array_unique($targets));
                if ($pairs['answer']) $targets = array_values(array_unique([$pairs['answer'][0], $pairs['answer'][1]]));
            }
            if (! $targets) $warnings[] = 'პასუხი და ვარიანტები არ იყო მითითებული — ფორმულა მხოლოდ სიტყვებით გამოვიცანი. აუცილებლად გადაამოწმე (ან ტექსტში დაამატე „პასუხი: …“).';
            $ranked = [];
            foreach ($cands as $c) {
                if ($targets && ! in_array($c['v'], $targets, true)) continue;
                $ranked[] = $c;
            }
            usort($ranked, fn ($a, $b) => $b['final'] <=> $a['final'] ?: strlen($a['e']) <=> strlen($b['e']));
            if (! $ranked) {
                return ['skip' => 'ვერცერთი + − × ÷ კომბინაცია ვერ იძლევა ვარიანტებიდან რომელიმე პასუხს — ამოცანა უფრო რთულია, AI-ით ან ხელით შეადგინე.', 'text' => $text];
            }
            $best = $ranked[0];
            $formula = $best['e']; $tree = $best['t'];
            $answerValue = $best['v'];
            // ალტერნატივები — სხვა პასუხით ან სხვა ფორმით
            $seen = [$best['e'] => true];
            foreach (array_slice($ranked, 1) as $c) {
                if (isset($seen[$c['e']]) || count($alts) >= 3) continue;
                if ($c['final'] < $best['final'] - 3) break;
                $seen[$c['e']] = true;
                $alts[] = $c;
            }
            if ($alts) {
                $warnings[] = 'ფორმულა გამოცნობილია — ' . $formula . ' = ' . $answerValue . '. სხვა შესაძლო: '
                    . implode('; ', array_map(fn ($c) => $c['e'] . ' = ' . $c['v'], $alts)) . '. თუ სხვა სწორია, JSON-ში correct_formula შეცვალე.';
            } else {
                $notes[] = 'ფორმულა გამოცნობილია: ' . $formula . ' = ' . $answerValue . '.';
            }
            $unused = array_filter($vars, fn ($v) => ! preg_match('/\b' . $v['name'] . '\b/', $formula));
            if ($unused) $notes[] = 'ფორმულაში არ მონაწილეობს: ' . implode(', ', array_map(fn ($v) => $v['name'] . '=' . $v['value'], $unused)) . ' (ზედმეტი მონაცემი?).';
        }

        if (! $sortFn && $pairs) {
            return $this->buildPairQuestion($text, $tpl, $vars, $values, $formula, $tree, $answerValue, $pairs, $notes, $warnings, $missing, $maxLevel, $options, $answer !== null || $pairs['answer'] !== null, $alts);
        }

        // ── არასწორი პასუხები ──
        $distractors = [];
        $wrongOpts = array_values(array_filter($options, fn ($o) => $o !== $answerValue));
        if ($tree && $wrongOpts) {
            // ტიპური შეცდომა ჩვეულებრივ უფრო მარტივია (გამოტოვებული ნაბიჯი) — რთული დამთხვევები არა
            $maxOps = max(1, $this->countOps($tree) - 1);
            foreach ($wrongOpts as $wo) {
                $c = $this->bestFor($cands, $wo, $formula, $maxOps, $best['final'] - 1.0);
                if ($c) $distractors[] = $c['e'];
            }
        }
        if ($tree) {
            foreach ($this->mutations($tree) as $m) {
                if (count($distractors) >= 3) break;
                $v = $this->evalTree($m, $values);
                if ($v === null || $v <= 0 || $v === $answerValue) continue;
                $e = $this->exprString($m);
                if (! in_array($e, $distractors, true)) $distractors[] = $e;
            }
        }
        $distractors = array_slice(array_values(array_unique($distractors)), 0, 3);

        // ── ცვლადების დიაპაზონები + პირობები ──
        $variables = [];
        foreach ($vars as $v) $variables[$v['name']] = $this->rangeFor($v['value']);
        $conditions = $tree ? $this->conditionsFor($tree) : [];
        if ($sortFn && $sortK) {
            // ნივთები ერთმანეთისგან განსხვავდებოდეს, რომ „ყველაზე ძვირი“ ცალსახა იყოს
            $unique = true;
        }

        $ops = $tree ? $this->countOps($tree) : 1;
        $difficulty = max(1, min($maxLevel, $ops <= 1 ? 1 : ($ops === 2 ? 2 : 3)));

        return [
            'title'           => $this->titleFrom($text),
            'difficulty'      => $difficulty,
            'answer_type'     => 'numeric',
            'template_text'   => $tpl,
            'hint_text'       => '',
            'correct_formula' => $formula,
            'variables'       => $variables,
            'unique'          => $unique ?? false,
            'conditions'      => $conditions,
            'distractors'     => ['formulas' => $distractors, 'min' => 1, 'max' => 3, 'step' => 1],
            'missing_words'   => $missing,
            'original'        => ['text' => $text, 'values' => $values, 'answer' => (string) $answerValue],
            '_draft'          => [
                'formula' => $formula, 'warnings' => $warnings, 'notes' => $notes,
                'options' => $options, 'answer_given' => $answer !== null,
                'alts'    => array_map(fn ($c) => ['e' => $c['e'], 'v' => $c['v']], $alts),
            ],
        ];
    }

    // ─────────────────────────── ფორმულის ხელით შეცვლა ───────────────────────────

    /**
     * ადმინმა სწორი ფორმულა ჩაწერა: პირობები და არასწორი პასუხები თავიდან აიგება ამ ფორმულიდან.
     * ოსტატის მიერ გამოცნობილი პასუხი ახალი ფორმულით გადაითვლება; ამოცანაში მითითებული პასუხი
     * (ან AI-ს JSON-ის original.answer) უცვლელი რჩება — შეუსაბამობას შემოწმება აჩვენებს.
     */
    public function refit(array $q, string $formula): array
    {
        $formula = trim(preg_replace('/\s+/', ' ', $formula));
        $values = is_array($q['original']['values'] ?? null) ? $q['original']['values'] : [];
        $tree = $this->parseFormula($formula);
        $draft = is_array($q['_draft'] ?? null) ? $q['_draft'] : null;

        $answer = $tree ? $this->evalTree($tree, $values) : null;
        if ($tree === null && $values) $answer = $this->evalPlain($formula, $values);

        // ტექსტური პასუხი („{{X}} და {{X}}“) — იცვლება X-ის ფორმულა, პასუხის ფორმა იგივე რჩება
        $fv = $draft['formula_var'] ?? null;
        if ($fv) {
            $q['variables'][$fv] = ['derived' => $formula];
            $q['conditions'] = array_merge($tree ? $this->conditionsFor($tree) : [], $this->offsetConditions((array) ($draft['offsets'] ?? [])));
            if (! empty($draft['digit'])) $q['conditions'][] = ['left' => $fv, 'op' => '<=', 'right' => (string) (9 - max(0, max(array_values((array) ($draft['offsets'] ?? [])) ?: [0])))];
            if (empty($draft['answer_given']) && $answer !== null && $answer > 0) {
                $vals = [$fv => $answer];
                foreach ((array) ($draft['offsets'] ?? []) as $nm => $k) $vals[$nm] = $answer + (int) $k;
                $q['original']['answer'] = preg_replace_callback('/\{\{(\w+)\}\}/', fn ($m) => (string) ($vals[$m[1]] ?? $m[0]), (string) ($draft['answer_tpl'] ?? $q['correct_formula']));
            }
            $q['_draft']['warnings'] = ($answer !== null && ! empty($draft['options']) && ! in_array($answer, $draft['options'], true)) ? ["{$fv} = {$formula} = {$answer} — ეს რიცხვი ვარიანტებში არ არის."] : [];
            $q['_draft']['notes'] = ["{$fv}-ის ფორმულა ხელით შეიცვალა: {$formula}" . ($answer !== null ? " = {$answer}" : '') . '.'];
            $q['_draft']['formula'] = $formula;
            return $q;
        }

        $q['correct_formula'] = $formula;
        if ($draft !== null && empty($draft['answer_given']) && $answer !== null && $answer > 0) {
            $q['original']['answer'] = (string) $answer;
        }

        if ($tree) {
            $q['conditions'] = $this->conditionsFor($tree);
            $dist = [];
            foreach ((array) ($q['distractors']['formulas'] ?? []) as $old) {
                $ot = $this->parseFormula((string) $old);
                $ov = $ot ? $this->evalTree($ot, $values) : null;
                if ($ot && $ov !== null && $ov > 0 && $ov !== $answer && $old !== $formula) $dist[] = (string) $old;
            }
            foreach ($this->mutations($tree) as $m) {
                if (count($dist) >= 3) break;
                $v = $this->evalTree($m, $values);
                $e = $this->exprString($m);
                if ($v === null || $v <= 0 || $v === $answer || in_array($e, $dist, true)) continue;
                $dist[] = $e;
            }
            $q['distractors'] = array_merge(is_array($q['distractors'] ?? null) ? $q['distractors'] : ['min' => 1, 'max' => 3, 'step' => 1], ['formulas' => array_slice($dist, 0, 3)]);
        }

        if ($draft !== null) {
            $note = 'ფორმულა ხელით შეიცვალა: ' . $formula . ($answer !== null ? ' = ' . $answer : '') . '.';
            if ($answer !== null && ! empty($draft['options']) && ! in_array($answer, $draft['options'], true)) {
                $q['_draft']['warnings'] = ["{$formula} = {$answer} — ეს რიცხვი პასუხის ვარიანტებში არ არის."];
            } else {
                $q['_draft']['warnings'] = [];
            }
            $q['_draft']['notes'] = [$note];
            $q['_draft']['formula'] = $formula;
        }
        return $q;
    }

    /** + − × ÷, ფრჩხილები, ცვლადები, რიცხვები → ხე; ფუნქციების შემთხვევაში null */
    public function parseFormula(string $f): ?array
    {
        if (! preg_match_all('/\s*(\d+|[A-Za-z_][A-Za-z0-9_]*|[-+*\/()])/A', $f, $m) || implode('', $m[0]) !== $f) {
            if (trim($f) === '' || preg_replace('/\s+/', '', implode('', $m[0] ?? [])) !== preg_replace('/\s+/', '', $f)) return null;
        }
        $tok = $m[1];
        $i = 0;
        $expr = function () use (&$expr, &$term, &$tok, &$i) {
            $l = $term();
            while ($l !== null && isset($tok[$i]) && in_array($tok[$i], ['+', '-'], true)) {
                $op = $tok[$i++]; $r = $term();
                if ($r === null) return null;
                $l = ['op' => $op, 'l' => $l, 'r' => $r];
            }
            return $l;
        };
        $atom = function () use (&$expr, &$tok, &$i) {
            $t = $tok[$i] ?? null;
            if ($t === null) return null;
            if ($t === '(') { $i++; $e = $expr(); if (($tok[$i] ?? null) !== ')') return null; $i++; return $e; }
            if ($t === 'floor' && ($tok[$i + 1] ?? null) === '(') {
                $i += 2; $e = $expr();
                if ($e === null || ($tok[$i] ?? null) !== ')') return null;
                $i++;
                if (($e['op'] ?? null) === '/') $e['op'] = '//';
                return $e;
            }
            if (preg_match('/^\d+$/', $t)) { $i++; return ['leaf' => $t]; }
            if (preg_match('/^[A-Z][A-Z0-9_]*$/', $t) && ($tok[$i + 1] ?? null) !== '(') { $i++; return ['leaf' => $t]; }
            return null; // ფუნქცია ან უცნობი
        };
        $term = function () use (&$atom, &$tok, &$i) {
            $l = $atom();
            while ($l !== null && isset($tok[$i]) && in_array($tok[$i], ['*', '/'], true)) {
                $op = $tok[$i++]; $r = $atom();
                if ($r === null) return null;
                $l = ['op' => $op, 'l' => $l, 'r' => $r];
            }
            return $l;
        };
        $tree = $expr();
        return ($tree !== null && $i === count($tok)) ? $tree : null;
    }

    /** ფუნქციებიანი ფორმულის მნიშვნელობა (topsum…) — იგივე წესით, რაც გენერატორში */
    private function evalPlain(string $f, array $values): ?int
    {
        uksort($values, fn ($a, $b) => strlen($b) <=> strlen($a));
        $e = preg_replace_callback('/\b[A-Z][A-Z0-9_]*\b/', fn ($m) => array_key_exists($m[0], $values) ? (string) (int) $values[$m[0]] : '0', $f);
        $allowed = ['round', 'floor', 'ceil', 'abs', 'min', 'max', 'pow', 'sqrt', 'topsum', 'botsum', 'kmax', 'kmin', 'median'];
        $e = preg_replace_callback('/[a-z_][a-z0-9_]*/i', fn ($m) => in_array($m[0], $allowed, true) ? $m[0] : '', $e);
        $e = preg_replace('/[^0-9a-z_+\-*\/(),\s]/i', '', $e);
        require_once __DIR__ . '/../Support/ks_math.php';
        try { $v = eval('return (' . $e . ');'); } catch (\Throwable $t) { return null; }
        return is_numeric($v) && floor($v) == $v ? (int) $v : null;
    }

    // ─────────────────────────── ვარიანტები ───────────────────────────

    /** @return array{0:string,1:int[],2:?int,3:string[],4:?string} ტექსტი, ვარიანტების რიცხვები, პასუხი, ვარიანტები ტექსტად, პასუხი ტექსტად */
    private function extractOptions(string $block): array
    {
        $answer = null;
        $answerRaw = null;
        if (preg_match('/(?:სწორი\s+)?პასუხი\s*[:\-–—=]\s*([^\n]+)/u', $block, $am)) {
            $answer = $this->firstNumber($am[1]);
            $answerRaw = trim($am[1]);
            $block = str_replace($am[0], ' ', $block);
        }
        $options = [];
        $rawOpts = [];
        $marked = null;
        // ა) … ბ) … — ხაზის დასაწყისში ან შუაში
        if (preg_match('/(?:^|\s)[აa]\)\s*/u', $block, $om, PREG_OFFSET_CAPTURE)) {
            $tail = substr($block, $om[0][1]);
            $block = substr($block, 0, $om[0][1]);
            foreach (preg_split('/(?:^|\s)[აბგდეa-e]\)\s*/u', $tail, -1, PREG_SPLIT_NO_EMPTY) as $opt) {
                $n = $this->firstNumber($opt);
                if ($n === null) continue;
                $options[] = $n;
                $rawOpts[] = trim(preg_replace('/[*✓✔]/u', '', $opt));
                if (preg_match('/[*✓✔]/u', $opt)) { $marked = $n; $answerRaw ??= end($rawOpts); }
            }
        }
        return [$block, array_values(array_unique($options)), $answer ?? $marked, $rawOpts, $answerRaw];
    }

    private function firstNumber(string $s): ?int
    {
        if (preg_match('/\d+/', $s, $m)) return (int) $m[0];
        foreach (preg_split('/[^\x{10D0}-\x{10FF}]+/u', $s, -1, PREG_SPLIT_NO_EMPTY) as $w) {
            $v = $this->wordValue($w, true);
            if ($v !== null) return $v;
        }
        return null;
    }

    // ─────────────────────────── რიცხვითი სიტყვები ───────────────────────────

    /** ყველა ფორმა → [value, suffix]: „ოთხი“, „ოთხ“, „ოთხმა“, „ოთხს“, „რვაჯერ“… */
    private function numberForms(): array
    {
        if ($this->numberForms !== null) return $this->numberForms;
        $nom = self::UNITS + self::TENS + [100 => 'ასი'];
        foreach (self::TENS as $t => $tw) {
            $base = mb_substr($tw, 0, -1) . 'და';
            foreach (self::UNITS as $u => $uw) $nom[$t + $u] = $base . $uw;
        }
        $forms = [];
        foreach ($nom as $v => $w) {
            $stem = str_ends_with($w, 'ი') ? mb_substr($w, 0, -1) : $w;
            $forms[$w] = [$v, ''];
            if ($v !== 100) $forms[$stem] ??= [$v, ''];
            foreach (self::SUFFIXES as $suf) $forms[$stem . $suf] ??= [$v, $suf];
        }
        return $this->numberForms = $forms;
    }

    /** სიტყვა → რიცხვი; $allowOne=false-ზე „ერთი“ არ ითვლება (ხშირად არტიკლივითაა: „ერთ თეფშზე“) */
    public function wordValue(string $w, bool $allowOne = false): ?int
    {
        $w = mb_strtolower(trim($w));
        if (preg_match('/^\d+/', $w, $m)) return (int) $m[0];
        $f = $this->numberForms()[$w] ?? null;
        if (! $f) return null;
        if ($f[0] === 1 && ! $allowOne) return null;
        return $f[0];
    }

    /** @return array{0:string,1:array} შაბლონის ტექსტი და ცვლადები [{name,value,noun,dist}] */
    private function replaceNumbers(string $text, array $skipWords = []): array
    {
        $vars = [];
        $forms = $this->numberForms();
        $stems = [];
        foreach ($forms as $f => [$v, $suf]) if ($suf === '' && $v > 1) $stems[$f] = $v;
        uksort($stems, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));
        $compound = '/^(' . implode('|', array_map(fn ($s) => preg_quote($s, '/'), array_keys($stems))) . ')(ლარიან|თეთრიან|კაპიკიან|ნიშნა|წლიან|კილოიან|ლიტრიან|მეტრიან)(\S*)$/u';

        $tokens = preg_split('/(\d+(?:-\d+)?|[\x{10D0}-\x{10FF}]+(?:-[\x{10D0}-\x{10FF}]+)?)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = '';
        $n = count($tokens);
        for ($i = 0; $i < $n; $i++) {
            $tok = $tokens[$i];
            if ($i % 2 === 0) { $out .= $tok; continue; }
            $value = null; $render = null; $dist = false; $noun = null;

            $si = array_search($tok, $skipWords, true);
            if ($si !== false) { unset($skipWords[$si]); $out .= $tok; continue; }

            if (preg_match('/^(\d+)(?:-(\d+))?$/', $tok, $m)) {
                $value = (int) $m[1];
                if (isset($m[2])) { $dist = $m[1] === $m[2]; if (! $dist) { $out .= $tok; continue; } }
            } elseif (preg_match('/^([\x{10D0}-\x{10FF}]+)-([\x{10D0}-\x{10FF}]+)$/u', $tok, $m) && $this->wordValue($m[1], true) !== null && $this->wordValue($m[1], true) === $this->wordValue($m[2], true)) {
                $value = $this->wordValue($m[1], true);
                $dist = true;
                if ($value === 1) { $out .= $tok; continue; } // „თითო-თითო“, „ერთ-ერთი“
            } elseif (preg_match($compound, mb_strtolower($tok), $m)) {
                $value = $stems[$m[1]];
                $render = fn ($ph) => $ph . '-' . $m[2] . $m[3];
                $noun = preg_replace('/იან$/u', '', $m[2]);
            } else {
                $f = $forms[mb_strtolower($tok)] ?? null;
                if (! $f || $f[0] === 1) { $out .= $tok; continue; }
                $value = $f[0];
                $suf = $f[1];
                $render = fn ($ph) => $suf === '' ? $ph : $ph . '-' . $suf;
            }
            if ($value === null || $value <= 0) { $out .= $tok; continue; }

            // „2 ათეული“ → 20
            $next = $tokens[$i + 2] ?? null;
            $gap = $tokens[$i + 1] ?? '';
            if ($next !== null && trim($gap) === '' && preg_match('/^ათეულ/u', $next)) {
                $value *= 10;
                $tokens[$i + 2] = '';
                $tokens[$i + 1] = '';
                $next = null;
            }
            // არსებითი სახელი რიცხვის შემდეგ („12 ბიჭი“) — ზედმეტი მონაცემის ამოსაცნობად
            if ($noun === null && $next !== null && trim($gap) === '' && preg_match('/^[\x{10D0}-\x{10FF}]{3,}$/u', $next)
                && (preg_match('/ი$/u', $next) || preg_match('/[^მს][აეოუ]$/u', $next)) && $this->wordValue($next, true) === null && ! preg_match($compound, mb_strtolower($next))) {
                $noun = TemplateImportService::stem($next);
            }

            $name = self::VAR_NAMES[count($vars)] ?? null;
            if ($name === null) { $out .= $tok; continue; }
            $ph = '{{' . $name . '}}';
            $out .= $dist ? $ph . '-' . $ph : ($render ? $render($ph) : $ph);
            $countOf = $next !== null && trim($gap) === '' && preg_match($compound, mb_strtolower($next));
            $vars[] = ['name' => $name, 'value' => $value, 'noun' => $noun, 'dist' => $dist || $countOf, 'count' => (bool) $countOf, 'unit' => $render !== null && isset($m[2]) && preg_match('/იან$/u', $m[2] ?? '')];
        }
        return [$out, $vars];
    }

    private function implicitConstants(string $lower): array
    {
        $c = [];
        foreach (self::IMPLICIT as $w => [$v, $op]) {
            $times = min(2, mb_substr_count($lower, $w));
            for ($i = 0; $i < $times; $i++) $c[] = ['v' => $v, 'op' => $op];
        }
        return array_slice($c, 0, 3);
    }

    private function unitConstants(string $lower): array
    {
        $c = [];
        foreach (self::UNIT_PAIRS as [$big, $small, $k]) {
            if (preg_match($big, $lower) && preg_match($small, $lower)) $c[$k] = ['v' => $k, 'op' => null];
        }
        return array_values($c);
    }

    /** „ა) 7 და 7 ბ) 6 და 7“ → [[7,7],[6,7]…]; პასუხიც წყვილი, თუ მითითებულია */
    private function pairOptions(array $rawOpts, ?string $answerRaw): ?array
    {
        $parse = function (string $s) {
            if (! preg_match('/^\s*(\S+)\s+და\s+(\S+)/u', $s, $m)) return null;
            $a = $this->firstNumber($m[1]); $b = $this->firstNumber($m[2]);
            return ($a !== null && $b !== null) ? [$a, $b] : null;
        };
        $list = [];
        foreach ($rawOpts as $o) { $pp = $parse($o); if ($pp) $list[] = $pp; }
        if (count($list) < 2 || count($list) < count($rawOpts)) return null;
        return ['list' => $list, 'answer' => $answerRaw !== null ? $parse($answerRaw) : null];
    }

    /** ტექსტური პასუხი: X = ფორმულა, პასუხი „{{X}} და {{X}}“, არასწორები — X ± k */
    private function buildPairQuestion(string $text, string $tpl, array $vars, array $values, string $formula, ?array $tree, int $v, array $pairs, array $notes, array $warnings, array $missing, int $maxLevel, array $options, bool $given, array $alts): array
    {
        // სწორი წყვილი: მითითებული, ან ის, სადაც ორივე (ან ერთი მაინც) = X
        $correct = $pairs['answer'];
        if (! $correct) {
            foreach ($pairs['list'] as $pp) if ($pp[0] === $v && $pp[1] === $v) { $correct = $pp; break; }
            if (! $correct) foreach ($pairs['list'] as $pp) if ($pp[0] === $v || $pp[1] === $v) { $correct = $pp; break; }
        }
        $correct ??= [$v, $v];

        $offsetNames = [];
        $nameFor = function (int $n) use ($v, &$offsetNames) {
            $k = $n - $v;
            if ($k === 0) return self::PAIR_MAIN;
            if (! isset($offsetNames[$k])) {
                $nm = self::PAIR_OFFSETS[count($offsetNames)] ?? null;
                if ($nm === null) return null;
                $offsetNames[$k] = $nm;
            }
            return $offsetNames[$k];
        };
        $tplOf = function (array $pp) use ($nameFor) {
            $a = $nameFor($pp[0]); $b = $nameFor($pp[1]);
            return ($a && $b) ? '{{' . $a . '}} და {{' . $b . '}}' : null;
        };
        $answerTpl = $tplOf($correct);
        $wrong = [];
        foreach ($pairs['list'] as $pp) {
            if ($pp === $correct) continue;
            $t = $tplOf($pp);
            if ($t && $t !== $answerTpl && ! in_array($t, $wrong, true)) $wrong[] = $t;
        }

        $variables = [];
        foreach ($vars as $vv) $variables[$vv['name']] = $this->rangeFor($vv['value']);
        $variables[self::PAIR_MAIN] = ['derived' => $formula];
        $offsets = [];
        foreach ($offsetNames as $k => $nm) {
            $variables[$nm] = ['derived' => self::PAIR_MAIN . ($k > 0 ? ' + ' . $k : ' - ' . (-$k))];
            $offsets[$nm] = $k;
        }
        $conditions = array_merge($tree ? $this->conditionsFor($tree) : [], $this->offsetConditions($offsets));
        // „ციფრი“ — პასუხი ერთნიშნა უნდა იყოს (ყველა ვარიანტიც)
        $isDigit = (bool) preg_match('/ციფრ/u', mb_strtolower($text));
        if ($isDigit) $conditions[] = ['left' => self::PAIR_MAIN, 'op' => '<=', 'right' => (string) (9 - max(0, max($offsets ?: [0])))];

        $notes[] = 'ტექსტური პასუხი: ' . $answerTpl . ', სადაც ' . self::PAIR_MAIN . ' = ' . $formula . ' (= ' . $v . ').';
        if ($offsets) $notes[] = 'არასწორი ვარიანტები ' . self::PAIR_MAIN . '-ის მიხედვით: ' . implode(', ', array_map(fn ($nm, $k) => $nm . ' = ' . self::PAIR_MAIN . ($k > 0 ? '+' : '') . $k, array_keys($offsets), $offsets)) . '.';
        if ($alts) {
            $warnings = array_values(array_filter($warnings, fn ($w) => ! str_starts_with($w, 'ფორმულა გამოცნობილია')));
            $warnings[] = 'ფორმულა გამოცნობილია — ' . self::PAIR_MAIN . ' = ' . $formula . ' = ' . $v . '. სხვა შესაძლო: ' . implode('; ', array_map(fn ($c) => $c['e'] . ' = ' . $c['v'], $alts)) . '.';
        }

        $ops = $tree ? $this->countOps($tree) : 1;
        return [
            'title'           => $this->titleFrom($text),
            'difficulty'      => max(1, min($maxLevel, $ops <= 1 ? 1 : ($ops === 2 ? 2 : 3))),
            'answer_type'     => 'text',
            'template_text'   => $tpl,
            'hint_text'       => '',
            'correct_formula' => $answerTpl,
            'variables'       => $variables,
            'unique'          => false,
            'conditions'      => $conditions,
            'distractors'     => ['vars' => $wrong, 'count' => min(3, max(1, count($wrong)))],
            'missing_words'   => $missing,
            'original'        => ['text' => $text, 'values' => $values, 'answer' => $correct[0] . ' და ' . $correct[1]],
            '_draft'          => [
                'formula' => $formula, 'formula_var' => self::PAIR_MAIN, 'answer_tpl' => $answerTpl, 'offsets' => $offsets, 'digit' => $isDigit,
                'warnings' => $warnings, 'notes' => $notes, 'options' => array_values(array_unique(array_merge(...$pairs['list']))),
                'answer_given' => $given, 'alts' => array_map(fn ($c) => ['e' => $c['e'], 'v' => $c['v']], $alts),
            ],
        ];
    }

    /** X ± k > 0 — არასწორი ვარიანტი უარყოფითი/ნული არ გამოვიდეს */
    private function offsetConditions(array $offsets): array
    {
        $min = 0;
        foreach ($offsets as $k) $min = min($min, (int) $k);
        return $min < 0 ? [['left' => self::PAIR_MAIN, 'op' => '>', 'right' => (string) (-$min)]] : [['left' => self::PAIR_MAIN, 'op' => '>', 'right' => '0']];
    }

    private function hintOps(string $lower, array $vars): array
    {
        $ops = [];
        foreach (self::HINTS as $op => $words) {
            foreach ($words as $w) if (str_contains($lower, $w)) { $ops[$op] = true; break; }
        }
        if (preg_match('/ყიდვა|იყიდოს|შეუძლია|ეყოფა|ჩაეტევა|ჩაეტია|სავსე|მთლიან|შეიძლება/u', $lower)) $ops['floor'] = true;
        // „რამდენი ათთეთრიანი / სამლარიანი …“ — მონეტების/ნივთების რაოდენობა → გაყოფა
        if (preg_match('/რამდენ\S*\s+\S*(?:ლარიან|თეთრიან|კაპიკიან)/u', $lower)) { $ops['/'] = true; $ops['floor'] = true; }
        // „ორი ხუთლარიანი და სამი ორლარიანი“ — რამდენიმე „რაოდენობა × ფასი“ → ჯამი
        if (count(array_filter($vars, fn ($v) => ! empty($v['count']))) >= 2) $ops['+'] = true;
        // „თანაბრად … თითოეულ ყუთში“ — „თითოეული“ აქ გაყოფის შედეგია, არა გამრავლება
        if (isset($ops['/']) && preg_match('/თანაბრად|გაუნაწილ|გაყო|გაუყო/u', $lower)) unset($ops['*']);
        foreach ($vars as $v) if ($v['dist']) $ops['*'] = true;
        return $ops;
    }

    /** „რამდენი ბიჭი …?“ → „ბიჭ“ */
    private function questionNoun(string $text): ?string
    {
        if (preg_match('/რამდენ(?:ი|ს|მა)?\s+(?:ცალი\s+)?([\x{10D0}-\x{10FF}]{3,})/u', $text, $m)) {
            return TemplateImportService::stem($m[1]);
        }
        return null;
    }

    // ─────────────────────────── თემატიკა ───────────────────────────

    /** @return array{0:string,1:array,2:array} */
    private function replaceThemeWords(string $tpl, ?Theme $theme): array
    {
        if (! $theme) return [$tpl, [], []];
        $map = [];
        foreach ((array) $theme->variableMap() as $var => $vals) {
            foreach ((array) $vals as $val) {
                $val = trim((string) $val);
                if (mb_strlen($val) >= 3 && ! str_contains($val, ' ')) $map[] = ['var' => $var, 'value' => mb_strtolower($val)];
            }
        }
        usort($map, fn ($a, $b) => mb_strlen($b['value']) <=> mb_strlen($a['value']));

        // სიტყვა → [var, value, suffix]
        preg_match_all('/[\x{10D0}-\x{10FF}]{3,}/u', preg_replace('/\{\{\w+\}\}/', ' ', $tpl), $wm);
        $hits = [];
        foreach (array_unique($wm[0]) as $w) {
            $lw = mb_strtolower($w);
            foreach ($map as $e) {
                if (str_starts_with($lw, $e['value']) && in_array(mb_substr($lw, mb_strlen($e['value'])), self::CASE_SUFFIXES, true)) {
                    $hits[$w] = $e + ['suffix' => mb_substr($w, mb_strlen($e['value']))];
                    break;
                }
                // „მარიამი“ → „მარიამს“, „მარიამმა“ (ფუძე ი-ს გარეშე + ბრუნვის ნიშანი)
                if (str_ends_with($e['value'], 'ი')) {
                    $root = mb_substr($e['value'], 0, -1);
                    $suf = mb_substr($lw, mb_strlen($root));
                    if (mb_strlen($root) >= 3 && str_starts_with($lw, $root) && in_array($suf, ['ს', 'მა', 'ის', 'ით', 'თან', 'ზე', 'ში', 'ისთვის'], true)) {
                        $hits[$w] = $e + ['suffix' => $suf];
                        break;
                    }
                }
            }
        }
        // ერთ ცვლადზე ორი სხვადასხვა მნიშვნელობა (ორი სხვადასხვა სახელი) — ვერ ჩავანაცვლებთ, თორემ ერთი და იგივე გახდება
        $byVar = [];
        foreach ($hits as $w => $h) $byVar[$h['var']][$h['value']] = true;
        $notes = []; $missing = [];
        foreach ($hits as $w => $h) {
            if (count($byVar[$h['var']]) > 1) {
                $missing[] = ['word' => $w, 'suggested_var' => $h['var'] . '2'];
                continue;
            }
            $ph = '{{' . $h['var'] . '}}' . ($h['suffix'] !== '' ? '-' . $h['suffix'] : '');
            $tpl = preg_replace('/(?<![\x{10D0}-\x{10FF}])' . preg_quote($w, '/') . '(?![\x{10D0}-\x{10FF}])/u', $ph, $tpl);
            $notes[$h['var']] = "„{$h['value']}“ → {{{$h['var']}}}";
        }
        $multi = array_keys(array_filter($byVar, fn ($v) => count($v) > 1));
        if ($multi) $notes[] = 'რამდენიმე სხვადასხვა სიტყვა ერთ ცვლადზე (' . implode(', ', $multi) . ') — უცვლელად დავტოვე, რომ ერთნაირი არ გამხდარიყო.';
        return [$tpl, array_values($notes), $missing];
    }

    // ─────────────────────────── ფორმულის ძებნა ───────────────────────────

    /**
     * ყველა გამოსახულება ≤ MAX_LEAVES ფოთლით (თითო ცვლადი/მუდმივა მაქსიმუმ ერთხელ),
     * მხოლოდ დადებითი მთელი შუალედური შედეგებით. თითო (ნაკრები, მნიშვნელობა) — საუკეთესო ქულით.
     */
    private function search(array $vars, array $consts, array $hints, ?string $qNoun): array
    {
        $items = [];
        $nounMatch = $qNoun && array_filter($vars, fn ($v) => $v['noun'] === $qNoun);
        foreach ($vars as $v) {
            $s = 0.3;
            if ($nounMatch && $v['noun'] !== null && $v['noun'] !== $qNoun) $s -= 2.5;
            $items[] = ['t' => ['leaf' => $v['name']] + ($v['dist'] ? ['dist' => true] : []), 'v' => $v['value'], 's' => $s, 'var' => true];
        }
        foreach ($consts as $c) $items[] = ['t' => ['leaf' => (string) $c['v']] + ($c['op'] !== null ? ['use' => $c['op']] : []), 'v' => $c['v'], 's' => 0.15, 'var' => false];
        // „კიდევ რამდენი ასეთი ნაყინი“ — ფასი ფორმულაში ორჯერ მონაწილეობს: (A − B) ÷ B
        if ($this->sameAgain && count($items) < self::MAX_ITEMS) {
            foreach ($vars as $v) if (! empty($v['unit'])) { $items[] = ['t' => ['leaf' => $v['name']], 'v' => $v['value'], 's' => 0.1, 'var' => false]; break; }
        }

        $n = count($items);
        $table = []; // mask => [value => entry]
        for ($i = 0; $i < $n; $i++) $table[1 << $i][$items[$i]['v']] = $items[$i] + ['leaves' => 1];

        // ნაშთიანი გაყოფა მხოლოდ „ყიდვა/შეუძლია/ეყოფა/ჩაეტევა“ ტიპის კითხვებში
        $floorOk = ! empty($hints['floor']);
        unset($hints['floor']);
        $opScore = function (string $op) use ($hints) {
            if (! $hints) return -0.2;
            return isset($hints[$op]) ? 0.45 : -0.7;
        };

        $full = (1 << $n) - 1;
        $masks = range(1, $full);
        usort($masks, fn ($a, $b) => substr_count(decbin($a), '1') <=> substr_count(decbin($b), '1'));
        foreach ($masks as $mask) {
            $bits = substr_count(decbin($mask), '1');
            if ($bits < 2 || $bits > self::MAX_LEAVES) continue;
            // ყველა დაყოფა ორ არაცარიელ ნაწილად (a — დაბალი ბიტის შემცველი, რომ არ გამეორდეს)
            for ($a = ($mask - 1) & $mask; $a > 0; $a = ($a - 1) & $mask) {
                $b = $mask ^ $a;
                if (empty($table[$a]) || empty($table[$b])) continue;
                foreach ($table[$a] as $x) {
                    foreach ($table[$b] as $y) {
                        foreach (['+', '-', '*', '/', '//'] as $op) {
                            // + და * კომუტატიურია — ერთი რიგი საკმარისია
                            // (მარცხნივ — ტექსტში უფრო ადრე მოხსენიებული)
                            if (($op === '+' || $op === '*') && ($a & -$a) > ($b & -$b)) continue;
                            $v = match ($op) {
                                '+' => $x['v'] + $y['v'],
                                '-' => $x['v'] - $y['v'],
                                '*' => $x['v'] * $y['v'],
                                '/' => ($y['v'] > 1 && $x['v'] % $y['v'] === 0) ? intdiv($x['v'], $y['v']) : 0,
                                // დამრგვალებით (ქვემოთ) გაყოფა — „რამდენი ნაყინის ყიდვა შეუძლია“; მხოლოდ თუ ნაშთი რჩება
                                '//' => ($floorOk && $y['v'] > 1 && $x['v'] > $y['v'] && $x['v'] % $y['v'] !== 0) ? intdiv($x['v'], $y['v']) : 0,
                            };
                            if ($v <= 0 || $v > self::MAX_VALUE) continue;
                            if ($op === '*' && ($x['v'] === 1 || $y['v'] === 1)) continue;
                            // მუდმივა („ნახევარი“, „სამივე“) — მხოლოდ თავის მოქმედებაში; გამყოფი — მარჯვნივ
                            $opK = $op === '//' ? '/' : $op;
                            if (isset($x['t']['use']) && ($x['t']['use'] !== $opK || $opK === '/')) continue;
                            if (isset($y['t']['use']) && $y['t']['use'] !== $opK) continue;
                            $s = $x['s'] + $y['s'] + $opScore($opK) - 0.4 - ($op === '//' && ! $floorOk ? 0.35 : 0);
                            // გაყოფა რთულ გამოსახულებაზე (A·B ÷ (C·D ÷ E)) — არაბუნებრივია, თითქმის ყოველთვის დამთხვევა
                            if ($opK === '/' && isset($y['t']['op'])) $s -= 0.6;
                            if ($this->maxBit($a) < ($b & -$b)) $s += 0.1; // ტექსტის რიგით
                            // „ოთხმა ორ-ორი“ — განაწილებითი რიცხვი გამრავლებაში
                            if ($op === '*' && (isset($x['t']['dist']) || isset($y['t']['dist']))) $s += 0.5;
                            // „ორი ორლარიანი“ — რაოდენობა × ფასი: მეზობელი რიცხვები
                            if ($op === '*' && isset($x['t']['leaf'], $y['t']['leaf']) && (isset($x['t']['dist']) || isset($y['t']['dist']))
                                && abs(array_search($x['t']['leaf'], self::VAR_NAMES, true) - array_search($y['t']['leaf'], self::VAR_NAMES, true)) === 1) $s += 0.3;
                            if (isset($table[$mask][$v]) && $table[$mask][$v]['s'] >= $s) continue;
                            $table[$mask][$v] = ['t' => ['op' => $op, 'l' => $x['t'], 'r' => $y['t']], 'v' => $v, 's' => $s, 'leaves' => $x['leaves'] + $y['leaves']];
                        }
                    }
                }
            }
        }

        $out = [];
        $needOp = count($vars) > 1;
        foreach ($table as $mask => $byVal) {
            foreach ($byVal as $e) {
                if ($needOp && ! isset($e['t']['op'])) continue;
                // გრძელი ფორმულა — ნაკლებად სავარაუდო (დამთხვევის რისკი იზრდება)
                $final = $e['s'] - 0.35 * max(0, $e['leaves'] - 3);
                $str = $this->exprString($e['t']);
                // „რამდენის ყიდვა შეუძლია“ — პასუხი გაყოფით მიიღება
                if ($floorOk && ($e['t']['op'] ?? null) !== null && preg_match('/\/|floor/', $str)) $final += 0.7;
                // ცვლადები ტექსტის რიგით — უფრო ბუნებრივი ამოხსნა
                preg_match_all('/\b[A-Z]\b/', $str, $lm);
                $order = array_map(fn ($l) => array_search($l, self::VAR_NAMES, true), $lm[0]);
                $sorted = $order; sort($sorted);
                if (count($order) > 1 && $order === $sorted) $final += 0.25;
                $out[] = ['e' => $str, 't' => $e['t'], 'v' => $e['v'], 'final' => $final];
            }
        }
        return $out;
    }

    private function maxBit(int $m): int
    {
        $b = 1;
        while ($m > 1) { $m >>= 1; $b <<= 1; }
        return $b;
    }

    private function bestFor(array $cands, int $value, string $exclude, int $maxOps, float $minScore): ?array
    {
        $best = null;
        foreach ($cands as $c) {
            if ($c['v'] !== $value || $c['e'] === $exclude || ! isset($c['t']['op'])) continue;
            if ($c['final'] < $minScore || $this->countOps($c['t']) > $maxOps) continue;
            if (! $best || $c['final'] > $best['final']) $best = $c;
        }
        return $best;
    }

    /** ტიპური შეცდომები: მოქმედების შებრუნება, ერთი წევრის გამოტოვება */
    private function mutations(array $t): array
    {
        if (! isset($t['op'])) return [];
        $flip = ['+' => '-', '-' => '+', '*' => '+', '/' => '*', '//' => '-'];
        $out = [array_merge($t, ['op' => $flip[$t['op']]])];
        $out[] = $t['l'];
        if (isset($t['r']['op']) || ! is_numeric($t['r']['leaf'] ?? '')) $out[] = $t['r'];
        foreach ($this->mutations($t['l']) as $m) $out[] = array_merge($t, ['l' => $m]);
        foreach ($this->mutations($t['r']) as $m) $out[] = array_merge($t, ['r' => $m]);
        return $out;
    }

    private function evalTree(array $t, array $vals): ?int
    {
        if (isset($t['leaf'])) return is_numeric($t['leaf']) ? (int) $t['leaf'] : ($vals[$t['leaf']] ?? null);
        $l = $this->evalTree($t['l'], $vals); $r = $this->evalTree($t['r'], $vals);
        if ($l === null || $r === null) return null;
        return match ($t['op']) {
            '+' => $l + $r, '-' => $l - $r, '*' => $l * $r,
            '/' => ($r !== 0 && $l % $r === 0) ? intdiv($l, $r) : null,
            '//' => $r > 0 ? intdiv($l, $r) : null,
        };
    }

    private function exprString(array $t, int $parentPrec = 0, bool $right = false): string
    {
        if (isset($t['leaf'])) return $t['leaf'];
        if ($t['op'] === '//') return 'floor(' . $this->exprString($t['l'], 2) . ' / ' . $this->exprString($t['r'], 2, true) . ')';
        $prec = in_array($t['op'], ['+', '-'], true) ? 1 : 2;
        $s = $this->exprString($t['l'], $prec) . ' ' . $t['op'] . ' ' . $this->exprString($t['r'], $prec, true);
        $need = $prec < $parentPrec || ($right && $prec === $parentPrec);
        return $need ? '(' . $s . ')' : $s;
    }

    private function conditionsFor(array $t): array
    {
        if (! isset($t['op'])) return [];
        $c = [];
        if ($t['op'] === '-') $c[] = ['left' => $this->exprString($t['l']), 'op' => '>', 'right' => $this->exprString($t['r'])];
        if ($t['op'] === '/') $c[] = ['left' => $this->exprString($t['l']), 'op' => '%0', 'right' => $this->exprString($t['r'])];
        if ($t['op'] === '//') $c[] = ['left' => $this->exprString($t['l']), 'op' => '>=', 'right' => $this->exprString($t['r'])];
        return array_merge($c, $this->conditionsFor($t['l']), $this->conditionsFor($t['r']));
    }

    private function countOps(array $t): int
    {
        return isset($t['op']) ? 1 + $this->countOps($t['l']) + $this->countOps($t['r']) : 0;
    }

    private function evalSort(string $fn, int $k, array $xs): int
    {
        $fn === 'topsum' ? rsort($xs) : sort($xs);
        return array_sum(array_slice($xs, 0, $k));
    }

    /** ორიგინალის გარშემო გონივრული დიაპაზონი; ორიგინალი ყოველთვის შიგნითაა */
    private function rangeFor(int $v): array
    {
        if ($v >= 20 && $v % 10 === 0) {
            return ['min' => max(10, intdiv($v, 20) * 10), 'max' => max($v + 20, (int) (ceil($v * 1.5 / 10) * 10)), 'step' => 10];
        }
        if ($v >= 10 && $v % 5 === 0) {
            return ['min' => max(5, intdiv($v, 10) * 5), 'max' => max($v + 10, (int) (ceil($v * 1.5 / 5) * 5)), 'step' => 5];
        }
        if ($v <= 3) return ['min' => 1, 'max' => $v + 2, 'step' => 1];
        return ['min' => max(1, intdiv($v, 2)), 'max' => max($v + 4, (int) ceil($v * 1.5)), 'step' => 1];
    }

    private function titleFrom(string $text): string
    {
        $plain = trim(preg_replace('/[^\x{10D0}-\x{10FF}\s]/u', ' ', $text));
        $words = array_slice(preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY), 0, 5);
        return implode(' ', $words) ?: 'ამოცანა';
    }
}
