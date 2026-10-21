<?php

namespace App\Services;

class ScaleService
{
    /**
     * Builds one balance-scale puzzle. Every symbol in $symbols gets a random
     * hidden weight (rolled fresh each attempt) within its own min/max, EXCEPT
     * the answer symbol when its kind is 'weight' — that one is never pre-assigned,
     * since its value IS the thing being solved for (the deficit itself).
     *
     * $left/$right: [['key' => string, 'count' => int], ...] — fixed pan composition,
     * only the underlying weights are randomized each generation.
     *
     * Retries (same outer-retry pattern used elsewhere in this app) until the pans
     * come out unbalanced AND the deficit resolves to a clean, in-range answer —
     * a whole weight value (weight-kind answer) or a whole, in-range item count
     * (emoji-kind answer, deficit / answerWeight). When a reference symbol is set,
     * its ratio to the answer symbol's weight must ALSO come out whole, since that
     * ratio is shown to the child as the "condition" (e.g. "1🍌 = 2🍐").
     *
     * $conditionsCheck, if given, is called with the rolled $weights map (keyed
     * by symbol key, same as everywhere else here) on every attempt; returning
     * false rejects that attempt and retries, exactly like the other per-attempt
     * checks below. Lets the caller (QuestionTemplate::generateScale()) reuse its
     * own conditionsMet()/evalExpr() engine against the symbols' letter labels
     * without this class needing to know anything about that syntax.
     *
     * @throws \RuntimeException if no attempt resolves within the retry budget.
     */
    public static function build(
        array $symbols,
        array $left,
        array $right,
        string $answerKey,
        ?string $referenceKey,
        int $countMin,
        int $countMax,
        ?callable $conditionsCheck = null
    ): array {
        $answerConf = $symbols[$answerKey] ?? null;
        if (! $answerConf) {
            throw new \RuntimeException("scale: answer_key '{$answerKey}' not found in symbols");
        }
        $answerKind   = $answerConf['kind'] ?? 'emoji';
        $answerSymbol = $answerConf['symbol'] ?? '❓';
        $answerMin    = (int) ($answerConf['min'] ?? 1);
        $answerMax    = max($answerMin, (int) ($answerConf['max'] ?? 20));

        // An emoji-kind answer's weight is never shown directly — without a
        // reference symbol to express it relative to, a child would have no way
        // to ever determine it, no matter how many attempts are retried below.
        // The admin form enforces this too, but a template saved any other way
        // (direct DB edit, a future bug) must still fail safely rather than serve
        // an unsolvable question.
        if ($answerKind === 'emoji' && ! $referenceKey) {
            throw new \RuntimeException('scale: emoji-kind answer requires a reference_key');
        }

        // The answer symbol is never a placed pan item — its "?" marker is attached
        // automatically to whichever side comes out lighter. If it were ALSO used as
        // a real item, a weight-kind answer has no weight rolled for it at all (see
        // above), so every occurrence of it in sumSide() would silently count as 0 —
        // corrupting both pan totals and the on-screen pan display (shown as
        // "undefined", since renderPansHtml()/renderLpScale() look up a weight that
        // was deliberately never assigned).
        foreach (array_merge($left, $right) as $it) {
            if (($it['key'] ?? null) === $answerKey) {
                throw new \RuntimeException("scale: answer_key '{$answerKey}' must not appear in left/right pans");
            }
        }

        for ($attempt = 0; $attempt < 300; $attempt++) {
            $weights = [];
            foreach ($symbols as $key => $conf) {
                if ($key === $answerKey && $answerKind === 'weight') continue;
                $min = (int) ($conf['min'] ?? 1);
                $max = max($min, (int) ($conf['max'] ?? 9));
                $weights[$key] = rand($min, $max);
            }

            if ($conditionsCheck && ! $conditionsCheck($weights)) continue;

            $totalLeft  = self::sumSide($left, $weights);
            $totalRight = self::sumSide($right, $weights);
            $deficit    = $totalLeft - $totalRight;
            if ($deficit === 0) continue;

            $blankSide = $deficit > 0 ? 'right' : 'left';
            $need      = abs($deficit);

            $conditionText = null;

            if ($answerKind === 'weight') {
                if ($need < $answerMin || $need > $answerMax) continue;
                $answerValue = $need;
            } else {
                $aw = $weights[$answerKey] ?? 0;
                if ($aw <= 0 || $need % $aw !== 0) continue;
                $neededCount = intdiv($need, $aw);
                if ($neededCount < $countMin || $neededCount > $countMax) continue;
                $answerValue = $neededCount;

                if ($referenceKey) {
                    $rw = $weights[$referenceKey] ?? 0;
                    if ($rw <= 0 || $aw % $rw !== 0) continue;
                    $ratio = intdiv($aw, $rw);
                    if ($ratio < 1) continue;
                    $refSymbol     = $symbols[$referenceKey]['symbol'] ?? '❓';
                    $conditionText = "1 {$answerSymbol} = {$ratio} {$refSymbol}";
                }
            }

            return [
                'left'           => self::renderItems($left, $symbols, $weights),
                'right'          => self::renderItems($right, $symbols, $weights),
                'blank_side'     => $blankSide,
                'blank_kind'     => $answerKind,
                'blank_symbol'   => $answerKind === 'emoji' ? $answerSymbol : null,
                'condition_text' => $conditionText,
                'answer_value'   => $answerValue,
                'weights'        => $weights,
            ];
        }

        throw new \RuntimeException('scale: no solvable combination found after 300 attempts');
    }

