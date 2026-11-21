<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\QuestionTemplate;
use App\Models\Theme;
use App\Models\Topic;

/**
 * კითხვების JSON იმპორტი (AI-ით შედგენილი შაბლონები).
 *
 * AI-ს დაბრუნებულ JSON-ს არასოდეს ვწერთ ბაზაში პირდაპირ: თითოეული კითხვა
 * გადის შემოწმებებს (errors — შენახვას ბლოკავს, warnings — მხოლოდ გაფრთხილებაა),
 * და აჩვენებს 3 გენერირებულ ვარიანტს. შენახვა ხდება ადმინის დადასტურებით.
 *
 * ამ ეტაპზე მხარდაჭერილია მხოლოდ question_type = multiple_choice
 * (answer_type: numeric | numeric_input | text).
 */
class TemplateImportService
{
    private const RUNS = 200;
    private const MATH_FNS = ['round', 'floor', 'ceil', 'abs', 'min', 'max', 'pow', 'sqrt', 'topsum', 'botsum', 'kmax', 'kmin', 'median'];
    private const ANSWER_TYPES = ['numeric', 'numeric_input', 'text'];

    /** @var array<string, array{theme:string, var:string, value:string}> stem => info */
    private ?array $valueIndex = null;

    // ─────────────────────────── JSON → items ───────────────────────────

    /** @return array{items: array, skipped: array, fatal: ?string} */
    public function parse(string $json, array $defaults = []): array
    {
        $json = trim($json);
        // AI ხშირად აბრუნებს ```json ... ``` ბლოკში
        $json = preg_replace('/^```(?:json)?\s*|\s*```$/u', '', $json);
        $data = json_decode($json, true);
        if (! is_array($data)) {
            return ['items' => [], 'skipped' => [], 'fatal' => 'JSON ვერ წაიკითხა: ' . json_last_error_msg() . '. შეამოწმე, რომ ტექსტი მთლიანად დააკოპირე.'];
        }
        $list = isset($data['questions']) && is_array($data['questions']) ? $data['questions'] : (array_keys($data) === range(0, count($data) - 1) ? $data : [$data]);
        if (! $list) {
            return ['items' => [], 'skipped' => [], 'fatal' => 'JSON-ში კითხვები ვერ მოიძებნა (საჭიროა "questions" სია).'];
        }
        $items = [];
        foreach (array_values($list) as $i => $q) {
            $items[] = $this->check(is_array($q) ? $q : [], $i, $defaults);
        }
        $skipped = [];
        foreach ((array) ($data['skipped'] ?? []) as $sk) {
            if (is_array($sk)) $skipped[] = ['text' => (string) ($sk['text'] ?? ''), 'reason' => (string) ($sk['reason'] ?? '')];
        }
        return ['items' => $items, 'skipped' => $skipped, 'fatal' => null];
    }

    // ─────────────────────────── ერთი კითხვის შემოწმება ───────────────────────────

