<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\QuestionTemplate;
use App\Models\Theme;
use App\Models\Topic;

/**
 * მასწავლებლის მარტივი ფორმა → კითხვის შაბლონი.
 *
 * მასწავლებელი წერს ჩვეულებრივ კითხვას (რიცხვებით), სწორ პასუხს და — ასარჩევისთვის — არასწორ პასუხებს.
 * „რიცხვები შეიცვალოს“ ჩართულია → TemplateDraftService ცვლადებად აქცევს რიცხვებს და პოულობს ფორმულას
 * (მასწავლებლის პასუხით მოწმდება). თუ ვერ მოხერხდა ან გამორთულია — კითხვა უცვლელი რჩება (ფიქსირებული).
 */
class TeacherQuestionBuilder
{
    public function __construct(
        private TemplateDraftService $draft,
        private TemplateImportService $import,
    ) {}

    /**
     * @param array{text:string, answer:string, wrong:array, mode:string, auto:bool, hint:?string, difficulty:int, topic_id:int, theme_id:?int, is_olympiad:bool} $in
     * @return array{attrs: array, samples: array, auto_used: bool, notes: array, error: ?string}
     */
    public function build(array $in): array
    {
        $topic = Topic::with('grade')->find($in['topic_id']);
        $theme = ! empty($in['theme_id']) ? Theme::find($in['theme_id']) : null;
        $maxLevel = (int) ($topic?->grade?->max_level ?: Grade::DEFAULT_MAX_LEVEL);
        $text = trim(preg_replace("/[ \t]+/u", ' ', (string) $in['text']));
        $answer = trim((string) $in['answer']);
        $wrong = array_values(array_filter(array_map(fn ($w) => trim((string) $w), (array) ($in['wrong'] ?? [])), fn ($w) => $w !== '' && $w !== $answer));
        $wrong = array_values(array_unique($wrong));
        $isChoice = ($in['mode'] ?? 'choice') !== 'input';
        $isNumber = (bool) preg_match('/^\d+$/', $answer);

        if ($text === '') return $this->fail('ჩაწერე კითხვის ტექსტი.');
        if ($answer === '') return $this->fail('ჩაწერე სწორი პასუხი.');
        if (! $isChoice && ! $isNumber) return $this->fail('ჩასაწერ კითხვაზე პასუხი მხოლოდ რიცხვი შეიძლება იყოს (მაგ. 12). ტექსტური პასუხისთვის აირჩიე „ასარჩევი“.');
        if ($isChoice && ! $isNumber && count($wrong) < 2) return $this->fail('ტექსტური პასუხისთვის ჩაწერე მინიმუმ 2 არასწორი პასუხი.');

        $common = [
            'topic_id'      => $topic?->id,
            'theme_id'      => $theme?->id,
            'difficulty'    => max(1, min($maxLevel, (int) ($in['difficulty'] ?? 1))),
            'is_olympiad'   => (bool) ($in['is_olympiad'] ?? false),
            'question_type' => 'multiple_choice',
            'hint_text'     => trim((string) ($in['hint'] ?? '')) ?: null,
            'title'         => $this->titleFrom($text),
        ];
        $notes = [];

        // ── რიცხვები ავტომატურად იცვლება ──
        if (! empty($in['auto'])) {
            $block = $text . "\nპასუხი: " . $answer;
            if ($isChoice && $wrong) {
                $letters = ['ა', 'ბ', 'გ', 'დ', 'ე', 'ვ'];
                $opts = array_merge([$answer], $wrong);
                $block .= "\n" . implode('   ', array_map(fn ($o, $i) => $letters[$i] . ') ' . $o, $opts, array_keys($opts)));
            }
            $q = $this->draft->draftOne($block, $theme, $maxLevel);
            if (! isset($q['skip'])) {
                // მასწავლებლის შესწორებები ოსტატის შეთავაზებაზე: ფორმულა და რიცხვების საზღვრები
                $overridden = false;
                $fOv = trim((string) ($in['formula'] ?? ''));
                if ($fOv !== '') { $q = $this->draft->refit($q, $fOv); $overridden = true; }
                foreach ((array) ($in['ranges'] ?? []) as $name => $r) {
                    $cur = $q['variables'][$name] ?? null;
                    if (! is_array($cur) || ! isset($cur['min'], $cur['max']) || ! is_array($r)) continue;
                    $orig = (int) ($q['original']['values'][$name] ?? $cur['min']);
                    $min = isset($r['min']) && is_numeric($r['min']) ? max(0, (int) $r['min']) : $cur['min'];
                    $max = isset($r['max']) && is_numeric($r['max']) ? (int) $r['max'] : $cur['max'];
                    if ($min > $max) [$min, $max] = [$max, $min];
                    $q['variables'][$name] = ['min' => min($min, $orig), 'max' => max($max, $orig), 'step' => max(1, (int) ($cur['step'] ?? 1))];
                    $overridden = true;
                }
                if (! $isChoice && $q['answer_type'] === 'numeric') $q['answer_type'] = 'numeric_input';
                $q['difficulty'] = $common['difficulty'];
                $q['hint_text'] = $common['hint_text'] ?? '';
                $q['is_olympiad'] = $common['is_olympiad'];
                $q['topic_id'] = $common['topic_id'];
                if ($theme) $q['theme_id'] = $theme->id;
                $item = $this->import->check($q, 0, []);
                if ($item['status'] !== 'error' && ($item['stats']['ok'] ?? 0) >= 150) {
                    $attrs = array_merge($item['attrs'], ['title' => $common['title'], 'question_type' => 'multiple_choice']);
                    $notes = array_merge($item['warnings'], $item['notes']);
                    return ['attrs' => $attrs, 'samples' => $this->samples($attrs, $theme), 'auto_used' => true, 'notes' => $notes, 'error' => null,
                        'formula' => $q['correct_formula'], 'variables' => $q['original']['values'] ?? [], 'wizard' => $this->wizardView($q, $answer)];
                }
                // მასწავლებლის შესწორება არ გამოდგა — ვუთხრათ რატომ (ფიქსირებულზე ჩუმად არ გადავიდეთ)
                if ($overridden) {
                    $why = $item['errors'][0] ?? null;
                    // „ორიგინალი რიცხვებით ფორმულა იძლევა „10“, სწორი პასუხი კი „4“…“ → მასწავლებლისთვის გასაგებად
                    if ($why && preg_match('/იძლევა „([^“]+)“, სწორი პასუხი კი „([^“]+)“/u', $why, $wm)) {
                        $why = "ამ ფორმულით შენი რიცხვებიდან გამოდის {$wm[1]}, შენ კი სწორ პასუხად {$wm[2]} ჩაწერე. შეასწორე ფორმულა ან აირჩიე სხვა ვარიანტი.";
                    }
                    if (! $why && ($item['stats']['ok'] ?? 0) < 150) $why = 'ამ საზღვრებით კითხვა ხშირად ვერ იქმნება (პასუხი უარყოფითი ან წილადი გამოდის) — გააფართოვე საზღვრები ან შეცვალე ფორმულა.';
                    $res = $this->fail($why ?? 'შესწორება ვერ შემოწმდა.');
                    $res['wizard'] = $this->wizardView($q, $answer);
                    return $res;
                }
                $notes[] = 'ავტომატური ცვლადები ვერ შემოწმდა (' . implode('; ', array_slice($item['errors'] ?: $item['warnings'], 0, 2)) . ') — კითხვა ფიქსირებული რიცხვებით შეინახა.';
            } else {
                $notes[] = 'რიცხვების ავტომატური შეცვლა ვერ მოხერხდა: ' . $q['skip'] . ' კითხვა ფიქსირებული რიცხვებით შეინახა.';
            }
        }

        // ── ფიქსირებული კითხვა: ბავშვი ზუსტად ამ ტექსტს ნახავს ──
        $tpl = str_replace(['{{', '}}'], ['{ {', '} }'], $text);
        if ($isNumber) {
            $wrongNums = array_values(array_filter($wrong, fn ($w) => preg_match('/^\d+$/', $w)));
            $attrs = $common + [
                'template_text'   => $tpl,
                'answer_type'     => $isChoice ? 'numeric' : 'numeric_input',
                'correct_formula' => (string) (int) $answer,
                'num_config'      => [],
                'conditions'      => [],
                'distractors'     => ['formulas' => array_map(fn ($w) => (string) (int) $w, array_slice($wrongNums, 0, 3)), 'min' => 1, 'max' => 3, 'step' => 1],
            ];
            if ($isChoice && count($wrongNums) < 3) $notes[] = 'დანარჩენი არასწორი პასუხები ავტომატურად შეირჩევა სწორი პასუხის ახლოს.';
        } else {
            $attrs = $common + [
                'template_text'   => $tpl,
                'answer_type'     => 'text',
                'correct_formula' => $answer,
                'num_config'      => [],
                'conditions'      => [],
                'distractors'     => ['vars' => array_slice($wrong, 0, 5), 'count' => min(3, count($wrong))],
            ];
        }
        return ['attrs' => $attrs, 'samples' => $this->samples($attrs, $theme), 'auto_used' => false, 'notes' => $notes, 'error' => null, 'formula' => null, 'variables' => [], 'wizard' => null];
    }

