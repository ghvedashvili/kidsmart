<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = ['topic_id', 'theme_id', 'difficulty', 'is_olympiad', 'is_active', 'answer_type', 'question_type', 'template_text', 'hint_text', 'correct_formula', 'num_config', 'distractors', 'conditions'];

    protected $casts = ['num_config' => 'array', 'distractors' => 'array', 'conditions' => 'array', 'is_olympiad' => 'boolean', 'is_active' => 'boolean'];

    private function evalExpr(string $expr, array $vars): int
    {
        $e = $expr;
        foreach ($vars as $k => $v) {
            $e = str_replace($k, (string) $v, $e);
        }
        $e = preg_replace('/[^0-9+\-*\/()\s%]/', '', $e);
        $result = @eval("return (int)({$e});");
        return ($result === false || !is_numeric($result)) ? 0 : (int) $result;
    }

    private function conditionsMet(array $conditions, array $vars): bool
    {
        foreach ($conditions as $c) {
            $l = $this->evalExpr((string) ($c['left']  ?? '0'), $vars);
            $r = $this->evalExpr((string) ($c['right'] ?? '0'), $vars);
            $ok = match($c['op'] ?? '') {
                '>'   => $l > $r,
                '<'   => $l < $r,
                '>='  => $l >= $r,
                '<='  => $l <= $r,
                '=='  => $l === $r,
                '!='  => $l !== $r,
                '%0'  => $r !== 0 && $l % $r === 0,
                '!%0' => $r !== 0 && $l % $r !== 0,
                default => true,
            };
            if (!$ok) return false;
        }
        return true;
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Theme::class);
    }

    public function isPyramid(): bool    { return $this->question_type === 'pyramid'; }
    public function isCode(): bool       { return $this->question_type === 'code'; }
    public function isCrossword(): bool  { return $this->question_type === 'crossword'; }

    public function generate(?Theme $theme = null): array
    {
        if ($this->isPyramid())   return $this->generatePyramid();
        if ($this->isCode())      return $this->generateCode();
        if ($this->isCrossword()) return $this->generateCrossword();
        return $this->answer_type === 'text'
            ? $this->generateText($theme)
            : $this->generateNumeric($theme);
    }

    public function generateCrossword(): array
    {
        $cfg  = $this->num_config ?? [];
        $rows = in_array((int)($cfg['rows'] ?? 2), [2, 3]) ? (int)$cfg['rows'] : 2;
        $cols = in_array((int)($cfg['cols'] ?? 2), [2, 3]) ? (int)$cfg['cols'] : 2;
        $result = \App\Services\CrosswordService::build(
            (int)  ($cfg['min_val']      ?? 1),
            (int)  ($cfg['max_val']      ?? 10),
            (array)($cfg['operators']    ?? ['+']),
            $rows,
            $cols,
            (int)  ($cfg['revealed_count'] ?? 0)
        );
        return array_merge($result, ['hint_text' => $this->hint_text ?: null]);
    }

    public function generateCode(): array
    {
        $cfg = $this->num_config ?? [];
        $result = \App\Services\CodeService::build(
            (int)  ($cfg['symbol_count']  ?? 3),
            (int)  ($cfg['min_val']       ?? 1),
            (int)  ($cfg['max_val']       ?? 9),
            (array)($cfg['operators']     ?? ['+']),
            (int)  ($cfg['vars_per_eq']   ?? 2),
            (bool) ($cfg['unique_values'] ?? false)
        );
        return array_merge($result, ['hint_text' => $this->hint_text ?: null]);
    }

    public function generatePyramid(): array
    {
        $cfg         = $this->num_config ?? [];
        $height      = max(3, (int) ($cfg['height']       ?? 3));
        $maxBase     = max(2, (int) ($cfg['max_base']     ?? 9));
        $hiddenCount = max(1, (int) ($cfg['hidden_count'] ?? 2));

        $result = \App\Services\PyramidService::build($height, $maxBase, $hiddenCount);

        return [
            'type'           => 'pyramid',
            'rows'           => $result['rows'],
            'solutions'      => $result['solutions'],
            'question_text'  => json_encode($result['rows']),
            'correct_answer' => json_encode($result['solutions']),
            'hint_text'      => $this->hint_text ?: null,
            'options'        => null,
        ];
    }

    private function generateText(Theme $theme): array
    {
        $vars = $theme->resolveVariables();
        $vars['__ALL__']  = 'ყველა პასუხი სწორეა';
        $vars['__NONE__'] = 'არცერთი სწორი არ არის';

        $correctVarName = $this->correct_formula;
        $correct = $vars[$correctVarName] ?? '?';

        $dist = $this->distractors ?? [];
        $optionVarNames = $dist['vars'] ?? [];

        $options = [];
        foreach ($optionVarNames as $varName) {
            $val = $vars[$varName] ?? '?';
            if (!in_array($val, $options, true)) {
                $options[] = $val;
            }
        }
        if (!in_array($correct, $options, true)) {
            array_unshift($options, $correct);
        }
        shuffle($options);

        $text = preg_replace('/\{\{(\w+)\}(?!\})/', '{{$1}}', $this->template_text);
        foreach ($vars as $k => $v) {
            $text = str_replace("{{{$k}}}", (string) $v, $text);
        }
        $text = preg_replace('/\{\{\w+\}\}/', '?', $text);

        $hint = null;
        if ($this->hint_text) {
            $h = preg_replace('/\{\{(\w+)\}\}(?!\})/', '{{$1}}', $this->hint_text);
            foreach ($vars as $k => $v) {
                $h = str_replace("{{{$k}}}", (string) $v, $h);
            }
            $hint = trim(preg_replace('/\{\{\w+\}\}/', '?', $h)) ?: null;
        }

        return [
            'question_text'  => $text,
            'hint_text'      => $hint,
            'options'        => $options,
            'correct_answer' => $correct,
        ];
    }

    private function generateNumeric(Theme $theme): array
    {
        $numConf = $this->num_config ?? [];

        if (! empty($numConf['_auto']['enabled'])) {
            return $this->generateAutoArithmetic($theme);
        }

        $uniqueVars = (bool) ($numConf['_unique'] ?? false);
        $baseFormula = preg_replace('/\{\{(\w+)\}\}/', '$1', $this->correct_formula);

        // Split variables into randomly-rolled ones and "derived" ones — a derived
        // variable (num_config[key] = ['derived' => 'N1+3']) is never rolled itself;
        // it's computed from whatever vars were already resolved above it (row order),
        // using the same evalExpr() conditions use. This keeps a relation like
        // "N2 is always N1+3" exact instead of relying on random values happening to
        // match it (which an '==' condition would only satisfy by chance).
        $randomConf = [];
        $derivedConf = [];
        foreach ($numConf as $key => $conf) {
            if (! is_array($conf) || str_starts_with((string) $key, '_')) continue;
            if (array_key_exists('derived', $conf)) {
                $derivedConf[$key] = $conf;
            } else {
                $randomConf[$key] = $conf;
            }
        }

        $conditions = $this->conditions ?? [];
        $numVars = [];
        $correct = 0;
        for ($attempt = 0; $attempt < 200; $attempt++) {
            $numVars = [];
            foreach ($randomConf as $key => $conf) {
                $step = max(1, (int) ($conf['step'] ?? 1));
                $steps = (int) (($conf['max'] - $conf['min']) / $step);
                $numVars[$key] = $conf['min'] + rand(0, $steps) * $step;
            }
            foreach ($derivedConf as $key => $conf) {
                $numVars[$key] = $this->evalExpr((string) ($conf['derived'] ?? '0'), $numVars);
            }
            if ($uniqueVars && count(array_unique($numVars)) !== count($numVars)) continue;
            $f = $baseFormula;
            foreach ($numVars as $k => $v) {
                $f = str_replace($k, (string) $v, $f);
            }
            $result = @eval("return (int)({$f});");
            if ($result === false || $result <= 0) continue;
            if (!$this->conditionsMet($conditions, $numVars)) continue;
            $correct = $result;
            break;
        }

        $vars = array_merge($numVars, $theme->resolveVariables());

        $text = preg_replace('/\{\{(\w+)\}(?!\})/', '{{$1}}', $this->template_text);
        foreach ($vars as $k => $v) {
            $text = str_replace("{{{$k}}}", (string) $v, $text);
        }
        $text = preg_replace('/\{\{\w+\}\}/', '?', $text);

        $hintRaw = preg_replace('/\{\{(\w+)\}\}(?!\})/', '{{$1}}', $this->hint_text ?? '');
        foreach ($vars as $k => $v) {
            $hintRaw = str_replace("{{{$k}}}", (string) $v, $hintRaw);
        }
        $hint = trim(preg_replace('/\{\{\w+\}\}/', '?', $hintRaw)) ?: null;

        return array_merge(
            ['question_text' => $text, 'hint_text' => $hint],
            $this->buildMcOptions($correct)
        );
    }

    /**
     * Builds the multiple-choice options array (correct value + distractors,
     * or distractors + "none correct") from $this->distractors config.
     * Shared by the manual-formula and auto-built arithmetic generators.
     */
    private function buildMcOptions(int $correct): array
    {
        $dist        = $this->distractors;
        $dMin        = max(1, (int) ($dist['min'] ?? 1));
        $dMax        = max($dMin, (int) ($dist['max'] ?? 10));
        $noneCorrect = (bool) ($dist['none_correct'] ?? false);

        $wrong = [];
        $attempts = 0;
        $wrongCount = 3;
        while (count($wrong) < $wrongCount && $attempts < 100) {
            $attempts++;
            $delta     = rand($dMin, $dMax);
            $sign      = rand(0, 1) ? 1 : -1;
            $candidate = $correct + ($sign * $delta);
            if ($candidate > 0 && !in_array($candidate, $wrong) && $candidate !== $correct) {
                $wrong[] = $candidate;
            }
        }

        if ($noneCorrect) {
            $options = array_map('strval', $wrong);
            $options[] = 'არცერთი სწორი არ არის';
            shuffle($options);
            $correctAnswer = 'არცერთი სწორი არ არის';
        } else {
            $options = array_merge([$correct], $wrong);
            shuffle($options);
            $correctAnswer = (string) $correct;
        }

        return ['options' => array_map('strval', $options), 'correct_answer' => $correctAnswer];
    }

    /**
     * Auto-built arithmetic: $this->num_config holds per-variable ranges (N1, N2, ...)
     * the same way the manual-formula mode does, plus a reserved '_auto' key with
     * ['enabled'=>true, 'operators'=>['+','-',...], 'parens'=>bool]. Operators (and,
     * if enabled, a pair of adjacent terms to wrap in parentheses) are re-rolled on
     * every generation instead of being fixed in a stored template_text/correct_formula,
     * so one template produces visibly different-looking questions each time.
     */
    private function generateAutoArithmetic(Theme $theme): array
    {
        $numConf = $this->num_config ?? [];
        $auto      = $numConf['_auto'] ?? [];
        $operators = array_values(array_filter((array) ($auto['operators'] ?? ['+']), fn ($o) => in_array($o, ['+', '-', '×', '÷'], true)));
        if (! $operators) $operators = ['+'];
        $useParens   = (bool) ($auto['parens'] ?? false);
        $noNegSteps  = array_key_exists('no_negative_steps', $auto) ? (bool) $auto['no_negative_steps'] : true;
        $mixBlank    = array_key_exists('mix_blank', $auto) ? (bool) $auto['mix_blank'] : true;

        // mix_blank's "sometimes hide an operand instead of the result" relies on
        // {{EXPR}} to correctly render either "...= ?" or "...= <result>" depending
        // on which was hidden. If the admin wrote their own text without that token
        // (e.g. raw {{N1}}/{{N2}} placeholders with a hand-written "= ?" at the end),
        // force the classic result-hidden style so a hidden operand never collides
        // with a hardcoded, now-misleading "= ?".
        $customText   = trim((string) $this->template_text);
        $hasExprToken = str_contains($customText, '{{EXPR}}');
        $mixBlank     = $mixBlank && ($customText === '' || $hasExprToken);

        $varConfigs = [];
        foreach ($numConf as $key => $conf) {
            if (! is_array($conf) || str_starts_with((string) $key, '_')) continue;
            $varConfigs[$key] = $conf;
        }
        if (count($varConfigs) < 2) {
            $varConfigs = ['N1' => ['min' => 1, 'max' => 9, 'step' => 1], 'N2' => ['min' => 1, 'max' => 9, 'step' => 1]];
        }
        $varNames = array_keys($varConfigs);
        $n = count($varNames);

        // Try with every constraint as configured first; if the ranges make that
        // combination impossible (e.g. only '-' with no room to stay non-negative),
        // relax the no-negative-intermediate check before giving up on the chosen
        // operators entirely — only fall back to a plain '+' chain as a last resort.
        $found = $this->attemptArithmetic($varConfigs, $varNames, $n, $operators, $useParens, $noNegSteps, 400);
        if ($found === null && $noNegSteps) {
            $found = $this->attemptArithmetic($varConfigs, $varNames, $n, $operators, $useParens, false, 400);
        }

        if ($found !== null) {
            ['values' => $values, 'ops' => $opsChosen, 'paren' => $parenStart, 'correct' => $correct] = $found;
        } else {
            // Exhausted attempts with the chosen operators under any setting —
            // fall back to a plain left-to-right '+' chain, which always resolves.
            $values = [];
            foreach ($varConfigs as $name => $conf) {
                $step  = max(1, (int) ($conf['step'] ?? 1));
                $min   = (int) ($conf['min'] ?? 1);
                $max   = (int) ($conf['max'] ?? 9);
                $steps = max(0, intdiv(max($min, $max) - $min, $step));
                $values[$name] = $min + rand(0, $steps) * $step;
            }
            $opsChosen = array_fill(0, $n - 1, '+');
            $parenStart = null;
            $correct = (int) array_sum($values);
        }

        // When $mixBlank is on, the hidden slot is always one of the operands
        // (never the result) — "4 + ? = 9" instead of "4 + 5 = ?". When it's off,
        // the result is always what's hidden (the classic style). Operand slots
        // inside the chosen paren pair are excluded from the random pick, since
        // solving for a blank a layer deep inside parens would need an extra
        // algebra step a child hasn't been shown.
        $blankPos = null;
        if ($mixBlank) {
            $blankCandidates = range(0, $n - 1);
            if ($parenStart !== null) {
                $blankCandidates = array_values(array_diff($blankCandidates, [$parenStart, $parenStart + 1]));
            }
            if ($blankCandidates) {
                $blankPos = $blankCandidates[array_rand($blankCandidates)];
            }
        }

        if ($blankPos === null) {
            $equation = $this->buildArithmeticExpr($varNames, $values, $opsChosen, $parenStart) . ' = ?';
            $answer = $correct;
        } else {
            $equation = $this->buildArithmeticExpr($varNames, $values, $opsChosen, $parenStart, $blankPos) . ' = ' . $correct;
            $answer = (int) $values[$varNames[$blankPos]];
        }

        // Admin can wrap the generated equation in their own instruction text (e.g.
        // "ამოხსენი: {{EXPR}}"). {{EXPR}} is replaced with the full equation; any
        // {{N1}}, {{N2}}... placeholder is also replaced with that variable's rolled
        // value (shown as "?" if it's the one hidden this round) — same {{var}}
        // syntax every other template type already uses. The equation is appended
        // at the end ONLY if the text references neither {{EXPR}} nor any variable
        // directly — otherwise the admin is already placing the numbers themselves
        // (e.g. "{{N1}} + {{N2}} = ?"), and appending it again would duplicate it.
        if ($customText === '') {
            $text = $equation;
        } else {
            $hasVarToken = false;
            foreach ($varNames as $vn) {
                if (str_contains($customText, "{{{$vn}}}")) { $hasVarToken = true; break; }
            }
            $displayValues = $values;
            if ($blankPos !== null) {
                $displayValues[$varNames[$blankPos]] = '?';
            }
            $text = $hasExprToken ? str_replace('{{EXPR}}', $equation, $customText) : $customText;
            foreach ($displayValues as $k => $v) {
                $text = str_replace("{{{$k}}}", (string) $v, $text);
            }
            $text = preg_replace('/\{\{\w+\}\}/', '?', $text);
            if (! $hasExprToken && ! $hasVarToken) {
                $text .= ' ' . $equation;
            }
        }

        $hint = $this->hint_text ?: null;

        return array_merge(
            ['question_text' => $text, 'hint_text' => $hint],
            $this->buildMcOptions($answer)
        );
    }

    /**
     * Picks an operator combo (and paren position), then retries only the random
     * VALUES against that fixed combo before giving up on it and picking a new one.
     * Operators are deliberately NOT re-rolled on every value attempt: if they were,
     * whichever operator is statistically easier to satisfy (e.g. '+', which is
     * always a valid positive result) would win the "first success" race far more
     * often than a harder one (e.g. '-', which needs the running total to stay
     * positive) — silently skewing the output even when both are equally selected
     * by the admin. Holding operators fixed for a batch of value attempts gives each
     * chosen combo a fair shot at succeeding on its own, so the actual distribution
     * of generated operators matches what was selected instead of what's "easiest".
     */
    private function attemptArithmetic(array $varConfigs, array $varNames, int $n, array $operators, bool $useParens, bool $noNegSteps, int $maxAttempts): ?array
    {
        $outerAttempts = min(10, $maxAttempts);
        $innerAttempts = max(1, intdiv($maxAttempts, $outerAttempts));

        for ($opAttempt = 0; $opAttempt < $outerAttempts; $opAttempt++) {
            $opsChosen = [];
            for ($i = 0; $i < $n - 1; $i++) {
                $opsChosen[] = $operators[array_rand($operators)];
            }

            // Parentheses are always applied once the admin enables them — only
            // *where* they land (which adjacent pair) is randomized.
            $parenStart = ($useParens && $n >= 3) ? rand(0, $n - 2) : null;

            for ($valAttempt = 0; $valAttempt < $innerAttempts; $valAttempt++) {
                $values = [];
                foreach ($varConfigs as $name => $conf) {
                    $step  = max(1, (int) ($conf['step'] ?? 1));
                    $min   = (int) ($conf['min'] ?? 1);
                    $max   = (int) ($conf['max'] ?? 9);
                    $steps = max(0, intdiv(max($min, $max) - $min, $step));
                    $values[$name] = $min + rand(0, $steps) * $step;
                }

                $evaluated = $this->evalArithmeticSteps($varNames, $values, $opsChosen, $parenStart);
                if ($evaluated === null) continue;
                if ($evaluated['result'] <= 0 || floor($evaluated['result']) != $evaluated['result']) continue;
                if ($noNegSteps && ! $evaluated['no_negative']) continue;

                return ['values' => $values, 'ops' => $opsChosen, 'paren' => $parenStart, 'correct' => (int) $evaluated['result']];
            }
        }

        return null;
    }

    /**
     * Evaluates the N1 op N2 op N3 ... chain (with an optional parenthesized adjacent
     * pair) the way a child would solve it on paper: parens first, then × and ÷ left
     * to right collapsing each factor chain into one term, then the remaining +/- chain
     * left to right. Returns the final result plus whether every step of that final
     * +/- walk (including the very first term) stayed >= 0 — or null if a division
     * along the way didn't come out to a whole number (so it's not usable either way).
     */
    private function evalArithmeticSteps(array $varNames, array $values, array $opsChosen, ?int $parenStart): ?array
    {
        $terms = [];
        foreach ($varNames as $i => $name) {
            $terms[] = ['val' => (float) $values[$name], 'op' => $i === 0 ? null : $opsChosen[$i - 1]];
        }

        // Tracks whether ANY sub-step — inside the parens, inside a ×÷ chain, or in
        // the outer +/- walk — ever produced a negative value a child would have had
        // to compute along the way, even if it's later cancelled out by what follows.
        $noNegative = true;

        if ($parenStart !== null) {
            $a = $terms[$parenStart];
            $b = $terms[$parenStart + 1];
            $combined = $this->applyArithmeticOp($a['val'], $b['op'], $b['val']);
            if ($combined === null) return null;
            if ($combined < 0) $noNegative = false;
            array_splice($terms, $parenStart, 2, [['val' => $combined, 'op' => $a['op']]]);
        }

        $reduced = [];
        foreach ($terms as $t) {
            if ($t['op'] === '×' || $t['op'] === '÷') {
                $prev = array_pop($reduced);
                $combined = $this->applyArithmeticOp($prev['val'], $t['op'], $t['val']);
                if ($combined === null) return null;
                if ($combined < 0) $noNegative = false;
                $reduced[] = ['val' => $combined, 'op' => $prev['op']];
            } else {
                $reduced[] = $t;
            }
        }

        $running = $reduced[0]['val'];
        if ($running < 0) $noNegative = false;
        for ($i = 1; $i < count($reduced); $i++) {
            $running = $this->applyArithmeticOp($running, $reduced[$i]['op'], $reduced[$i]['val']);
            if ($running === null) return null;
            if ($running < 0) $noNegative = false;
        }

        return ['result' => $running, 'no_negative' => $noNegative];
    }

    private function applyArithmeticOp(float $a, string $op, float $b): ?float
    {
        return match ($op) {
            '+' => $a + $b,
            '-' => $a - $b,
            '×' => $a * $b,
            '÷' => ($b != 0 && fmod($a, $b) == 0) ? $a / $b : null,
            default => null,
        };
    }

    /**
     * Builds the display string (× ÷ shown as-is, for what the child actually sees)
     * from the same ordered variable names/values/operators used for evaluation.
     * $blankPos, if given, replaces that one operand's value with "?" instead of
     * showing it (used for the "4 + ? = 9" style, as opposed to the classic
     * "4 + 5 = ?" where the result itself is hidden).
     */
    private function buildArithmeticExpr(array $varNames, array $values, array $opsChosen, ?int $parenStart, ?int $blankPos = null): string
    {
        $n = count($varNames);
        $parts = [];
        for ($i = 0; $i < $n; $i++) {
            if ($parenStart !== null && $i === $parenStart) $parts[] = '(';
            $parts[] = $i === $blankPos ? '?' : (string) $values[$varNames[$i]];
            if ($parenStart !== null && $i === $parenStart + 1) $parts[] = ')';
            if ($i < $n - 1) $parts[] = ' ' . $opsChosen[$i] . ' ';
        }
        return implode('', $parts);
    }
}
