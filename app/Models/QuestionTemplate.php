<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionTemplate extends Model
{
    protected $fillable = ['topic_id', 'theme_id', 'difficulty', 'is_olympiad', 'answer_type', 'question_type', 'template_text', 'hint_text', 'correct_formula', 'num_config', 'distractors', 'conditions'];

    protected $casts = ['num_config' => 'array', 'distractors' => 'array', 'conditions' => 'array', 'is_olympiad' => 'boolean'];

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
        return \App\Services\CrosswordService::build(
            (int)  ($cfg['min_val']      ?? 1),
            (int)  ($cfg['max_val']      ?? 10),
            (array)($cfg['operators']    ?? ['+']),
            $rows,
            $cols,
            (int)  ($cfg['revealed_count'] ?? 0)
        );
    }

    public function generateCode(): array
    {
        $cfg = $this->num_config ?? [];
        return \App\Services\CodeService::build(
            (int)  ($cfg['symbol_count']  ?? 3),
            (int)  ($cfg['min_val']       ?? 1),
            (int)  ($cfg['max_val']       ?? 9),
            (array)($cfg['operators']     ?? ['+']),
            (int)  ($cfg['vars_per_eq']   ?? 2),
            (bool) ($cfg['unique_values'] ?? false)
        );
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
            'hint_text'      => null,
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

        $conditions = $this->conditions ?? [];
        $numVars = [];
        $correct = 0;
        for ($attempt = 0; $attempt < 200; $attempt++) {
            $numVars = [];
            foreach ($numConf as $key => $conf) {
                if (! is_array($conf)) continue;
                $step = max(1, (int) ($conf['step'] ?? 1));
                $steps = (int) (($conf['max'] - $conf['min']) / $step);
                $numVars[$key] = $conf['min'] + rand(0, $steps) * $step;
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
        $wrongCount = $noneCorrect ? 3 : 4;
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

        $displayExpr = $this->buildArithmeticExpr($varNames, $values, $opsChosen, $parenStart);
        $text = $displayExpr . ' = ?';

        $hint = $this->hint_text ?: null;

        return array_merge(
            ['question_text' => $text, 'hint_text' => $hint],
            $this->buildMcOptions($correct)
        );
    }

    /**
     * Randomly rolls values/operators/parens up to $maxAttempts times, keeping the
     * first roll that produces a positive whole-number result (and, if $noNegSteps,
     * never dips negative along the way). Returns null if nothing qualified.
     */
    private function attemptArithmetic(array $varConfigs, array $varNames, int $n, array $operators, bool $useParens, bool $noNegSteps, int $maxAttempts): ?array
    {
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $values = [];
            foreach ($varConfigs as $name => $conf) {
                $step  = max(1, (int) ($conf['step'] ?? 1));
                $min   = (int) ($conf['min'] ?? 1);
                $max   = (int) ($conf['max'] ?? 9);
                $steps = max(0, intdiv(max($min, $max) - $min, $step));
                $values[$name] = $min + rand(0, $steps) * $step;
            }

            $opsChosen = [];
            for ($i = 0; $i < $n - 1; $i++) {
                $opsChosen[] = $operators[array_rand($operators)];
            }

            // Parentheses are always applied once the admin enables them — only
            // *where* they land (which adjacent pair) is randomized.
            $parenStart = ($useParens && $n >= 3) ? rand(0, $n - 2) : null;

            $evaluated = $this->evalArithmeticSteps($varNames, $values, $opsChosen, $parenStart);
            if ($evaluated === null) continue;
            if ($evaluated['result'] <= 0 || floor($evaluated['result']) != $evaluated['result']) continue;
            if ($noNegSteps && ! $evaluated['no_negative']) continue;

            return ['values' => $values, 'ops' => $opsChosen, 'paren' => $parenStart, 'correct' => (int) $evaluated['result']];
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
     */
    private function buildArithmeticExpr(array $varNames, array $values, array $opsChosen, ?int $parenStart): string
    {
        $n = count($varNames);
        $parts = [];
        for ($i = 0; $i < $n; $i++) {
            if ($parenStart !== null && $i === $parenStart) $parts[] = '(';
            $parts[] = (string) $values[$varNames[$i]];
            if ($parenStart !== null && $i === $parenStart + 1) $parts[] = ')';
            if ($i < $n - 1) $parts[] = ' ' . $opsChosen[$i] . ' ';
        }
        return implode('', $parts);
    }
}