    /** ოსტატის შეთავაზება მასწავლებლისთვის: ტექსტი ცვლადებით, ფორმულა, ალტერნატივები, საზღვრები */
    private function wizardView(array $q, string $answer): array
    {
        $d = (array) ($q['_draft'] ?? []);
        $fv = $d['formula_var'] ?? null;
        $vals = (array) ($q['original']['values'] ?? []);
        $vars = [];
        foreach ((array) ($q['variables'] ?? []) as $name => $c) {
            if (! is_array($c)) continue;
            if (isset($c['min'], $c['max'])) $vars[] = ['name' => $name, 'value' => $vals[$name] ?? null, 'min' => (int) $c['min'], 'max' => (int) $c['max'], 'step' => (int) ($c['step'] ?? 1), 'kind' => 'range'];
            elseif (isset($c['values'])) $vars[] = ['name' => $name, 'value' => $vals[$name] ?? null, 'list' => array_values((array) $c['values']), 'kind' => 'list'];
        }
        preg_match('/\d+/', $answer, $am);
        return [
            'template'    => (string) ($q['template_text'] ?? ''),
            'formula'     => $fv ? (string) ($q['variables'][$fv]['derived'] ?? '') : (string) ($q['correct_formula'] ?? ''),
            'formula_var' => $fv,
            'answer_tpl'  => $fv ? (string) ($q['correct_formula'] ?? '') : null,
            'alts'        => array_values((array) ($d['alts'] ?? [])),
            'vars'        => $vars,
            'values'      => (object) $vals,
            'target'      => isset($am[0]) ? (int) $am[0] : null,
            'distractors' => array_values((array) ($q['distractors']['formulas'] ?? [])),
        ];
    }

