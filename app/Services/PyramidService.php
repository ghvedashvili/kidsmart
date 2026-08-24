<?php

namespace App\Services;

class PyramidService
{
    /**
     * Build a random pyramid and pick hidden cells, retrying until uniquely solvable.
     * Returns ['rows' => $displayRows, 'solutions' => $solutions].
     */
    public static function build(int $height, int $maxBase, int $hiddenCount): array
    {
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $base = [];
            for ($i = 0; $i < $height; $i++) {
                $base[] = rand(1, $maxBase);
            }

            $rowsBottomUp = [$base];
            $curr = $base;
            while (count($curr) > 1) {
                $next = [];
                for ($i = 0; $i < count($curr) - 1; $i++) {
                    $next[] = $curr[$i] + $curr[$i + 1];
                }
                $rowsBottomUp[] = $next;
                $curr = $next;
            }
            $rows = array_reverse($rowsBottomUp); // rows[0]=top

            $allPos = [];
            foreach ($rows as $r => $row) {
                foreach (array_keys($row) as $c) {
                    $allPos[] = "$r,$c";
                }
            }
            shuffle($allPos);
            $hiddenSet = array_slice($allPos, 0, min($hiddenCount, count($allPos)));

            if (!self::isSolvable($rows, $hiddenSet)) {
                continue;
            }

            $displayRows = [];
            $solutions   = [];
            foreach ($rows as $r => $row) {
                $dispRow = [];
                foreach ($row as $c => $val) {
                    $pos = "$r,$c";
                    if (in_array($pos, $hiddenSet, true)) {
                        $dispRow[]       = null;
                        $solutions[$pos] = $val;
                    } else {
                        $dispRow[] = $val;
                    }
                }
                $displayRows[] = $dispRow;
            }

            return ['rows' => $displayRows, 'solutions' => $solutions];
        }

        // Fallback: no hidden cells (always solvable)
        $base = [];
        for ($i = 0; $i < $height; $i++) {
            $base[] = rand(1, $maxBase);
        }
        $rowsBottomUp = [$base];
        $curr = $base;
        while (count($curr) > 1) {
            $next = [];
            for ($i = 0; $i < count($curr) - 1; $i++) {
                $next[] = $curr[$i] + $curr[$i + 1];
            }
            $rowsBottomUp[] = $next;
            $curr = $next;
        }
        $rows = array_reverse($rowsBottomUp);
        $displayRows = array_map(fn($row) => array_values($row), $rows);
        return ['rows' => $displayRows, 'solutions' => []];
    }

    /**
     * Check if all hidden cells can be uniquely determined from visible cells
     * using bottom-up and top-down propagation.
     *
     * rows[0] = top (1 cell), rows[H-1] = bottom (H cells).
     * Relationship: rows[r][c] = rows[r+1][c] + rows[r+1][c+1]
     */
    public static function isSolvable(array $rows, array $hiddenSet): bool
    {
        $H = count($rows);
        $hidden = array_flip($hiddenSet); // for O(1) lookup

        // known[r][c] = true if value is determinable
        $known = [];
        foreach ($rows as $r => $row) {
            foreach (array_keys($row) as $c) {
                $known[$r][$c] = !isset($hidden["$r,$c"]);
            }
        }

        $changed = true;
        while ($changed) {
            $changed = false;
            foreach ($rows as $r => $row) {
                foreach (array_keys($row) as $c) {
                    if ($known[$r][$c]) continue;

                    // Bottom-up: both children known → parent known
                    if (isset($rows[$r + 1][$c]) && isset($rows[$r + 1][$c + 1])
                        && $known[$r + 1][$c] && $known[$r + 1][$c + 1]) {
                        $known[$r][$c] = true;
                        $changed = true;
                        continue;
                    }
                    // Top-down left child: parent (r-1,c) + right sibling (r,c+1) known
                    if ($r > 0 && isset($known[$r - 1][$c]) && isset($known[$r][$c + 1])
                        && $known[$r - 1][$c] && $known[$r][$c + 1]) {
                        $known[$r][$c] = true;
                        $changed = true;
                        continue;
                    }
                    // Top-down right child: parent (r-1,c-1) + left sibling (r,c-1) known
                    if ($r > 0 && $c > 0 && isset($known[$r - 1][$c - 1]) && isset($known[$r][$c - 1])
                        && $known[$r - 1][$c - 1] && $known[$r][$c - 1]) {
                        $known[$r][$c] = true;
                        $changed = true;
                        continue;
                    }
                }
            }
        }

        foreach ($known as $row) {
            foreach ($row as $v) {
                if (!$v) return false;
            }
        }
        return true;
    }
}