    public function check(array $q, int $index = 0, array $defaults = []): array
    {
        // ნაგულისხმევი თემა/თემატიკა იმპორტის გვერდიდან — თუ JSON-ში არ წერია ან „[...]“ ჩანაცვლების ადგილია
        $isBlank = fn ($v) => $v === null || $v === '' || (is_string($v) && str_starts_with(trim($v), '['));
        if (! empty($defaults['topic_id']) && $isBlank($q['topic_id'] ?? null) && $isBlank($q['topic'] ?? null)) {
            $q['topic_id'] = (int) $defaults['topic_id'];
            unset($q['grade'], $q['grade_id']);
        }
        if (! empty($defaults['theme_id']) && $isBlank($q['theme_id'] ?? null) && $isBlank($q['theme'] ?? null)) {
            $q['theme_id'] = (int) $defaults['theme_id'];
        }
        if (isset($q['topic']) && $isBlank($q['topic'])) unset($q['topic']);
        if (isset($q['theme']) && $isBlank($q['theme'])) unset($q['theme']);

        $errors = [];
        $warnings = [];
        $notes = [];

        $title = trim((string) ($q['title'] ?? ''));
        $label = $title !== '' ? $title : 'კითხვა #' . ($index + 1);

        // ── კლასი / თემა / თემატიკა / დონე ──
        $grade = null;
        if (isset($q['grade_id'])) $grade = Grade::find((int) $q['grade_id']);
        elseif (isset($q['grade']) && is_numeric($q['grade'])) $grade = Grade::where('number', (int) $q['grade'])->first();
        elseif (isset($q['grade'])) $grade = Grade::where('name', trim((string) $q['grade']))->first();

        $topic = null;
        if (isset($q['topic_id'])) {
            $topic = Topic::with('grade')->find((int) $q['topic_id']);
        } elseif (! empty($q['topic'])) {
            $name = mb_strtolower(trim((string) $q['topic']));
            $cands = Topic::with('grade')->get()->filter(fn ($t) => mb_strtolower(trim($t->name)) === $name);
            if ($grade) $cands = $cands->where('grade_id', $grade->id);
            if ($cands->count() > 1) $errors[] = "თემა „{$q['topic']}“ რამდენიმე კლასშია — მიუთითე grade ან topic_id.";
            $topic = $cands->count() === 1 ? $cands->first() : null;
        }
        if (! $topic) $errors[] = 'თემა ვერ მოიძებნა' . (! empty($q['topic']) ? " („{$q['topic']}“)" : '') . ' — გამოიყენე ზუსტი სახელი ან topic_id ინსტრუქციის სიიდან.';
        if ($topic && $grade && $topic->grade_id !== $grade->id) $errors[] = "თემა „{$topic->name}“ არჩეულ კლასს არ ეკუთვნის.";
        $grade = $topic?->grade ?? $grade;

        $theme = null;
        if (isset($q['theme_id'])) $theme = Theme::find((int) $q['theme_id']);
        elseif (! empty($q['theme'])) $theme = Theme::get()->first(fn ($t) => mb_strtolower(trim($t->name)) === mb_strtolower(trim((string) $q['theme'])));
        if ((isset($q['theme_id']) || ! empty($q['theme'])) && ! $theme) {
            $errors[] = 'თემატიკა ვერ მოიძებნა („' . ($q['theme'] ?? $q['theme_id']) . '“).';
        }

        $isOlympiad = (bool) ($q['is_olympiad'] ?? false);
        $difficulty = (int) ($q['difficulty'] ?? 0);
        $maxLevel = (int) ($grade?->max_level ?: Grade::DEFAULT_MAX_LEVEL);
        if ($difficulty < 1 || $difficulty > 5) $errors[] = 'დონე (difficulty) უნდა იყოს 1-დან 5-მდე.';
        elseif (! $isOlympiad && $difficulty > $maxLevel) $errors[] = "ამ კლასს მაქსიმუმ {$maxLevel} დონე აქვს (მითითებულია {$difficulty}).";

        $answerType = (string) ($q['answer_type'] ?? 'numeric');
        if (! in_array($answerType, self::ANSWER_TYPES, true)) $errors[] = "answer_type უნდა იყოს: numeric, numeric_input ან text (მითითებულია „{$answerType}“).";

        $text = trim((string) ($q['template_text'] ?? ''));
        $hint = trim((string) ($q['hint_text'] ?? ''));
        $rawFormula = trim((string) ($q['correct_formula'] ?? ''));
        $answerTypeRaw = (string) ($q['answer_type'] ?? 'numeric');
        // რიცხვით ფორმულაში {{N1}} → N1; ტექსტურ პასუხში {{…}} რჩება (მაგ. "{{P}} და {{P}}")
        $formula = $answerTypeRaw === 'text' ? $rawFormula : trim(preg_replace('/\{\{\s*(\w+)\s*\}\}/', '$1', $rawFormula));
        if ($text === '') $errors[] = 'კითხვის ტექსტი (template_text) ცარიელია.';
        if ($formula === '') $errors[] = 'სწორი პასუხის ფორმულა (correct_formula) ცარიელია.';

        // ── ცვლადები → num_config ──
        $vars = is_array($q['variables'] ?? null) ? $q['variables'] : [];
        $numConfig = [];
        $randomVars = [];
        foreach ($vars as $name => $conf) {
            $name = (string) $name;
            if (! preg_match('/^[A-Z][A-Z0-9_]*$/', $name)) {
                $errors[] = "ცვლადის სახელი „{$name}“ არასწორია — მხოლოდ დიდი ლათინური ასოები, ციფრები და _ (მაგ. N1, PRICE).";
                continue;
            }
            if (! is_array($conf)) { $errors[] = "ცვლადი {$name}: აღწერა უნდა იყოს ობიექტი."; continue; }
            if (! empty($conf['operator'])) {
                $opts = array_values(array_intersect((array) ($conf['options'] ?? ['+']), ['+', '-', '*', '/']));
                if (! $opts) $errors[] = "ცვლადი {$name}: operator options უნდა იყოს + - * / -დან.";
                $numConfig[$name] = ['operator' => true, 'options' => $opts ?: ['+']];
            } elseif (array_key_exists('derived', $conf)) {
                $numConfig[$name] = ['derived' => (string) $conf['derived']];
            } elseif (array_key_exists('conditional', $conf)) {
                $numConfig[$name] = ['conditional' => (string) $conf['conditional']];
            } elseif (array_key_exists('values', $conf)) {
                $vals = is_array($conf['values']) ? $conf['values'] : [];
                $ints = array_values(array_unique(array_map('intval', array_filter($vals, fn ($v) => is_numeric($v) && (float) $v == (int) $v))));
                if (! $ints || count($ints) !== count($vals)) {
                    $errors[] = "ცვლადი {$name}: values უნდა იყოს მთელი რიცხვების სია (მაგ. [1, 2, 5, 10]), გამეორების გარეშე.";
                    continue;
                }
                if (count($ints) < 2) $warnings[] = "ცვლადი {$name}: values-ში მხოლოდ ერთი მნიშვნელობაა — ცვლადი არ შეიცვლება.";
                $numConfig[$name] = ['values' => $ints];
                $randomVars[] = $name;
            } else {
                $min = $conf['min'] ?? null; $max = $conf['max'] ?? null; $step = (int) ($conf['step'] ?? 1);
                if (! is_numeric($min) || ! is_numeric($max) || (int) $min > (int) $max) {
                    $errors[] = "ცვლადი {$name}: საჭიროა min ≤ max (მთელი რიცხვები).";
                    continue;
                }
                $numConfig[$name] = ['min' => (int) $min, 'max' => (int) $max, 'step' => max(1, $step)];
                $randomVars[] = $name;
            }
        }
        if (! empty($q['unique'])) $numConfig['_unique'] = true;

        // N1 და N12 — ერთი მეორის ნაწილია, ფორმულაში ჩანაცვლება აირევა
        $names = array_keys(array_filter($numConfig, 'is_array'));
        foreach ($names as $a) foreach ($names as $b) {
            if ($a !== $b && str_starts_with($b, $a)) {
                $errors[] = "ცვლადები {$a} და {$b}: ერთი სახელი მეორის დასაწყისია — გადაარქვი (მაგ. N1 → A, N12 → B).";
            }
        }

        // ── ტექსტში {{X}} — ცვლადი ან თემატიკის ცვლადი უნდა იყოს ──
        $themeMap = $theme ? $theme->variableMap() : [];
        preg_match_all('/\{\{\s*(\w+)\s*\}\}/u', $text . ' ' . $hint, $m);
        $placeholders = array_unique($m[1]);
        $usedThemeVars = [];
        foreach ($placeholders as $ph) {
            if (isset($numConfig[$ph])) continue;
            if (isset($themeMap[$ph])) { $usedThemeVars[] = $ph; continue; }
            $errors[] = $theme
                ? "ტექსტში {{{$ph}}} არც კითხვის ცვლადია და არც თემატიკა „{$theme->name}“-ის ცვლადი."
                : "ტექსტში {{{$ph}}} თემატიკის ცვლადს ჰგავს, მაგრამ თემატიკა (theme) არ არის მითითებული.";
        }

        // ── ფორმულა: მხოლოდ ცნობილი ცვლადები და მათ. ფუნქციები ──
        preg_match_all('/[A-Za-z_][A-Za-z0-9_]*/', $formula, $fm);
        foreach (array_unique($fm[0]) as $tok) {
            if (isset($numConfig[$tok]) || in_array($tok, self::MATH_FNS, true)) continue;
            if ($answerType === 'text' && (isset($themeMap[$tok]) || str_starts_with($tok, '__'))) continue;
            $errors[] = "ფორმულაში „{$tok}“ უცნობია — არც ცვლადია და არც დაშვებული ფუნქცია (" . implode(', ', self::MATH_FNS) . ').';
        }

        // ── არასწორი პასუხები ──
        $dIn = is_array($q['distractors'] ?? null) ? $q['distractors'] : [];
        if ($answerType === 'text') {
            $distractors = ['vars' => array_values(array_map('strval', (array) ($dIn['vars'] ?? []))), 'count' => max(1, (int) ($dIn['count'] ?? 3))];
            if (! $distractors['vars']) $errors[] = 'ტექსტური პასუხისთვის საჭიროა distractors.vars (არასწორი ვარიანტების ცვლადები/შაბლონები).';
        } elseif ($answerType === 'numeric') {
            $distractors = [
                'formulas'     => array_values(array_filter(array_map(fn ($f) => trim(preg_replace('/\{\{\s*(\w+)\s*\}\}/', '$1', (string) $f)), (array) ($dIn['formulas'] ?? [])))),
                'min'          => max(1, (int) ($dIn['min'] ?? 1)),
                'max'          => max(1, (int) ($dIn['max'] ?? 5)),
                'step'         => max(1, (int) ($dIn['step'] ?? 1)),
                'none_correct' => (bool) ($dIn['none_correct'] ?? false),
            ];
            if ($distractors['max'] < $distractors['min']) $distractors['max'] = $distractors['min'];
            if (! $distractors['formulas']) $notes[] = 'არასწორი პასუხები მხოლოდ შემთხვევითი ±' . $distractors['min'] . '…' . $distractors['max'] . ' იქნება — ტიპური შეცდომის ფორმულები (distractors.formulas) უფრო კარგია.';
            foreach ($distractors['formulas'] as $df) {
                preg_match_all('/[A-Za-z_][A-Za-z0-9_]*/', $df, $dm);
                foreach (array_unique($dm[0]) as $tok) {
                    if (! isset($numConfig[$tok]) && ! in_array($tok, self::MATH_FNS, true)) $errors[] = "არასწორი პასუხის ფორმულაში „{$df}“ უცნობია „{$tok}“.";
                }
            }
        } else {
            $distractors = null;
        }

        // ── პირობები ──
        $conditions = [];
        foreach ((array) ($q['conditions'] ?? []) as $c) {
            if (is_array($c) && isset($c['left'], $c['op'], $c['right'])) {
                if (! in_array($c['op'], ['>', '<', '>=', '<=', '==', '!=', '%0', '!%0'], true)) { $errors[] = "პირობის ოპერატორი „{$c['op']}“ უცნობია."; continue; }
                $conditions[] = ['left' => (string) $c['left'], 'op' => (string) $c['op'], 'right' => (string) $c['right']];
            }
        }

        $attrs = [
            'topic_id'        => $topic?->id,
            'theme_id'        => $theme?->id,
            'difficulty'      => $difficulty,
            'is_olympiad'     => $isOlympiad,
            'is_active'       => true,
            'question_type'   => 'multiple_choice',
            'answer_type'     => $answerType,
            'title'           => $title !== '' ? mb_substr($title, 0, 150) : null,
            'template_text'   => $text,
            'hint_text'       => $hint !== '' ? $hint : null,
            'correct_formula' => $formula,
            'num_config'      => $numConfig,
            'distractors'     => $distractors,
            'conditions'      => $conditions ?: null,
        ];

        // ── სიტყვები: ტექსტში დარჩენილი სახელები/საგნები, რომლებიც ცვლადებშია ან არ არის ──
        $literalHits = $this->literalWordsInVars($text);
        $literalStems = array_map(fn ($h) => self::stem($h['word']), $literalHits);
        foreach ($literalHits as $hit) {
            $warnings[] = "სიტყვა „{$hit['word']}“ ტექსტში პირდაპირაა ჩაწერილი, თუმცა თემატიკა „{$hit['theme']}“-ის ცვლადში {$hit['var']} არსებობს — ჯობს ჩაანაცვლო {{{$hit['var']}}}-ით, რომ კითხვა იცვლებოდეს.";
        }
        foreach ((array) ($q['missing_words'] ?? []) as $mw) {
            $w = is_array($mw) ? ($mw['word'] ?? '') : (string) $mw;
            $sv = is_array($mw) ? ($mw['suggested_var'] ?? '') : '';
            if (trim($w) === '' || in_array(self::stem($w), $literalStems, true)) continue;
            $warnings[] = "სიტყვა „{$w}“ ცვლადებში არ გაქვს" . ($sv ? " (შემოთავაზებული ცვლადი: {$sv})" : '') . ' — დაამატე თემატიკაში, თორემ ტექსტში ყოველთვის ერთი და იგივე დარჩება.';
        }
        if ($theme && $usedThemeVars) {
            foreach ($usedThemeVars as $tv) {
                if (count($themeMap[$tv] ?? []) < 2) $warnings[] = "თემატიკის ცვლადს {$tv} მხოლოდ " . count($themeMap[$tv] ?? []) . ' მნიშვნელობა აქვს — კითხვა ნაკლებად იცვლება.';
            }
        }

        // ── ტექსტში ფიქსირებული რიცხვები ──
        $plain = preg_replace('/\{\{\s*\w+\s*\}\}/u', ' ', $text);
        if (preg_match_all('/\d+/u', $plain, $nm) && $nm[0]) {
            $warnings[] = 'ტექსტში ფიქსირებული რიცხვ(ებ)ია: ' . implode(', ', array_unique($nm[0])) . ' — თუ ამოცანის მონაცემია, ცვლადად აქციე.';
        }

        // ── გამოუყენებელი ცვლადები ──
        $formulaTokens = array_unique($fm[0]);
        foreach ($randomVars as $rv) {
            $inText = in_array($rv, $placeholders, true);
            $inFormula = in_array($rv, $formulaTokens, true) || $this->usedInDerived($rv, $numConfig);
            if (! $inText && ! $inFormula) $warnings[] = "ცვლადი {$rv} არსად გამოიყენება.";
            elseif ($inText && ! $inFormula) $notes[] = "{$rv} ტექსტშია, მაგრამ ფორმულაში არა — ზედმეტი ინფორმაციაა (განზრახ?).";
            elseif (! $inText && $inFormula && ! $this->usedInDerivedShownInText($rv, $numConfig, $placeholders)) $warnings[] = "{$rv} ფორმულაშია, მაგრამ ტექსტში არ ჩანს — ბავშვი მის მნიშვნელობას ვერ გაიგებს.";
        }
        if (! $randomVars && ! array_filter($numConfig, fn ($c) => is_array($c) && ! empty($c['operator']))) {
            $warnings[] = 'შემთხვევითი ცვლადი არ არის — კითხვა და პასუხი ყოველთვის ერთი და იგივე იქნება.';
        }

        // ── ორიგინალი რიცხვებით სწორი პასუხის შემოწმება ──
        $original = is_array($q['original'] ?? null) ? $q['original'] : [];
        if (! $errors && ! empty($original['values']) && array_key_exists('answer', $original)) {
            $fixed = $numConfig;
            $missing = [];
            foreach ($randomVars as $rv) {
                if (! array_key_exists($rv, $original['values'])) { $missing[] = $rv; continue; }
                $v = (int) $original['values'][$rv];
                $fixed[$rv] = ['min' => $v, 'max' => $v, 'step' => 1];
            }
            foreach ($numConfig as $k => $c) {
                if (is_array($c) && ! empty($c['operator']) && isset($original['values'][$k])) $fixed[$k] = ['operator' => true, 'options' => [(string) $original['values'][$k]]];
            }
            unset($fixed['_unique']);
            if ($missing) {
                $warnings[] = 'ორიგინალის შემოწმება ვერ მოხერხდა — original.values-ში აკლია: ' . implode(', ', $missing) . '.';
            } else {
                $res = $this->tryGenerate(array_merge($attrs, ['num_config' => $fixed]), $theme);
                $expected = $this->normalizeAnswer((string) $original['answer']);
                if (! $res['ok']) {
                    $errors[] = 'ორიგინალი რიცხვებით კითხვა ვერ გენერირდა (' . $res['error'] . ') — ფორმულა ან პირობები ორიგინალს არ შეესაბამება.';
                } elseif ($this->normalizeAnswer((string) $res['q']['correct_answer']) !== $expected) {
                    $errors[] = "ორიგინალი რიცხვებით ფორმულა იძლევა „{$res['q']['correct_answer']}“, სწორი პასუხი კი „{$original['answer']}“ — ფორმულა არასწორია.";
                } else {
                    $notes[] = "✓ ორიგინალი რიცხვებით ფორმულა სწორ პასუხს იძლევა ({$original['answer']}).";
                }
            }
        } elseif (! $original) {
            $warnings[] = 'original (ორიგინალი რიცხვები და სწორი პასუხი) არ არის — ფორმულის სისწორე ვერ შემოწმდა.';
        }

        // ── 200 სატესტო გენერაცია ──
        $samples = [];
        $stats = null;
        if (! $errors) {
            $ok = 0; $answers = []; $badOpts = 0; $firstErr = null;
            for ($i = 0; $i < self::RUNS; $i++) {
                $res = $this->tryGenerate($attrs, $theme);
                if (! $res['ok']) { $firstErr ??= $res['error']; continue; }
                $ok++;
                $g = $res['q'];
                $answers[(string) $g['correct_answer']] = true;
                if (count($samples) < 3) $samples[] = $g;
                if ($answerType !== 'numeric_input') {
                    $opts = array_map('strval', (array) ($g['options'] ?? []));
                    if (count($opts) < 2 || count($opts) !== count(array_unique($opts)) || ! in_array((string) $g['correct_answer'], $opts, true)) $badOpts++;
                }
            }
            $stats = ['runs' => self::RUNS, 'ok' => $ok, 'distinct' => count($answers), 'bad_options' => $badOpts];
            if ($ok === 0) $errors[] = 'კითხვა ვერცერთხელ ვერ გენერირდა' . ($firstErr ? " ({$firstErr})" : '') . ' — დიაპაზონები ან პირობები შეუთავსებელია.';
            elseif ($ok < self::RUNS * 0.6) $warnings[] = "გენერაცია მხოლოდ {$ok}/" . self::RUNS . '-ჯერ გამოვიდა — პირობები მეტისმეტად მკაცრია ან დიაპაზონები ვიწრო.';
            if ($ok > 0 && count($answers) < 3 && $randomVars) $warnings[] = 'სწორი პასუხი მხოლოდ ' . count($answers) . ' განსხვავებული გამოდის — გააფართოვე დიაპაზონები.';
            if ($badOpts > 0) $warnings[] = "{$badOpts}-ჯერ ვარიანტები გამეორდა ან სწორი პასუხი ვერ მოხვდა ვარიანტებში.";
            foreach ($samples as $s) {
                if (preg_match('/(^|\s)\?(\s|$)/u', (string) $s['question_text']) && ! str_contains($text, ' ? ')) {
                    $warnings[] = 'ტექსტში შეუვსებელი ცვლადი დარჩა („?“) — შეამოწმე {{…}} სახელები.';
                    break;
                }
            }
        }

        // ── დუბლიკატი ──
        if ($topic && $text !== '') {
            $norm = fn ($s) => preg_replace('/\s+/u', ' ', trim(preg_replace('/\{\{\s*\w+\s*\}\}|\d+/u', '', mb_strtolower($s))));
            $mine = $norm($text);
            foreach (QuestionTemplate::where('topic_id', $topic->id)->get(['id', 'template_text']) as $ex) {
                similar_text($mine, $norm((string) $ex->template_text), $pct);
                if ($pct >= 85) { $warnings[] = 'ძალიან ჰგავს არსებულ კითხვას #' . $ex->id . ' (' . round($pct) . '%) — შესაძლოა დუბლიკატია.'; break; }
            }
        }

        // ოსტატის („ტექსტიდან“) შენიშვნები — მხოლოდ მანამ, სანამ ფორმულა ხელით არ შეცვლილა
        $draft = is_array($q['_draft'] ?? null) ? $q['_draft'] : [];
        $draftTarget = ! empty($draft['formula_var']) ? (string) ($vars[$draft['formula_var']]['derived'] ?? '') : $rawFormula;
        if ($draft && (! isset($draft['formula']) || trim((string) $draft['formula']) === trim($draftTarget))) {
            foreach ((array) ($draft['warnings'] ?? []) as $m) $warnings[] = (string) $m;
            foreach ((array) ($draft['notes'] ?? []) as $m) $notes[] = (string) $m;
        }

        return [
            'index'    => $index,
            'label'    => $label,
            'grade'    => $grade?->name,
            'topic'    => $topic?->name,
            'theme'    => $theme?->name,
            'attrs'    => $attrs,
            'original' => $original['text'] ?? null,
            'errors'   => array_values(array_unique($errors)),
            'warnings' => array_values(array_unique($warnings)),
            'notes'    => array_values(array_unique($notes)),
            'samples'  => $samples,
            'stats'    => $stats,
            'status'   => $errors ? 'error' : ($warnings ? 'warn' : 'ok'),
        ];
    }

