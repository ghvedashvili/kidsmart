<?php

namespace App\Services;

class ScaleService
{
    /**
     * Builds one balance-scale puzzle. The pans ($left/$right) are the admin's
     * fixed composition. The answer symbol is NOT admin-picked — it's
     * automatically chosen, per attempt, from ANY defined symbol (on a pan or
     * not), trying them in random order until one resolves into a clean,
     * in-range value:
     *   - emoji-kind candidate: deficit / itsWeight, as a whole, in-range COUNT
     *     to ADD — works identically whether it's already on a pan too (e.g.
     *     the right pan already has 3⭐, answer is "2 more ⭐") or not, since
     *     its weight is always rolled and already correctly baked into the
     *     pan totals either way.
     *   - weight-kind candidate NOT on any pan: the deficit itself (classic
     *     shortcut, its "?" is a separately-appended blank chip).
     *   - weight-kind candidate ALSO on a pan, but on ONLY ONE side: solved
     *     ALGEBRAICALLY — its own occurrences are excluded from that side's
     *     "known" sum, leaving (cLeft - cRight) copies of the unknown X, so
     *     X = (knownRight - knownLeft) / (cLeft - cRight). Its "?" is
     *     rendered IN PLACE on the pan instead of appended separately. If it
     *     occurs on BOTH pans (with different counts — equal counts cancel
     *     out of the deficit and fall to the classic-shortcut branch below),
     *     that candidate is skipped entirely rather than solved: blanking
     *     every occurrence on both sides at once would make the "?" appear
     *     twice, once per side, which reads as two separate unknowns.
     *
     * An emoji-kind candidate's weight is never shown to the child directly, so
     * it's only eligible when $hasCustomPrompt is true — trusting the admin to
     * have written the needed relationship themselves (e.g. "{{A}} = 2×{{B}}")
     * into the template_text, since there's no more admin-picked "reference
     * symbol" to auto-generate that hint from.
     *
     * $conditionsCheck, if given, is called with the rolled $weights map (keyed
     * by symbol key) on every attempt; returning false rejects that attempt and
     * retries, exactly like the other per-attempt checks below. Lets the caller
     * (QuestionTemplate::generateScale()) reuse its own conditionsMet()/evalExpr()
     * engine against the symbols' letter labels without this class needing to
     * know anything about that syntax. Note: for a weight-kind symbol that ends
     * up winning as an on-pan (algebraic) answer, its condition-check value is
     * still its throwaway provisional roll, not the solved X — a condition that
     * specifically targets that exact combination is a known edge case this
     * doesn't resolve perfectly.
     *
     * @throws \RuntimeException if no candidate symbol exists, or no attempt
     *     resolves within the retry budget.
     */
    public static function build(
        array $symbols,
        array $left,
        array $right,
        int $countMin,
        int $countMax,
        bool $hasCustomPrompt,
        ?callable $conditionsCheck = null
    ): array {
        $candidates = [];
        foreach ($symbols as $key => $conf) {
            $kind = $conf['kind'] ?? 'emoji';
            if ($kind === 'emoji' && ! $hasCustomPrompt) continue;
            $candidates[] = $key;
        }
        if (! $candidates) {
            throw new \RuntimeException(
                'scale: no eligible answer symbol — add a weight-kind symbol'
                . ($hasCustomPrompt ? '' : ', or write a custom template_text to also allow an emoji-kind one')
            );
        }

        for ($attempt = 0; $attempt < 300; $attempt++) {
            // Every symbol gets a provisional rolled weight — used directly for
            // an emoji-kind symbol (always), and as a weight-kind symbol's own
            // pan value for whenever it ISN'T the candidate being tried below
            // (when it IS, this provisional roll is set aside entirely in favor
            // of solving for its true value).
            $weights = [];
            foreach ($symbols as $key => $conf) {
                $min = (int) ($conf['min'] ?? 1);
                $max = max($min, (int) ($conf['max'] ?? 9));
                $weights[$key] = rand($min, $max);
            }

            $totalLeft  = self::sumSide($left, $weights);
            $totalRight = self::sumSide($right, $weights);
            $deficit    = $totalLeft - $totalRight;
            if ($deficit === 0) continue;

            $blankSide = $deficit > 0 ? 'right' : 'left';
            $need      = abs($deficit);

            $pool = $candidates;
            shuffle($pool);
            foreach ($pool as $answerKey) {
                $conf = $symbols[$answerKey];
                $kind = $conf['kind'] ?? 'emoji';
                $cLeft  = self::countOf($left, $answerKey);
                $cRight = self::countOf($right, $answerKey);
                $coef   = $cLeft - $cRight;
                $inline = false;
                $thisBlankSide = $blankSide;

                if ($kind === 'weight' && $coef !== 0 && ($cLeft === 0 || $cRight === 0)) {
                    $knownLeft  = $totalLeft  - $weights[$answerKey] * $cLeft;
                    $knownRight = $totalRight - $weights[$answerKey] * $cRight;
                    $diff = $knownRight - $knownLeft;
                    if ($diff % $coef !== 0) continue;
                    $x = intdiv($diff, $coef);
                    $min = (int) ($conf['min'] ?? 1);
                    $max = max($min, (int) ($conf['max'] ?? 20));
                    if ($x < $min || $x > $max) continue;
                    $answerValue   = $x;
                    $inline        = true;
                    $thisBlankSide = null;
                } elseif ($kind === 'weight' && $coef !== 0) {
                    continue; // on both pans with different counts — "?" would show on both sides, skip
                } elseif ($kind === 'weight') {
                    $min = (int) ($conf['min'] ?? 1);
                    $max = max($min, (int) ($conf['max'] ?? 20));
                    if ($need < $min || $need > $max) continue;
                    $answerValue = $need;
                } else {
                    $aw = $weights[$answerKey] ?? 0;
                    if ($aw <= 0 || $need % $aw !== 0) continue;
                    $cnt = intdiv($need, $aw);
                    if ($cnt < $countMin || $cnt > $countMax) continue;
                    $answerValue = $cnt;
                }

                // Conditions must be checked against each symbol's REAL final
                // value, not the provisional roll — for a weight-kind winner
                // (inline-solved or the classic deficit answer), its provisional
                // roll was never actually used/shown, so it's swapped out here.
                if ($conditionsCheck) {
                    $checkWeights = $weights;
                    if ($kind === 'weight') {
                        $checkWeights[$answerKey] = $answerValue;
                    }
                    if (! $conditionsCheck($checkWeights)) continue;
                }

                return [
                    'left'         => self::renderItems($left, $symbols, $weights, $inline ? $answerKey : null),
                    'right'        => self::renderItems($right, $symbols, $weights, $inline ? $answerKey : null),
                    'blank_side'   => $thisBlankSide,
                    'blank_kind'   => $kind,
                    'blank_symbol' => $kind === 'emoji' ? ($conf['symbol'] ?? '❓') : null,
                    'answer_key'   => $answerKey,
                    'answer_value' => $answerValue,
                    'weights'      => $weights,
                ];
            }
            // none of the candidates resolved this deficit — retry with fresh weights
        }

        throw new \RuntimeException('scale: no solvable combination found after 300 attempts');
    }

