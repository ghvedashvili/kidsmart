<?php

namespace App\Services;

class DataTableService
{
    /**
     * Rolls a fresh rows×cols matrix of random values and finds the winning
     * row (highest value in $analyzedColIndex; a tie resolves to the lowest
     * row index — deterministic, so unlike CrosswordService::build() this
     * never needs a retry loop: a random fill always has SOME max).
     */
    public static function build(
        int $rows,
        int $cols,
        array $rowLabels,
        array $colHeaders,
        int $analyzedColIndex,
        int $minVal,
        int $maxVal
    ): array {
        $data = [];
        for ($r = 0; $r < $rows; $r++) {
            $row = [];
            for ($c = 0; $c < $cols; $c++) {
                $row[] = rand($minVal, $maxVal);
            }
            $data[] = $row;
        }

        $winnerRowIndex = 0;
        $best = $data[0][$analyzedColIndex] ?? 0;
        for ($r = 1; $r < $rows; $r++) {
            $v = $data[$r][$analyzedColIndex] ?? 0;
            if ($v > $best) {
                $best = $v;
                $winnerRowIndex = $r;
            }
        }

        return ['data' => $data, 'winner_row_index' => $winnerRowIndex];
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