    /**
     * Builds 4 "{count} {symbol}" multiple-choice options for an emoji-kind answer
     * (the child picks both the emoji AND the count together — the question never
     * names which emoji is correct). Distractors are random (symbol,count) pairs
     * from the whole emoji pool, not checked against the puzzle's actual weights —
     * good enough to look plausible without risking a second accidentally-correct
     * option slipping past a cheap check.
     */
    public static function buildEmojiOptions(array $symbols, string $answerKey, int $correctCount, int $countMin, int $countMax): array
    {
        $answerSymbol = $symbols[$answerKey]['symbol'] ?? '❓';
        $correctLabel = "{$correctCount} {$answerSymbol}";

        $emojiKeys = array_keys(array_filter($symbols, fn ($c) => ($c['kind'] ?? 'emoji') === 'emoji'));
        if (! $emojiKeys) $emojiKeys = [$answerKey];

        $wrong = [];
        $attempts = 0;
        while (count($wrong) < 3 && $attempts < 60) {
            $attempts++;
            $key   = $emojiKeys[array_rand($emojiKeys)];
            $cnt   = rand($countMin, $countMax);
            $label = "{$cnt} " . ($symbols[$key]['symbol'] ?? '❓');
            if ($label === $correctLabel || in_array($label, $wrong, true)) continue;
            $wrong[] = $label;
        }

        $delta = 1;
        while (count($wrong) < 3) {
            $label = max(1, $correctCount + $delta) . " {$answerSymbol}";
            if ($label !== $correctLabel && ! in_array($label, $wrong, true)) $wrong[] = $label;
            $delta++;
        }

        $options = array_merge([$correctLabel], $wrong);
        shuffle($options);

        return ['options' => $options, 'correct_answer' => $correctLabel];
    }

    /**
     * Renders the two-pan visual (prompt + left/right boxes + condition hint) as
     * self-contained HTML — shared by every view that needs to show a scale
     * question (interactive test-taking, read-only review, teacher preview) so
     * the box layout only has to be designed once. $data is the array decoded
     * from a TestQuestion's question_text (see QuestionTemplate::generateScale()).
     * Every piece placed into the markup is either a fixed emoji/symbol from an
     * admin-authored template or an escaped string — never raw user input.
     */
    public static function renderPansHtml(array $data): string
    {
        $prompt      = e((string) ($data['prompt'] ?? ''));
        $left        = $data['left']  ?? [];
        $right       = $data['right'] ?? [];
        $blankSide   = $data['blank_side'] ?? 'right';
        $blankKind   = $data['blank_kind'] ?? 'emoji';
        $blankSymbol = $data['blank_symbol'] ?? null;
        $condition   = $data['condition_text'] ?? null;

        $blankChip = self::renderBlankChip($blankKind, $blankSymbol);
        $leftHtml  = self::renderPanHtml($left)  . ($blankSide === 'left'  ? $blankChip : '');
        $rightHtml = self::renderPanHtml($right) . ($blankSide === 'right' ? $blankChip : '');

        // Straight, level beam resting on the triangle — items sit directly ON
        // TOP of it (chips row drawn first, beam right underneath), not tilted
        // and not inside any platform/tray box. nowrap (not wrap) keeps a side's
        // up-to-4 items on one row instead of dropping the 4th to a second line.
        $html  = '<div class="q-text" style="margin-bottom:10px;">⚖️ ' . $prompt . '</div>';
        $html .= '<div style="max-width:340px;margin:0 auto;">';
        $html .= '<div style="display:flex;justify-content:space-between;gap:8px;padding:0 6px;margin-bottom:2px;">';
        $html .= '<div style="flex:1;display:flex;flex-wrap:nowrap;gap:3px;align-items:flex-end;justify-content:center;min-height:34px;overflow:visible;">' . $leftHtml . '</div>';
        $html .= '<div style="flex:1;display:flex;flex-wrap:nowrap;gap:3px;align-items:flex-end;justify-content:center;min-height:34px;overflow:visible;">' . $rightHtml . '</div>';
        $html .= '</div>';
        $html .= self::scaleSvg();
        $html .= '</div>';
        if ($condition) {
            $html .= '<div style="background:#eef2ff;border:1.5px dashed #a5b4fc;border-radius:10px;padding:7px 12px;text-align:center;font-size:0.82rem;font-weight:700;color:#4338ca;margin:10px 0 4px;">💡 ' . e((string) $condition) . '</div>';
        }
        return $html;
    }

