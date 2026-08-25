<?php

namespace App\Services;

class CrosswordService
{
    public static function build(
        int $minVal,
        int $maxVal,
        array $operators,
        int $rows = 2,
        int $cols = 2,
        int $revealedCount = 0
    ): array {
        $ops = array_values(array_filter($operators));
        if (empty($ops)) $ops = ['+'];

        for ($attempt = 0; $attempt < 300; $attempt++) {
            $cells = [];
            for ($i = 0; $i < $rows * $cols; $i++) {
                $cells[$i] = rand($minVal, $maxVal);
            }

            $rowOps = [];
            $colOps = [];
            for ($r = 0; $r < $rows; $r++) {
                $rowOps[$r] = $ops[array_rand($ops)];
            }
            for ($c = 0; $c < $cols; $c++) {
                $colOps[$c] = $ops[array_rand($ops)];
            }

            $rowResults = [];
            $valid = true;
            for ($r = 0; $r < $rows; $r++) {
                $rowCells = array_slice($cells, $r * $cols, $cols);
                $result   = self::calcChain($rowCells, $rowOps[$r]);
                if ($result === null || $result <= 0) { $valid = false; break; }
                $rowResults[$r] = $result;
            }
            if (! $valid) continue;

            $colResults = [];
            for ($c = 0; $c < $cols; $c++) {
                $colCells = [];
                for ($r = 0; $r < $rows; $r++) {
                    $colCells[] = $cells[$r * $cols + $c];
                }
                $result = self::calcChain($colCells, $colOps[$c]);
                if ($result === null || $result <= 0) { $valid = false; break; }
                $colResults[$c] = $result;
            }
            if (! $valid) continue;

            $revealed = self::pickRevealed(
                $cells, $rowOps, $colOps, $rowResults, $colResults, $rows, $cols,
                max(0, min($rows * $cols - 1, $revealedCount))
            );

            $correctAnswer = [];
            for ($i = 0; $i < $rows * $cols; $i++) {
                $correctAnswer[(string) $i] = $cells[$i];
            }

            return [
                'question_text'  => json_encode([
                    'type'        => 'crossword',
                    'rows'        => $rows,
                    'cols'        => $cols,
                    'row_ops'     => $rowOps,
                    'col_ops'     => $colOps,
                    'row_results' => $rowResults,
                    'col_results' => $colResults,
                    'revealed'    => $revealed,
                ]),
                'correct_answer' => json_encode($correctAnswer),
                'hint_text'      => null,
                'options'        => null,
            ];
        }

        // fallback: pure addition
        $cells = [];
        for ($i = 0; $i < $rows * $cols; $i++) $cells[$i] = rand($minVal, $maxVal);
        $rowOps    = array_fill(0, $rows, '+');
        $colOps    = array_fill(0, $cols, '+');
        $rowResults = [];
        for ($r = 0; $r < $rows; $r++) {
            $s = 0; for ($c = 0; $c < $cols; $c++) $s += $cells[$r * $cols + $c]; $rowResults[$r] = $s;
        }
        $colResults = [];
        for ($c = 0; $c < $cols; $c++) {
            $s = 0; for ($r = 0; $r < $rows; $r++) $s += $cells[$r * $cols + $c]; $colResults[$c] = $s;
        }
        $revealed = self::pickRevealed($cells, $rowOps, $colOps, $rowResults, $colResults, $rows, $cols, $revealedCount);
        $correctAnswer = [];
        for ($i = 0; $i < $rows * $cols; $i++) $correctAnswer[(string) $i] = $cells[$i];
        return [
            'question_text'  => json_encode([
                'type' => 'crossword', 'rows' => $rows, 'cols' => $cols,
                'row_ops' => $rowOps, 'col_ops' => $colOps,
                'row_results' => $rowResults, 'col_results' => $colResults,
                'revealed' => $revealed,
            ]),
            'correct_answer' => json_encode($correctAnswer),
            'hint_text'      => null,
            'options'        => null,
        ];
    }

    private static function pickRevealed(
        array $cells, array $rowOps, array $colOps,
        array $rowResults, array $colResults,
        int $rows, int $cols, int $count
    ): array {
        if ($count <= 0) return [];

        // Revealing an entire row or column makes that equation trivially satisfied —
        // the student learns nothing from it. Cap at the maximum that leaves at least
        // one hidden cell in every row AND every column.
        $maxAllowed = min($rows * ($cols - 1), ($rows - 1) * $cols);
        $count = min($count, $maxAllowed);
        if ($count <= 0) return [];

        $positions = range(0, $rows * $cols - 1);

        // Try from the requested count downward; each step tries many random placements.
        // Going down (never up) because the user said: if the constraint can't be met,
        // reduce the revealed count.
        for ($c = $count; $c >= 1; $c--) {
            for ($attempt = 0; $attempt < 300; $attempt++) {
                $shuffled = $positions;
                shuffle($shuffled);
                $trial = array_slice($shuffled, 0, $c);
                if (self::noFullLine($trial, $rows, $cols) &&
                    self::canSolve($cells, $rowOps, $colOps, $rowResults, $colResults, $rows, $cols, $trial)) {
                    sort($trial);
                    return $trial;
                }
            }
        }

        return [];
    }

