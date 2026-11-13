<?php

namespace App\Services;

class DataTableService
{
    /**
     * Rolls a fresh rows×cols matrix of random values and finds the winning
     * slot along $answerAxis — a ROW (the original behavior: picking the row
     * with the best combined value across some COLUMNS), or, with
     * $answerAxis='col', a COLUMN (symmetric: picking the column with the
     * best combined value across some ROWS — e.g. "which gender has more
     * children, summed across both clubs?"). $analyzedIndices are indices
     * along the OTHER axis from $answerAxis, combined by $analysisOp into one
     * number per slot; "best" is highest for $winnerMode='max' (default) or
     * lowest for 'min'. A tie still resolves to the lowest index if one
     * happens anyway, but every derived value is retried up to 200 times to
     * come out DISTINCT first — two slots showing the same number would make
     * the "winner" look arbitrary to a child reading the table. Never throws:
     * if $minVal..$maxVal is too narrow to fit that many distinct values
     * (e.g. 8 rows, range of only 2), the last attempt is used as-is, tie and
     * all, rather than blocking generation.
     */
    public static function build(
        int $rows,
        int $cols,
        array $rowLabels,
        array $colHeaders,
        string $answerAxis,
        array $analyzedIndices,
        string $analysisOp,
        string $winnerMode,
        int $minVal,
        int $maxVal
    ): array {
        $isCol = $answerAxis === 'col';
        $slots = $isCol ? $cols : $rows;

        $data = [];
        $winnerIndex = 0;

        for ($attempt = 0; $attempt < 200; $attempt++) {
            $data = [];
            for ($r = 0; $r < $rows; $r++) {
                $row = [];
                for ($c = 0; $c < $cols; $c++) {
                    $row[] = rand($minVal, $maxVal);
                }
                $data[] = $row;
            }

            $derived = [];
            for ($i = 0; $i < $slots; $i++) {
                // Row-axis: combine columns $analyzedIndices within row $i.
                // Col-axis: combine rows $analyzedIndices within column $i.
                $vals = $isCol
                    ? array_map(fn ($r) => (int) ($data[$r][$i] ?? 0), $analyzedIndices)
                    : array_map(fn ($c) => (int) ($data[$i][$c] ?? 0), $analyzedIndices);
                $derived[$i] = self::combine($vals, $analysisOp);
            }

            $winnerIndex = self::pickWinnerIndex($derived, $winnerMode);

            if (count(array_unique($derived)) === $slots) break; // every slot's value is distinct — done
        }

        return ['data' => $data, 'winner_index' => $winnerIndex];
    }

    /**
     * Index of the slot (row or column, whichever is being decided) whose
     * derived value is "best" — highest for $mode='max' (the default), lowest
     * for $mode='min' (e.g. "which club has the FEWEST children?"). A tie
     * resolves to the lowest index either way, for a deterministic result.
     */
    private static function pickWinnerIndex(array $derived, string $mode): int
    {
        $winnerIndex = 0;
        $best = $derived[0];
        $n = count($derived);
        for ($i = 1; $i < $n; $i++) {
            $better = $mode === 'min' ? ($derived[$i] < $best) : ($derived[$i] > $best);
            if ($better) {
                $best = $derived[$i];
                $winnerIndex = $i;
            }
        }
        return $winnerIndex;
    }

    /**
     * Combines a set of values into a single number, per $op:
     *   - 'sum':  total of all of them (e.g. ბიჭი+გოგო = ბავშვები)
     *   - 'diff': the first value minus every other, left to right
     *   - 'max'/'min': the largest/smallest of them
     * With a single value, every op produces that same value — exactly the
     * original "pick one column" (or one row) behavior.
     */
    private static function combine(array $vals, string $op): int
    {
        if (! $vals) return 0;
        return match ($op) {
            'diff' => array_reduce(array_slice($vals, 1), fn ($acc, $v) => $acc - $v, $vals[0]),
            'max'  => max($vals),
            'min'  => min($vals),
            default => array_sum($vals),
        };
    }

    /**
     * Renders the prompt + data table as self-contained HTML — shared by every
     * view that needs to show a data-table question (interactive test-taking,
     * read-only review, teacher preview, PDF export), same contract as
     * ScaleService::renderPansHtml(). $data is the array decoded from a
     * TestQuestion's question_text (see QuestionTemplate::generateDataTable()).
     * Deliberately never reveals the winning row (no highlighting) — a caller
     * that needs to show the answer does so via the question's own existing
     * MC options/correct-answer markup, exactly like the scale renderer stays
     * answer-agnostic too.
     */
    public static function renderTableHtml(array $data): string
    {
        $prompt     = e((string) ($data['prompt'] ?? ''));
        $rowLabels  = $data['row_labels']  ?? [];
        $colHeaders = $data['col_headers'] ?? [];
        $matrix     = $data['data'] ?? [];

        $html  = '<div class="q-text" style="margin-bottom:10px;">📊 ' . $prompt . '</div>';
        $html .= '<div style="overflow-x:auto;">';
        $html .= '<table style="border-collapse:collapse;margin:0 auto;font-size:0.9rem;">';

        $html .= '<tr><th style="border:1px solid #cbd5e1;background:#f1f5f9;padding:6px 10px;"></th>';
        foreach ($colHeaders as $h) {
            $html .= '<th style="border:1px solid #cbd5e1;background:#f1f5f9;padding:6px 10px;">' . e((string) $h) . '</th>';
        }
        $html .= '</tr>';

        foreach ($matrix as $i => $row) {
            $html .= '<tr><th style="border:1px solid #cbd5e1;background:#f8fafc;padding:6px 10px;text-align:left;">' . e((string) ($rowLabels[$i] ?? '')) . '</th>';
            foreach ($row as $val) {
                $html .= '<td style="border:1px solid #cbd5e1;padding:6px 10px;text-align:center;">' . e((string) $val) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table></div>';
        return $html;
    }
}