    // ─────────────────────────── დამხმარეები ───────────────────────────

    private function tryGenerate(array $attrs, ?Theme $theme): array
    {
        try {
            $t = new QuestionTemplate($attrs);
            $g = $t->generate($theme ?? new Theme());
            if (! isset($g['question_text'], $g['correct_answer'])) return ['ok' => false, 'error' => 'გენერატორმა არასრული შედეგი დააბრუნა'];
            if ((string) $g['correct_answer'] === '' || (string) $g['correct_answer'] === '0') return ['ok' => false, 'error' => 'პასუხი 0 ან ცარიელია'];
            return ['ok' => true, 'q' => $g];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => mb_substr($e->getMessage(), 0, 160)];
        }
    }

    private function normalizeAnswer(string $a): string
    {
        $a = mb_strtolower(trim($a));
        if (preg_match('/-?\d+(?:[.,]\d+)?/', $a, $m) && preg_match('/^[\d\s.,\-]+[^\d]*$/u', $a)) {
            return (string) (0 + str_replace(',', '.', $m[0]));
        }
        return preg_replace('/\s+/u', ' ', $a);
    }

    private function usedInDerived(string $var, array $numConfig): bool
    {
        foreach ($numConfig as $c) {
            if (is_array($c) && isset($c['derived']) && preg_match('/\b' . preg_quote($var, '/') . '\b/', $c['derived'])) return true;
            if (is_array($c) && isset($c['conditional']) && preg_match('/\b' . preg_quote($var, '/') . '\b/', $c['conditional'])) return true;
        }
        return false;
    }

    private function usedInDerivedShownInText(string $var, array $numConfig, array $placeholders): bool
    {
        foreach ($numConfig as $k => $c) {
            if (is_array($c) && isset($c['derived']) && in_array($k, $placeholders, true) && preg_match('/\b' . preg_quote($var, '/') . '\b/', $c['derived'])) return true;
        }
        return false;
    }

    /** ქართული სიტყვის ფუძე: ბრუნვის/მრავლობითის დაბოლოებების მოცილება („ნინიკოს“ → „ნინიკო“). */
    public static function stem(string $w): string
    {
        $w = mb_strtolower(trim($w));
        foreach (['ებისთვის', 'ისთვის', 'ებთან', 'ებმა', 'ებში', 'ებზე', 'ებით', 'ების', 'ებს', 'ები', 'თან', 'ზე', 'ში', 'მა', 'ის', 'ით', 'ად', 'მ', 'ს', 'ო'] as $suf) {
            if (mb_strlen($w) - mb_strlen($suf) >= 3 && str_ends_with($w, $suf)) return mb_substr($w, 0, mb_strlen($w) - mb_strlen($suf));
        }
        if (mb_strlen($w) > 3 && preg_match('/[აი]$/u', $w)) return mb_substr($w, 0, -1);
        return $w;
    }

    private function valueIndex(): array
    {
        if ($this->valueIndex !== null) return $this->valueIndex;
        $idx = [];
        foreach (Theme::with('variables.group')->get() as $th) {
            foreach ($th->variableMap() as $var => $values) {
                foreach ((array) $values as $v) {
                    $v = trim((string) $v);
                    if ($v === '' || mb_strlen($v) < 3 || str_contains($v, ' ')) continue;
                    $idx[self::stem($v)] ??= ['theme' => $th->name, 'var' => $var, 'value' => $v];
                }
            }
        }
        return $this->valueIndex = $idx;
    }

    private function literalWordsInVars(string $text): array
    {
        $plain = preg_replace('/\{\{\s*\w+\s*\}\}/u', ' ', $text);
        preg_match_all('/[\x{10D0}-\x{10FF}]{3,}/u', $plain, $m);
        $idx = $this->valueIndex();
        $hits = [];
        foreach (array_unique($m[0]) as $w) {
            $s = self::stem($w);
            if (isset($idx[$s])) $hits[$s] = ['word' => $w] + $idx[$s];
        }
        return array_values($hits);
    }

    // ─────────────────────────── AI-სთვის ინსტრუქცია ───────────────────────────

    public function buildPrompt(): string
    {
        $grades = Grade::orderBy('number')->get();
        $topics = Topic::orderBy('grade_id')->orderBy('sequence')->get()->groupBy('grade_id');
        $themes = Theme::with('variables.group')->orderBy('name')->get();

        $cat = "კლასები და თემები (გამოიყენე ზუსტად ეს სახელები ან topic_id):\n";
        foreach ($grades as $g) {
            $max = $g->max_level ?: Grade::DEFAULT_MAX_LEVEL;
            $cat .= "- grade {$g->number} ({$g->name}, დონეები 1–{$max}):\n";
            foreach ($topics[$g->id] ?? [] as $t) $cat .= "    • topic_id {$t->id}: {$t->name}\n";
        }
        $cat .= "\nთემატიკები (theme) და მათი ცვლადები — ტექსტში ჩასვი როგორც {{ცვლადი}}:\n";
        foreach ($themes as $th) {
            $cat .= "- theme \"{$th->name}\" (theme_id {$th->id}):\n";
            foreach ($th->variableMap() as $var => $vals) {
                $sample = implode(', ', array_slice(array_map('strval', (array) $vals), 0, 6));
                $cat .= "    • {$var}: {$sample}" . (count((array) $vals) > 6 ? ', …' : '') . "\n";
            }
        }

        $example = json_encode(['questions' => [[
            'title' => 'ვაშლები — გამოკლება',
            'grade' => 2, 'topic' => '[თემის ზუსტი სახელი სიიდან]', 'theme' => '[თემატიკის სახელი ან null]',
            'difficulty' => 1, 'is_olympiad' => false, 'answer_type' => 'numeric',
            'template_text' => '{{NAME}}-ს {{N1}} ვაშლი ჰქონდა, {{N2}} შეჭამა. რამდენი ვაშლი დარჩა?',
            'hint_text' => 'რაც ჰქონდა, გამოაკელი რაც შეჭამა.',
            'correct_formula' => 'N1 - N2',
            'variables' => ['N1' => ['min' => 5, 'max' => 20, 'step' => 1], 'N2' => ['min' => 1, 'max' => 9, 'step' => 1]],
            'unique' => true,
            'conditions' => [['left' => 'N1', 'op' => '>', 'right' => 'N2']],
            'distractors' => ['formulas' => ['N1 + N2', 'N1'], 'min' => 1, 'max' => 3, 'step' => 1],
            'missing_words' => [['word' => 'ვაშლი', 'suggested_var' => 'FRUIT']],
            'original' => ['text' => 'ნიკას 7 ვაშლი ჰქონდა, 3 შეჭამა. რამდენი ვაშლი დარჩა?', 'values' => ['N1' => 7, 'N2' => 3], 'answer' => '4'],
        ]]], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<TXT
შენ ხარ მათემატიკის ამოცანების შაბლონების შემდგენი KidSmart-ისთვის (II–V კლასი, ქართული ენა).
ქვემოთ მოგცემ ამოცანებს. თითოეული ამოცანა გადააქციე შაბლონად, რომელიც ყოველ ჯერზე ახალ რიცხვებს და სახელებს აგენერირებს.
დააბრუნე მხოლოდ JSON (არანაირი ახსნა), ზუსტად ამ ფორმატით:

{$example}

წესები:
1. template_text — ამოცანის ტექსტი, სადაც ყოველი რიცხვი შეცვლილია ცვლადით {{N1}}, {{N2}}…, ხოლო სახელები/საგნები — თემატიკის ცვლადით (მაგ. {{NAME}}), თუ ასეთი ცვლადი სიაში არსებობს. ბრუნვის ნიშანი დაურთე დეფისით: {{NAME}}-ს, {{NAME}}-მ.
2. სიტყვით დაწერილი რიცხვებიც (ოთხი, ათ-ათი, ორ-ორი, ხუთლარიანი, 2 ათეული, ნახევარი) ცვლადებად აქციე, ტექსტში კი ციფრით ჩასვი.
3. variables — ყოველი რიცხვითი ცვლადი: {"min","max","step"}. ცვლადი, რომელიც სხვებით გამოითვლება: {"derived": "N1 + 3"}. ოპერაცია, რომელიც იცვლება: {"operator": true, "options": ["+","-"]}. მნიშვნელობა კონკრეტული სიიდან (მაგ. მონეტები, ფასები): {"values": [1, 2, 5, 10, 20, 50]}.
   ცვლადის სახელი: დიდი ლათინური ასოები/ციფრები; ერთი სახელი მეორის დასაწყისი არ უნდა იყოს (N1 და N12 — არა; გამოიყენე A, B, C ან N1…N9).
4. correct_formula — სწორი პასუხი ცვლადებით (+ - * / და round, floor, ceil, abs, min, max). „რამდენის ყიდვა შეუძლია“ ტიპის ნაშთიანი გაყოფისთვის: floor((A - B) / B). დალაგებისთვის: topsum(k, A, B, C…) — k ყველაზე დიდის ჯამი („3 ყველაზე ძვირი“), botsum(k, …) — k ყველაზე პატარის ჯამი, kmax(k, …) — k-ური უდიდესი, kmin(k, …) — k-ური უმცირესი, median(…) — შუა რიცხვი. პასუხი ყოველთვის დადებითი მთელი რიცხვი უნდა იყოს.
5. conditions — პირობები, რომ ამოცანას აზრი ჰქონდეს: {"left","op","right"}, op: > < >= <= == != %0 (იყოფა უნაშთოდ) !%0. მაგ. გამოკლებისას N1 > N2, ნახევრისთვის {"left":"N1","op":"%0","right":"2"}.
6. დიაპაზონები შეუსაბამე კლასს (II კლასი — ძირითადად 100-მდე) და დონეს (1 — მარტივი, მაღალი დონე — დიდი რიცხვები/მეტი ნაბიჯი).
7. answer_type: "numeric" (4 ვარიანტი), "numeric_input" (ბავშვი წერს პასუხს), "text" (ტექსტური პასუხი — მაშინ distractors: {"vars": ["..."], "count": 3}).
8. distractors.formulas — ტიპური შეცდომები ცვლადებით (მაგ. ზედმეტი მონაცემის ჩათვლა, ნაბიჯის გამოტოვება). თუ ორიგინალს აქვს არასწორი ვარიანტები, გაარკვიე რომელი შეცდომიდან მოდის თითოეული და ის ფორმულად ჩაწერე. min/max — შემთხვევითი გადახრა სწორი პასუხიდან.
9. missing_words — სახელები და საგნები, რომლებიც ტექსტში უცვლელად დატოვე, რადგან თემატიკაში შესაბამისი ცვლადი არ არის. შემოგვთავაზე ცვლადის სახელი.
10. original — ორიგინალი ტექსტი, ორიგინალი რიცხვები ცვლადების მიხედვით (values) და სწორი პასუხი (answer). ეს აუცილებელია: სისტემა ამით ამოწმებს ფორმულას.
11. თუ ამოცანა შაბლონად ვერ იქცევა (მაგ. ჩამოთვლა, რიცხვები არ აქვს, პასუხი ყოველთვის ერთია), questions-ში არ ჩასვა — ჩაწერე იმავე JSON-ის "skipped" სიაში: {"text": "ამოცანის ტექსტი", "reason": "მიზეზი"}.

{$cat}
ჩემი ამოცანები:
[აქ ჩასვი ამოცანები]
TXT;
    }
}