    private static function noFullLine(array $revealedIdx, int $rows, int $cols): bool
    {
        $set = array_flip($revealedIdx);
        for ($r = 0; $r < $rows; $r++) {
            $full = true;
            for ($c = 0; $c < $cols; $c++) {
                if (! isset($set[$r * $cols + $c])) { $full = false; break; }
            }
            if ($full) return false;
        }
        for ($c = 0; $c < $cols; $c++) {
            $full = true;
            for ($r = 0; $r < $rows; $r++) {
                if (! isset($set[$r * $cols + $c])) { $full = false; break; }
            }
            if ($full) return false;
        }
        return true;
    }

    private static function canSolve(
        array $cells, array $rowOps, array $colOps,
        array $rowResults, array $colResults,
        int $rows, int $cols, array $revealedIdx
    ): bool {
        $known = array_fill(0, $rows * $cols, null);
        foreach ($revealedIdx as $pos) {
            $known[$pos] = $cells[$pos];
        }

        $changed = true;
        while ($changed) {
            $changed = false;

            for ($r = 0; $r < $rows; $r++) {
                $unknowns = 0;
                for ($c = 0; $c < $cols; $c++) {
                    if ($known[$r * $cols + $c] === null) $unknowns++;
                }
                if ($unknowns === 1) {
                    $vals = [];
                    for ($c = 0; $c < $cols; $c++) $vals[] = $known[$r * $cols + $c];
                    $solved = self::solveForUnknown($vals, $rowOps[$r], $rowResults[$r]);
                    if ($solved !== null) {
                        for ($c = 0; $c < $cols; $c++) {
                            if ($known[$r * $cols + $c] === null) {
                                $known[$r * $cols + $c] = $solved;
                                $changed = true;
                            }
                        }
                    }
                }
            }

            for ($c = 0; $c < $cols; $c++) {
                $unknowns = 0;
                for ($r = 0; $r < $rows; $r++) {
                    if ($known[$r * $cols + $c] === null) $unknowns++;
                }
                if ($unknowns === 1) {
                    $vals = [];
                    for ($r = 0; $r < $rows; $r++) $vals[] = $known[$r * $cols + $c];
                    $solved = self::solveForUnknown($vals, $colOps[$c], $colResults[$c]);
                    if ($solved !== null) {
                        for ($r = 0; $r < $rows; $r++) {
                            if ($known[$r * $cols + $c] === null) {
                                $known[$r * $cols + $c] = $solved;
                                $changed = true;
                            }
                        }
                    }
                }
            }
        }

        return ! in_array(null, $known, true);
    }

    private static function solveForUnknown(array $vals, string $op, int $result): ?int
    {
        $ukIdx = null;
        foreach ($vals as $i => $v) {
            if ($v === null) { $ukIdx = $i; break; }
        }
        if ($ukIdx === null) return null;
        $n = count($vals);

        switch ($op) {
            case '+':
                $sum = 0;
                foreach ($vals as $i => $v) { if ($i !== $ukIdx) $sum += $v; }
                return $result - $sum;

            case '-':
                if ($ukIdx === 0) {
                    $sum = 0;
                    for ($i = 1; $i < $n; $i++) $sum += $vals[$i];
                    return $result + $sum;
                } else {
                    $sumOthers = 0;
                    for ($i = 1; $i < $n; $i++) {
                        if ($i !== $ukIdx) $sumOthers += $vals[$i];
                    }
                    return $vals[0] - $sumOthers - $result;
                }

            case '*': case '×':
                $prod = 1;
                foreach ($vals as $i => $v) { if ($i !== $ukIdx) $prod *= $v; }
                if ($prod === 0 || $result % $prod !== 0) return null;
                return intdiv($result, $prod);

            case '/': case '÷':
                if ($ukIdx === 0) {
                    return $result * $vals[1];
                } else {
                    if ($result === 0 || $vals[0] % $result !== 0) return null;
                    return intdiv($vals[0], $result);
                }
        }
        return null;
    }

    private static function calcChain(array $values, string $op): ?int
    {
        if (count($values) > 2 && in_array($op, ['÷', '/'])) return null;

        $result = (int) $values[0];
        for ($i = 1; $i < count($values); $i++) {
            $v = (int) $values[$i];
            switch ($op) {
                case '+': $result += $v; break;
                case '-':
                    if ($result <= $v) return null;
                    $result -= $v;
                    break;
                case '*': case '×': $result *= $v; break;
                case '/': case '÷':
                    if ($v === 0 || $result % $v !== 0) return null;
                    $result = intdiv($result, $v);
                    break;
            }
        }
        return $result > 0 ? $result : null;
    }

    public static function check(string $correctAnswerJson, array $userInputs): array
    {
        $correct = json_decode($correctAnswerJson, true) ?? [];
        $allOk   = ! empty($correct);
        $results = [];
        foreach ($correct as $pos => $val) {
            $ok            = (int) ($userInputs[$pos] ?? PHP_INT_MIN) === (int) $val;
            $results[$pos] = ['correct' => $ok, 'value' => (int) $val];
            if (! $ok) $allOk = false;
        }
        return ['ok' => $allOk, 'results' => $results];
    }
}
