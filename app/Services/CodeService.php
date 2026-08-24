<?php

namespace App\Services;

class CodeService
{
    private const EMOJI_POOL = [
        '🍎','🍌','🍓','🍊','🍇','🍐','🍍','🍒','🍉','🍋',
        '🍑','🥝','⭐','🌙','💎','🔥','🌊','🎵','🐶','🐱',
        '🐻','🦊','🐼','🌸','🌈','🎯','🏆','🚀','🎸','🦋',
    ];

    /**
     * Build a random code puzzle.
     *
     * @param int    $symbolCount  Number of distinct emoji symbols (2–5)
     * @param int    $minVal       Minimum value for each symbol
     * @param int    $maxVal       Maximum value for each symbol
     * @param array  $operators    Allowed operators: '+', '-', '×', '÷'
     * @param int    $varsPerEq    Variables per chain equation: 2 or 3
     * @param bool   $uniqueValues Whether all symbol values must be distinct
     */
    public static function build(
        int   $symbolCount,
        int   $minVal,
        int   $maxVal,
        array $operators,
        int   $varsPerEq    = 2,
        bool  $uniqueValues = false
    ): array {
        $symbolCount = max(2, min(5, $symbolCount));
        $minVal      = max(1, $minVal);
        $maxVal      = max($minVal + 1, $maxVal);
        if (empty($operators)) $operators = ['+'];

        $pool = self::EMOJI_POOL;
        shuffle($pool);
        $symbols = array_slice($pool, 0, $symbolCount);

        // ── Assign values ────────────────────────────────────────────────
        $values = self::generateValues($symbolCount, $minVal, $maxVal, $uniqueValues);

        // ── Build chain equations ─────────────────────────────────────────
        $equations = [];

        // Anchor equation: uniquely determines S0 without needing any other symbol
        $equations[] = self::anchorEquation($symbols[0], $values[0], $varsPerEq);

        // Chain: each equation introduces one new symbol S(i), solvable from S(i-1)
        for ($i = 1; $i < $symbolCount; $i++) {
            $op = $operators[array_rand($operators)];
            $equations[] = self::chainEquation($symbols[$i - 1], $values[$i - 1], $symbols[$i], $values[$i], $op, $varsPerEq, $minVal, $maxVal, $values);
        }

        // ── Target (reverse order for variety) ───────────────────────────
        $targetIndices = range($symbolCount - 1, 0);
        $targetSymbols = array_map(fn($i) => $symbols[$i], $targetIndices);
        $answers       = [];
        foreach ($targetIndices as $pos => $symIdx) {
            $answers[$pos] = $values[$symIdx];
        }

        return [
            'question_text'  => json_encode([
                'type'           => 'code',
                'symbols'        => $symbols,
                'equations'      => $equations,
                'target'         => $targetSymbols,
                'target_indices' => $targetIndices,
            ]),
            'correct_answer' => json_encode($answers),
            'hint_text'      => null,
            'options'        => null,
        ];
    }

    // ── Check answers ────────────────────────────────────────────────────────
    public static function check(string $correctJson, array $userAnswers): array
    {
        $solutions = json_decode($correctJson, true) ?? [];
        $results   = [];
        $allOk     = !empty($solutions);
        foreach ($solutions as $pos => $val) {
            $ok            = intval($userAnswers[$pos] ?? PHP_INT_MIN) === $val;
            $results[$pos] = ['correct' => $ok, 'value' => $val];
            if (!$ok) $allOk = false;
        }
        return ['ok' => $allOk, 'results' => $results];
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private static function generateValues(int $count, int $min, int $max, bool $unique): array
    {
        $range = range($min, $max);
        if ($unique && count($range) >= $count) {
            shuffle($range);
            return array_slice($range, 0, $count);
        }
        $values = [];
        for ($i = 0; $i < $count; $i++) {
            $values[] = rand($min, $max);
        }
        return $values;
    }

    /**
     * Anchor equation: uniquely determines S0 using only S0 itself.
     * Always uses +, so it's easy to read: S0 + S0 [+ S0] = N*v0.
     */
    private static function anchorEquation(string $sym, int $val, int $varsPerEq): string
    {
        if ($varsPerEq === 3) {
            return "$sym + $sym + $sym = " . (3 * $val);
        }
        return "$sym + $sym = " . (2 * $val);
    }

    /**
     * Chain equation for 2-var: S_prev OP S_new = result.
     * For ÷: S_new is chosen to divide S_prev (value may be replaced in $values).
     */
    private static function chainEquation(
        string $symPrev, int $vPrev,
        string $symNew,  int $vNew,
        string $op,
        int $varsPerEq,
        int $min, int $max,
        array &$values
    ): string {
        if ($varsPerEq === 3) {
            return self::chain3Eq($symPrev, $vPrev, $symNew, $vNew, $op);
        }
        return self::chain2Eq($symPrev, $vPrev, $symNew, $vNew, $op, $min, $max, $values);
    }

    /** 2-variable chain: S_prev OP S_new = result */
    private static function chain2Eq(
        string $sp, int $vp,
        string $sn, int $vn,
        string $op,
        int $min, int $max,
        array &$values
    ): string {
        $symIdx = array_search($sn, array_column([], 'x')); // not used; we use $vn directly

        switch ($op) {
            case '+':
                return "$sp + $sn = " . ($vp + $vn);

            case '-':
                if ($vp >= $vn) return "$sp − $sn = " . ($vp - $vn);
                return "$sn − $sp = " . ($vn - $vp); // swap so result ≥ 0

            case '×':
                return "$sp × $sn = " . ($vp * $vn);

            case '÷':
                // Find divisors of $vp in [$min,$max]; replace vn if possible
                $divisors = [];
                for ($d = $min; $d <= $max; $d++) {
                    if ($vp % $d === 0) $divisors[] = $d;
                }
                if (!empty($divisors)) {
                    $newVn = $divisors[array_rand($divisors)];
                    // patch value in-place (values array passed by ref)
                    foreach ($values as &$v) {
                        if ($v === $vn) { $v = $newVn; break; }
                    }
                    unset($v);
                    return "$sp ÷ $sn = " . ($vp / $newVn);
                }
                // No valid divisor — fall back to +
                return "$sp + $sn = " . ($vp + $vn);

            default:
                return "$sp + $sn = " . ($vp + $vn);
        }
    }

    /**
     * 3-variable chain: pattern S_prev OP S_new OP S_new = result.
     * Given S_prev, student solves for S_new.
     *   + : result = vp + 2*vn  → vn = (result - vp) / 2
     *   − : result = vp − 2*vn  → vn = (vp - result) / 2  (only if vp > 2*vn)
     *   × : result = vp * vn²   → vn = sqrt(result/vp) — kept small
     *   ÷ : falls back to +     (too restrictive for 3-var)
     */
    private static function chain3Eq(
        string $sp, int $vp,
        string $sn, int $vn,
        string $op
    ): string {
        switch ($op) {
            case '+':
                $result = $vp + 2 * $vn;
                return "$sp + $sn + $sn = $result";

            case '-':
                if ($vp > 2 * $vn) {
                    $result = $vp - 2 * $vn;
                    return "$sp − $sn − $sn = $result";
                }
                // Fallback: use + form
                return "$sp + $sn + $sn = " . ($vp + 2 * $vn);

            case '×':
                $result = $vp * $vn * $vn;
                return "$sp × $sn × $sn = $result";

            case '÷':
                // Fall back to +
                return "$sp + $sn + $sn = " . ($vp + 2 * $vn);

            default:
                return "$sp + $sn + $sn = " . ($vp + 2 * $vn);
        }
    }
}