    /**
     * Builds 4 multiple-choice options for an emoji-kind answer, each a symbol
     * repeated N times with no digit (e.g. "⭐⭐", never "2⭐") — matching how
     * symbols are shown on the pans. Distractors are drawn from every emoji-kind
     * symbol in the template, INCLUDING ones already placed on a pan — the
     * generator already knows each one's real rolled weight, so there's no
     * reason not to reuse them instead of requiring the admin to add an unused
     * symbol just for variety. Ranked by how close a candidate's TOTAL weight
     * comes to the actual deficit, so wrong answers look like genuine near-misses
     * rather than arbitrary noise. Different symbols are preferred over
     * different counts of the same one, as long as there are enough of them.
     */
    public static function buildEmojiOptions(array $symbols, string $answerKey, int $correctCount, int $countMin, int $countMax, array $weights): array
    {
        $answerSymbol = $symbols[$answerKey]['symbol'] ?? '❓';
        $label = fn (string $symbol, int $n) => str_repeat($symbol, max(1, $n));
        $correctLabel = $label($answerSymbol, $correctCount);
        $need = $correctCount * max(1, (int) ($weights[$answerKey] ?? 1));

        $pool = array_keys(array_filter($symbols, fn ($c) => ($c['kind'] ?? 'emoji') === 'emoji'));
        if (! $pool) $pool = [$answerKey];

        $ranked = [];
        foreach ($pool as $key) {
            $w = max(1, (int) ($weights[$key] ?? 1));
            for ($cnt = $countMin; $cnt <= $countMax; $cnt++) {
                $lbl = $label($symbols[$key]['symbol'] ?? '❓', $cnt);
                if ($lbl === $correctLabel) continue;
                $total = $cnt * $w;
                // A different symbol can happen to share the same rolled weight
                // as the answer symbol (e.g. both weigh 5) — if this candidate's
                // TOTAL also happens to equal the actual deficit, it would
                // genuinely balance the scale too, so it's a second correct
                // answer in disguise, not a wrong one. Must be excluded, not
                // just the one matching the correct LABEL string.
                if ($total === $need) continue;
                $ranked[] = ['label' => $lbl, 'key' => $key, 'dev' => abs($total - $need)];
            }
        }
        usort($ranked, fn ($a, $b) => $a['dev'] <=> $b['dev']);

        // First pass: closest candidate per DISTINCT symbol, for variety.
        $wrong = [];
        $usedKeys = [];
        foreach ($ranked as $cand) {
            if (count($wrong) >= 3) break;
            if (in_array($cand['key'], $usedKeys, true) || in_array($cand['label'], $wrong, true)) continue;
            $wrong[] = $cand['label'];
            $usedKeys[] = $cand['key'];
        }
        // Second pass: not enough distinct symbols in the pool — fill remaining
        // slots with the next-closest candidates regardless of symbol repeats.
        foreach ($ranked as $cand) {
            if (count($wrong) >= 3) break;
            if (in_array($cand['label'], $wrong, true)) continue;
            $wrong[] = $cand['label'];
        }
        // Last-resort fallback — pool/count range too narrow to find 3 at all.
        $delta = 1;
        while (count($wrong) < 3) {
            $cand = $label($answerSymbol, $correctCount + $delta);
            if ($cand !== $correctLabel && ! in_array($cand, $wrong, true)) $wrong[] = $cand;
            $delta++;
        }

        $options = array_merge([$correctLabel], $wrong);
        shuffle($options);

        return ['options' => $options, 'correct_answer' => $correctLabel];
    }