    /** 3 ვარიანტი, როგორც ბავშვი ნახავს */
    public function samples(array $attrs, ?Theme $theme, int $n = 3): array
    {
        $out = [];
        $seen = [];
        for ($i = 0; $i < $n * 4 && count($out) < $n; $i++) {
            try {
                $g = (new QuestionTemplate($attrs))->generate($theme ?? new Theme());
            } catch (\Throwable $e) {
                continue;
            }
            $key = ($g['question_text'] ?? '') . '|' . ($g['correct_answer'] ?? '');
            if (isset($seen[$key]) && $i < $n * 3) continue;
            $seen[$key] = true;
            $out[] = [
                'text'    => (string) ($g['question_text'] ?? ''),
                'hint'    => $g['hint_text'] ?? null,
                'options' => array_values((array) ($g['options'] ?? [])),
                'correct' => (string) ($g['correct_answer'] ?? ''),
                'input'   => ($attrs['answer_type'] ?? '') === 'numeric_input',
            ];
        }
        return $out;
    }

    /** ინტერაქტიული ტიპები (პირამიდა, კოდი, კროსვორდი, სასწორი, ცხრილი) — ბავშვი მათ მარტივ ბარათად არ ხედავს */
    public const INTERACTIVE = ['pyramid' => 'პირამიდა', 'code' => 'კოდი', 'crossword' => 'კროსვორდი', 'scale' => 'სასწორი', 'data_table' => 'ცხრილი'];

    /**
     * ინტერაქტიული კითხვის n ვარიანტი — generate()-ის სრული შედეგი question_type-ით,
     * teacher/partials/interactive-card-ისთვის (იგივე რენდერი, რაც ადმინის ტესტის გადახედვაში).
     */
    public function interactiveSamples(QuestionTemplate $q, int $n = 3): array
    {
        $out = [];
        $seen = [];
        for ($i = 0; $i < $n * 4 && count($out) < $n; $i++) {
            try {
                $g = $q->generate($q->theme ?? Theme::first());
            } catch (\Throwable $e) {
                continue;
            }
            $key = ($g['question_text'] ?? '') . '|' . json_encode($g['correct_answer'] ?? '');
            if (isset($seen[$key]) && $i < $n * 3) continue;
            $seen[$key] = true;
            $out[] = array_merge($g, ['question_type' => $q->question_type ?? 'multiple_choice']);
        }
        return $out;
    }

    private function fail(string $msg): array
    {
        return ['attrs' => [], 'samples' => [], 'auto_used' => false, 'notes' => [], 'error' => $msg, 'formula' => null, 'variables' => [], 'wizard' => null];
    }

    private function titleFrom(string $text): string
    {
        $plain = trim(preg_replace('/\s+/u', ' ', preg_replace('/[^\x{10D0}-\x{10FF}0-9\s]/u', ' ', $text)));
        return mb_substr(implode(' ', array_slice(explode(' ', $plain), 0, 6)), 0, 80) ?: 'კითხვა';
    }
}