    /**
     * The triangle-and-beam scale frame — a straight, level beam resting on a
     * fixed triangle stand. Left/right imbalance is communicated by which side
     * ends up with the "?" blank chip (rendered just above this in renderPansHtml()),
     * not by tilting the beam.
     */
    private static function scaleSvg(): string
    {
        return '<svg viewBox="0 0 280 60" style="display:block;width:100%;height:auto;overflow:visible;">'
            . '<line x1="10" y1="10" x2="270" y2="10" stroke="#1a7a3c" stroke-width="5" stroke-linecap="round"/>'
            . '<circle cx="140" cy="10" r="6" fill="#1a7a3c"/>'
            . '<polygon points="118,58 162,58 140,16" fill="#1a7a3c"/>'
            . '</svg>';
    }

    /**
     * Renders every occurrence of an item as its OWN small flex chip (never a
     * single text blob with the symbol repeated N times) — that's what lets
     * the nowrap row above size and lay out each one individually instead of
     * risking the browser line-wrapping a multi-emoji text string mid-pan.
     */
    private static function renderPanHtml(array $items): string
    {
        $html = '';
        foreach ($items as $it) {
            $kind  = $it['kind'] ?? 'emoji';
            $count = max(1, (int) ($it['count'] ?? 1));
            for ($i = 0; $i < $count; $i++) {
                if ($kind === 'weight') {
                    $val = e((string) ($it['value'] ?? '?'));
                    $html .= '<div style="width:26px;height:26px;border-radius:6px;background:#4f46e5;color:white;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.7rem;flex-shrink:0;">' . $val . '</div>';
                } else {
                    $sym = e((string) ($it['symbol'] ?? '❓'));
                    $html .= '<div style="font-size:1.15rem;line-height:1;flex-shrink:0;">' . $sym . '</div>';
                }
            }
        }
        return $html;
    }

    private static function renderBlankChip(string $kind, ?string $symbol): string
    {
        if ($kind === 'weight') {
            return '<div style="width:26px;height:26px;border-radius:6px;border:2.5px dashed #94a3b8;background:#f8fafc;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.75rem;color:#94a3b8;flex-shrink:0;">?</div>';
        }
        $sym = $symbol ? e($symbol) : '❓';
        return '<div style="font-size:1.15rem;line-height:1;text-align:center;border:2.5px dashed #94a3b8;border-radius:7px;padding:2px 4px;flex-shrink:0;">'
            . $sym . '<span style="font-size:0.5rem;color:#94a3b8;display:block;line-height:1;">?</span></div>';
    }

    private static function sumSide(array $items, array $weights): int
    {
        $total = 0;
        foreach ($items as $it) {
            $key   = $it['key'] ?? null;
            $count = max(1, (int) ($it['count'] ?? 1));
            $total += ($weights[$key] ?? 0) * $count;
        }
        return $total;
    }

    private static function renderItems(array $items, array $symbols, array $weights): array
    {
        $out = [];
        foreach ($items as $it) {
            $key  = $it['key'] ?? null;
            $conf = $symbols[$key] ?? ['kind' => 'emoji', 'symbol' => '❓'];
            $kind = $conf['kind'] ?? 'emoji';
            $out[] = [
                'kind'   => $kind,
                'symbol' => $conf['symbol'] ?? null,
                'value'  => $kind === 'weight' ? ($weights[$key] ?? null) : null,
                'count'  => max(1, (int) ($it['count'] ?? 1)),
            ];
        }
        return $out;
    }
}