    /**
     * Renders the two-pan visual (prompt + left/right boxes) as self-contained
     * HTML — shared by every view that needs to show a scale question
     * (interactive test-taking, read-only review, teacher preview) so the box
     * layout only has to be designed once. $data is the array decoded from a
     * TestQuestion's question_text (see QuestionTemplate::generateScale()).
     * Every piece placed into the markup is either a fixed emoji/symbol from an
     * admin-authored template or an escaped string — never raw user input.
     */
    public static function renderPansHtml(array $data): string
    {
        $prompt      = e((string) ($data['prompt'] ?? ''));
        $left        = $data['left']  ?? [];
        $right       = $data['right'] ?? [];
        // array_key_exists, not ?? — build() deliberately stores blank_side as
        // NULL (not merely absent) when the answer is already rendered in place
        // on a pan (the inline/algebraic case), meaning no separate chip at all.
        // ?? treats that stored null the same as a missing key and falls back to
        // 'right', which wrongly appends a second "?" there on top of the real
        // in-place one — see renderItems()'s 'blank' flag for the correct one.
        $blankSide   = array_key_exists('blank_side', $data) ? $data['blank_side'] : 'right';
        $blankKind   = $data['blank_kind'] ?? 'emoji';
        $blankSymbol = $data['blank_symbol'] ?? null;

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
                if (! empty($it['blank'])) {
                    // Weight-kind answer symbol placed inline on a pan (see
                    // build()'s algebraic branch) — same dashed "?" plate as the
                    // separately-appended blank, just rendered in-place instead.
                    $html .= '<div style="width:26px;height:26px;border-radius:6px;border:2.5px dashed #94a3b8;background:#f8fafc;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.75rem;color:#94a3b8;flex-shrink:0;">?</div>';
                } elseif ($kind === 'weight') {
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
        // MC mode never names the answer symbol in advance (null $symbol —
        // that's the whole point of offering 4 "{emoji}×N" choices), same as
        // a weight-kind blank — a single centered "?" is the whole clue.
        // Falling back to the generic "❓" emoji AND still appending the
        // small "how many?" subscript below it prints two question marks.
        if ($kind === 'weight' || ! $symbol) {
            return '<div style="width:26px;height:26px;border-radius:6px;border:2.5px dashed #94a3b8;background:#f8fafc;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.95rem;color:#94a3b8;flex-shrink:0;">?</div>';
        }
        return '<div style="font-size:1.15rem;line-height:1;text-align:center;border:2.5px dashed #94a3b8;border-radius:7px;padding:2px 4px;flex-shrink:0;">'
            . e($symbol) . '<span style="font-size:0.5rem;color:#94a3b8;display:block;line-height:1;">?</span></div>';
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

    /**
     * $inlineAnswerKey, when given, marks every pan item matching it as the
     * unlabeled "?" answer slot (its weight is never shown — it's what's being
     * solved for) instead of a normal numbered plate, for a weight-kind answer
     * that's also a placed pan item (see build()'s algebraic branch above).
     */
    private static function renderItems(array $items, array $symbols, array $weights, ?string $inlineAnswerKey = null): array
    {
        $out = [];
        foreach ($items as $it) {
            $key    = $it['key'] ?? null;
            $conf   = $symbols[$key] ?? ['kind' => 'emoji', 'symbol' => '❓'];
            $kind   = $conf['kind'] ?? 'emoji';
            $isBlank = $inlineAnswerKey !== null && $key === $inlineAnswerKey;
            $out[] = [
                'kind'   => $kind,
                'symbol' => $conf['symbol'] ?? null,
                'value'  => ($kind === 'weight' && ! $isBlank) ? ($weights[$key] ?? null) : null,
                'count'  => max(1, (int) ($it['count'] ?? 1)),
                'blank'  => $isBlank,
            ];
        }
        return $out;
    }

    private static function countOf(array $items, string $key): int
    {
        $total = 0;
        foreach ($items as $it) {
            if (($it['key'] ?? null) === $key) $total += max(1, (int) ($it['count'] ?? 1));
        }
        return $total;
    }
}
