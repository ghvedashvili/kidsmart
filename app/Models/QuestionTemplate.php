<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = ['topic_id', 'theme_id', 'difficulty', 'is_olympiad', 'is_active', 'sequence', 'answer_type', 'question_type', 'title', 'template_text', 'hint_text', 'correct_formula', 'num_config', 'distractors', 'conditions'];

    protected $casts = ['num_config' => 'array', 'distractors' => 'array', 'conditions' => 'array', 'is_olympiad' => 'boolean', 'is_active' => 'boolean', 'sequence' => 'integer'];

    private function evalExpr(string $expr, array $vars): int
    {
        $e = $expr;
        foreach ($vars as $k => $v) {
            $e = str_replace($k, (string) $v, $e);
        }
        $e = preg_replace('/[^0-9+\-*\/()\s%]/', '', $e);
        // @ only suppresses warnings — malformed input (e.g. an unresolved variable
        // name leaving stray tokens, or two numbers left adjacent with no operator
        // between them) makes eval() throw a real ParseError/Error, which @ does NOT
        // catch. Without this try/catch, one bad formula crashes the whole request.
        try {
            $result = @eval("return (int)({$e});");
        } catch (\Throwable $ex) {
            $result = false;
        }
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
    public function isScale(): bool      { return $this->question_type === 'scale'; }

    public function generate(?Theme $theme = null): array
    {
        if ($this->isPyramid())   return $this->generatePyramid();
        if ($this->isCode())      return $this->generateCode();
        if ($this->isCrossword()) return $this->generateCrossword();
        if ($this->isScale())     return $this->generateScale();
        return $this->answer_type === 'text'
            ? $this->generateText($theme)
            : $this->generateNumeric($theme);
    }

    /**
     * Balance-scale puzzle: two pans, each holding a fixed (admin-authored)
     * composition of "weight" items (value shown directly) and/or "emoji" items
     * (weight hidden, rolled fresh each generation — see ScaleService::build()).
     * One side ends up lighter; the child must say what it takes to balance it —
     * either a plain number (weight-kind answer, or emoji-kind with answer_type
     * numeric_input, which also names the emoji in the prompt) or, in MC mode,
     * pick the correct "{count} {emoji}" figure from 4 options (emoji identity
     * not revealed in advance).
     */
    public function generateScale(): array
    {
        $cfg     = $this->num_config ?? [];
        $symbols = is_array($cfg['symbols'] ?? null) ? $cfg['symbols'] : [];
        $left    = is_array($cfg['left']    ?? null) ? $cfg['left']    : [];
        $right   = is_array($cfg['right']   ?? null) ? $cfg['right']   : [];
        $answerKey    = (string) ($cfg['answer_key'] ?? '');
        $referenceKey = $cfg['reference_key'] ?: null;
        $countMin = max(1, (int) ($cfg['count_min'] ?? 1));
        $countMax = max($countMin, (int) ($cfg['count_max'] ?? 6));
        $isMc     = $this->answer_type !== 'numeric_input';

        // Letters (A, B, C...) are the names the admin builds both {{A}}-style
        // template_text tokens and "conditions" rows against — assigned
        // positionally from $symbols in save order, same as the admin form's
        // display labels (scaleSymbolLabel()).
        $letterMap = [];
        $li = 0;
        foreach (array_keys($symbols) as $key) {
            $letterMap[chr(65 + $li)] = $key;
            $li++;
        }

        // Conditions constrain how the symbols' rolled weights relate to each
        // other, reusing the same conditionsMet()/evalExpr() engine the plain
        // numeric templates use — just against these letter-named weights
        // instead of N1/N2. Checked on every ScaleService::build() attempt, so
        // an impossible condition just exhausts the retry budget like any other
        // unsatisfiable constraint (caught below as QuestionGenerationException).
        $conditions = $this->conditions ?? [];
        $conditionsCheck = empty($conditions) ? null : function (array $weights) use ($conditions, $letterMap) {
            $letterWeights = [];
            foreach ($letterMap as $letter => $key) {
                $letterWeights[$letter] = $weights[$key] ?? 0;
            }
            return $this->conditionsMet($conditions, $letterWeights);
        };

        try {
            $result = \App\Services\ScaleService::build($symbols, $left, $right, $answerKey, $referenceKey, $countMin, $countMax, $conditionsCheck);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('QuestionTemplate: scale config never resolved after retries', [
                'template_id' => $this->id,
                'error'       => $e->getMessage(),
            ]);
            throw new \App\Exceptions\QuestionGenerationException("Template #{$this->id}: scale config never resolved");
        }

        $answerKind  = $result['blank_kind'];
        $answerValue = (int) $result['answer_value'];

        if ($answerKind === 'weight') {
            $ansData = $this->buildMcOptions($answerValue);
        } elseif ($isMc) {
            $ansData = \App\Services\ScaleService::buildEmojiOptions($symbols, $answerKey, $answerValue, $countMin, $countMax);
        } else {
            $ansData = ['options' => null, 'correct_answer' => (string) $answerValue];
        }

        $isMcResolved = ! empty($ansData['options']);

        $defaultPrompt = $isMcResolved
            ? 'რომელი ვარიანტი დააბალანსებს სასწორს? ⚖️'
            : ($answerKind === 'weight'
                ? 'რა წონა აკლია, რომ სასწორი დაბალანსდეს? ⚖️'
                : 'რამდენი ' . ($result['blank_symbol'] ?? '') . ' დასამატებელია, რომ სასწორი დაბალანსდეს? ⚖️');
        $prompt = trim((string) $this->template_text) !== '' ? $this->template_text : $defaultPrompt;

        // {{A}}, {{B}}... in a custom prompt resolve to that symbol's rolled
        // value — the number itself for a weight-kind symbol (its own weight if
        // rolled, or the just-solved answer value for the answer symbol, which
        // is deliberately never pre-rolled), or the emoji character for an
        // emoji-kind symbol (never its hidden weight, which would spoil the puzzle).
        foreach ($letterMap as $letter => $key) {
            $conf = $symbols[$key] ?? [];
            if (($conf['kind'] ?? 'emoji') === 'weight') {
                $val = $result['weights'][$key] ?? ($key === $answerKey ? $answerValue : '?');
            } else {
                $val = $conf['symbol'] ?? '❓';
            }
            $prompt = str_replace("{{{$letter}}}", (string) $val, $prompt);
        }

        $questionData = [
            'type'           => 'scale',
            'prompt'         => $prompt,
            'left'           => $result['left'],
            'right'          => $result['right'],
            'blank_side'     => $result['blank_side'],
            'blank_kind'     => $answerKind,
            'blank_symbol'   => $isMcResolved ? null : $result['blank_symbol'],
            'condition_text' => $result['condition_text'],
        ];

        return array_merge(
            ['question_text' => json_encode($questionData), 'hint_text' => $this->hint_text ?: null],
            $ansData
        );
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

    /**
     * Text-answer templates can now also declare num_config variables (random,
     * derived, operator) the same way numeric ones do — so a question can compare
     * computed quantities and pick a THEME variable (a text answer) based on that
     * comparison. {{N1}}, {{X}}, etc. render in the text/hint exactly as the numeric
     * generator does (operator vars show their Georgian label); "correct_formula"
     * can then be either a plain variable name (existing behavior, unchanged) or a
     * conditional formula — see resolveTextFormula().
     */
    private function generateText(Theme $theme): array
    {
        $themeVars = $theme->resolveVariables();
        $themeVars['__ALL__']   = 'ყველა პასუხი სწორეა';
        $themeVars['__NONE__']  = 'არცერთი სწორი არ არის';
        $themeVars['__EQUAL__'] = 'თანაბარია';

        ['numVars' => $numVars, 'displayVars' => $displayNumVars] = $this->resolveNumConfigVars();
        $vars = array_merge($displayNumVars, $themeVars);

        $correct = $this->resolveTextFormula(trim((string) $this->correct_formula), $numVars, $themeVars);

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

    /**
     * Rolls num_config's variables (random, derived, operator) up to 200 times,
     * retrying until `conditions` and (if set) uniqueness are satisfied — the same
     * variable-resolution rules generateNumeric() uses, factored out so text-answer
     * templates can use num_config too without duplicating a numeric correct_formula
     * check they don't need (there's no formula to validate here, just variables).
     */
    private function resolveNumConfigVars(): array
    {
        $numConf = $this->num_config ?? [];
        $uniqueVars = (bool) ($numConf['_unique'] ?? false);

        $randomConf      = [];
        $derivedConf     = [];
        $operatorConf    = [];
        $conditionalConf = [];
        foreach ($numConf as $key => $conf) {
            if (! is_array($conf) || str_starts_with((string) $key, '_')) continue;
            if (! empty($conf['operator'])) {
                $operatorConf[$key] = $conf;
            } elseif (array_key_exists('derived', $conf)) {
                $derivedConf[$key] = $conf;
            } elseif (array_key_exists('conditional', $conf)) {
                $conditionalConf[$key] = $conf;
            } else {
                $randomConf[$key] = $conf;
            }
        }

        $conditions = $this->conditions ?? [];
        $numVars = [];

        // Operator variables are picked ONCE per outer attempt, then only the random
        // values are retried for a batch of inner attempts — see generateNumeric()'s
        // matching comment for why re-rolling the operator on every attempt would
        // skew its distribution toward whichever option is easier to satisfy.
        $outerAttempts = 10;
        $innerAttempts = 20;
        $solved = false;
        for ($opAttempt = 0; $opAttempt < $outerAttempts && ! $solved; $opAttempt++) {
            $opVars = [];
            foreach ($operatorConf as $key => $conf) {
                $options = array_values(array_filter((array) ($conf['options'] ?? []), fn ($o) => isset(self::OPERATOR_LABELS[$o])));
                if (! $options) $options = ['+'];
                $opVars[$key] = $options[array_rand($options)];
            }

            for ($attempt = 0; $attempt < $innerAttempts; $attempt++) {
                $numVars = $opVars;
                foreach ($randomConf as $key => $conf) {
                    $step  = max(1, (int) ($conf['step'] ?? 1));
                    $steps = (int) (($conf['max'] - $conf['min']) / $step);
                    $numVars[$key] = $conf['min'] + rand(0, $steps) * $step;
                }
                foreach ($derivedConf as $key => $conf) {
                    $numVars[$key] = $this->evalExpr((string) ($conf['derived'] ?? '0'), $numVars);
                }
                foreach ($conditionalConf as $key => $conf) {
                    $numVars[$key] = $this->resolveTextFormula((string) ($conf['conditional'] ?? ''), $numVars, []);
                }

                if ($uniqueVars) {
                    $uniqueCheck = array_diff_key($numVars, $operatorConf, $conditionalConf);
                    if (count(array_unique($uniqueCheck)) !== count($uniqueCheck)) continue;
                }
                if ($this->conditionsMet($conditions, $numVars)) { $solved = true; break; }
            }
        }

        $displayVars = $numVars;
        foreach ($operatorConf as $key => $conf) {
            if (isset($displayVars[$key])) {
                $displayVars[$key] = self::OPERATOR_LABELS[$displayVars[$key]] ?? $displayVars[$key];
            }
        }

        return ['numVars' => $numVars, 'displayVars' => $displayVars];
    }

    /**
     * Resolves a text-answer "correct_formula". Three forms:
     *   - a plain variable name (existing behavior) — looked up directly;
     *   - a conditional formula "COND?IFTRUE:IFFALSE", where COND compares two
     *     num_config expressions (>,<,>=,<=,==,!=) and IFTRUE/IFFALSE are each
     *     either a variable name or, recursively, another conditional formula —
     *     so chains like "A>B?X:C>D?Y:Z" work without extra syntax. A branch that
     *     isn't itself conditional falls through to the same plain-variable lookup
     *     as the base case;
     *   - literal text containing one or more "{{VAR}}" tokens (e.g. "{{DEL1}},{{DEL2}}")
     *     — each token is substituted with that variable's value, so two otherwise-
     *     unrelated variables can be combined into one answer string. Final fallback
     *     if nothing matches: the branch text itself (lets an admin write a literal
     *     word directly).
     */
    private function resolveTextFormula(string $formula, array $numVars, array $themeVars): string
    {
        $formula = $this->stripWrappingParens(trim($formula));
        $vars = array_merge($numVars, $themeVars);
        $qPos = $this->findTopLevelChar($formula, '?');
        if ($qPos === null) {
            if (isset($vars[$formula])) return (string) $vars[$formula];
            if (str_contains($formula, '{{')) {
                return preg_replace_callback(
                    '/\{\{(\w+)\}\}/',
                    fn ($m) => isset($vars[$m[1]]) ? (string) $vars[$m[1]] : $m[0],
                    $formula
                );
            }
            return $formula !== '' ? $formula : '?';
        }

        $condStr = trim(substr($formula, 0, $qPos));
        $restStr = substr($formula, $qPos + 1);
        $cPos = $this->findTopLevelChar($restStr, ':');
        if ($cPos === null) return '?';

        $ifTrueStr  = trim(substr($restStr, 0, $cPos));
        $ifFalseStr = trim(substr($restStr, $cPos + 1));

        $parts = $this->splitConditionExpr($condStr);
        if ($parts === null) return '?';
        [$left, $op, $right] = $parts;
        $condTrue = $this->conditionsMet([['left' => $left, 'op' => $op, 'right' => $right]], $numVars);

        return $this->resolveTextFormula($condTrue ? $ifTrueStr : $ifFalseStr, $numVars, $themeVars);
    }

    /**
     * Strips a single outer paren pair that wraps the ENTIRE string (e.g.
     * "(A>B?X:Y)" -> "A>B?X:Y"), repeating for multiple layers. Without this,
     * a parenthesized ternary branch like "COND?(A>B?X:Y):Z" would have its
     * chosen branch "(A>B?X:Y)" returned unevaluated — the outer parens shift
     * every character one paren-depth deeper, so findTopLevelChar never finds
     * the inner "?" at depth 0. Does NOT strip "(A)+(B)"-style pairs that don't
     * wrap the whole string, since the first "(" there closes before the end.
     */
    private function stripWrappingParens(string $s): string
    {
        while (strlen($s) >= 2 && $s[0] === '(' && substr($s, -1) === ')') {
            $depth = 0;
            $wrapsWhole = true;
            for ($i = 0, $len = strlen($s); $i < $len; $i++) {
                if ($s[$i] === '(') $depth++;
                elseif ($s[$i] === ')') {
                    $depth--;
                    if ($depth === 0 && $i !== $len - 1) { $wrapsWhole = false; break; }
                }
            }
            if (! $wrapsWhole) break;
            $s = trim(substr($s, 1, -1));
        }
        return $s;
    }

    /** First top-level (paren-depth 0) index of $char in $str, or null. */
    private function findTopLevelChar(string $str, string $char): ?int
    {
        $depth = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $c = $str[$i];
            if ($c === '(') $depth++;
            elseif ($c === ')') $depth--;
            elseif ($depth === 0 && $c === $char) return $i;
        }
        return null;
    }

    /** Splits "LEFT op RIGHT" on the first comparison operator found (checking
     *  2-char operators before 1-char ones so ">=" isn't mis-split as ">"). */
    private function splitConditionExpr(string $cond): ?array
    {
        foreach (['>=', '<=', '==', '!='] as $op) {
            $pos = strpos($cond, $op);
            if ($pos !== false) return [trim(substr($cond, 0, $pos)), $op, trim(substr($cond, $pos + 2))];
        }
        foreach (['>', '<'] as $op) {
            $pos = strpos($cond, $op);
            if ($pos !== false) return [trim(substr($cond, 0, $pos)), $op, trim(substr($cond, $pos + 1))];
        }
        return null;
    }

    /**
     * Georgian labels for the "operator" variable type's underlying math symbol —
     * {{X}} in template_text/hint_text shows the label ("ით მეტი"); the symbol
     * itself ("+") is what gets substituted into correct_formula.
     */
    private const OPERATOR_LABELS = [
        '+' => 'ით მეტი',
        '-' => 'ით ნაკლები',
        '*' => 'ჯერ მეტი',
        '/' => 'ჯერ ნაკლები',
    ];

    private function generateNumeric(Theme $theme): array
    {
        $numConf = $this->num_config ?? [];

        if (! empty($numConf['_auto']['enabled'])) {
            return $this->generateAutoArithmetic($theme);
        }

        $uniqueVars = (bool) ($numConf['_unique'] ?? false);
        $baseFormula = preg_replace('/\{\{(\w+)\}\}/', '$1', $this->correct_formula);

        // Variables split four ways: randomly-rolled, "derived" (computed from
        // other vars already resolved above it, same evalExpr() conditions use —
        // keeps a relation like "N2 is always N1+3" exact instead of relying on
        // random values happening to match it), "operator" (its value is one of
        // +-*/ , randomly chosen each attempt from the admin-checked subset — lets
        // a formula like "N1 X N2" literally change which operation runs), and
        // "conditional" (a text label picked via a comparison — e.g.
        // "TOTAL1>TOTAL2?ჯერ მეტი:ჯერ ნაკლები" — resolved with the same engine
        // the text-answer correct_formula uses, so a derived comparison's result
        // can be shown as a word instead of just driving which MC option is correct).
        $randomConf      = [];
        $derivedConf     = [];
        $operatorConf    = [];
        $conditionalConf = [];
        foreach ($numConf as $key => $conf) {
            if (! is_array($conf) || str_starts_with((string) $key, '_')) continue;
            if (! empty($conf['operator'])) {
                $operatorConf[$key] = $conf;
            } elseif (array_key_exists('derived', $conf)) {
                $derivedConf[$key] = $conf;
            } elseif (array_key_exists('conditional', $conf)) {
                $conditionalConf[$key] = $conf;
            } else {
                $randomConf[$key] = $conf;
            }
        }

        $conditions = $this->conditions ?? [];
        $numVars = [];
        $correct = 0;

        // Operator variables are picked ONCE per outer attempt, then only the random
        // values are retried for a batch of inner attempts before giving up on that
        // operator combo and picking new ones. Re-rolling the operator on every single
        // attempt (as before) let whichever option is statistically easier to satisfy
        // (e.g. '+', almost always a valid positive whole result) win the "first
        // success" race far more often than a harder one (e.g. '-', which needs N1 >
        // N2, or '/', which needs clean division) — skewing the output even when both
        // were equally selected by the admin. Mirrors CodeService/CrosswordService's
        // outer/inner split.
        $outerAttempts = 10;
        $innerAttempts = 20;
        $solved = false;
        for ($opAttempt = 0; $opAttempt < $outerAttempts && ! $solved; $opAttempt++) {
            $opVars = [];
            foreach ($operatorConf as $key => $conf) {
                $options = array_values(array_filter((array) ($conf['options'] ?? []), fn ($o) => isset(self::OPERATOR_LABELS[$o])));
                if (! $options) $options = ['+'];
                $opVars[$key] = $options[array_rand($options)];
            }

            for ($attempt = 0; $attempt < $innerAttempts; $attempt++) {
                $numVars = $opVars;
                foreach ($randomConf as $key => $conf) {
                    $step = max(1, (int) ($conf['step'] ?? 1));
                    $steps = (int) (($conf['max'] - $conf['min']) / $step);
                    $numVars[$key] = $conf['min'] + rand(0, $steps) * $step;
                }
                foreach ($derivedConf as $key => $conf) {
                    $numVars[$key] = $this->evalExpr((string) ($conf['derived'] ?? '0'), $numVars);
                }
                foreach ($conditionalConf as $key => $conf) {
                    $numVars[$key] = $this->resolveTextFormula((string) ($conf['conditional'] ?? ''), $numVars, []);
                }

                // Uniqueness only makes sense among the actual numbers — an operator
                // symbol or a conditional label repeating isn't a "duplicate value"
                // in the sense this checkbox means.
                if ($uniqueVars) {
                    $uniqueCheck = array_diff_key($numVars, $operatorConf, $conditionalConf);
                    if (count(array_unique($uniqueCheck)) !== count($uniqueCheck)) continue;
                }

                $f = $baseFormula;
                foreach ($numVars as $k => $v) {
                    $f = str_replace($k, (string) $v, $f);
                }
                // Strip anything left over that isn't a valid arithmetic character —
                // an unresolved variable reference (typo, or one that depends on a
                // row added after it) would otherwise reach eval() as bare text.
                $f = preg_replace('/[^0-9+\-*\/()\s%.]/', '', $f);
                // @ only suppresses warnings — malformed input makes eval() throw a
                // real ParseError/Error, which @ does NOT catch. Without this
                // try/catch, one bad formula crashes the whole request instead of
                // just being treated as an invalid attempt to retry.
                try {
                    $rawResult = @eval("return ({$f});");
                } catch (\Throwable $e) {
                    $rawResult = false;
                }
                // Reject non-whole results outright instead of silently truncating —
                // matters most for an operator variable landing on "/" where the
                // numbers don't happen to divide evenly this attempt.
                if ($rawResult === false || ! is_numeric($rawResult) || $rawResult <= 0) continue;
                if (floor($rawResult) != $rawResult) continue;
                $result = (int) $rawResult;
                if (!$this->conditionsMet($conditions, $numVars)) continue;
                $correct = $result;
                $solved = true;
                break;
            }
        }

        // A broken formula (typo, stale variable reference) must never silently
        // degrade to correct_answer=0 — that would hand a child a question with a
        // meaningless "correct" answer. Throw so the caller can skip this template
        // and pick another one instead of serving it.
        if (! $solved) {
            \Illuminate\Support\Facades\Log::warning('QuestionTemplate: correct_formula never resolved after 200 attempts', [
                'template_id' => $this->id,
                'correct_formula' => $baseFormula,
            ]);
            throw new \App\Exceptions\QuestionGenerationException(
                "Template #{$this->id}: correct_formula '{$baseFormula}' never resolved"
            );
        }

        // Display values for the question text/hint — an operator variable shows
        // its Georgian label here ("ით მეტი") instead of the raw symbol ("+") that
        // was substituted into correct_formula above.
        $displayVars = $numVars;
        foreach ($operatorConf as $key => $conf) {
            if (isset($displayVars[$key])) {
                $displayVars[$key] = self::OPERATOR_LABELS[$displayVars[$key]] ?? $displayVars[$key];
            }
        }

        $vars = array_merge($displayVars, $theme->resolveVariables());

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
     * For answer_type "numeric_input" there are no options at all — the child
     * types the number in directly — so distractor generation is skipped.
     */
    private function buildMcOptions(int $correct): array
    {
        if ($this->answer_type === 'numeric_input') {
            return ['options' => null, 'correct_answer' => (string) $correct];
        }

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

            // If multiple operators were selected but every slot collapsed to the same
            // one by chance, force one slot to differ — otherwise a 3+ variable chain
            // could render as "N1 + N2 + N3" even though '-' was selected alongside '+'.
            if (count($operators) > 1 && count($opsChosen) > 1 && count(array_unique($opsChosen)) < 2) {
                $alt = $operators[array_rand($operators)];
                while ($alt === $opsChosen[0]) $alt = $operators[array_rand($operators)];
                $opsChosen[array_rand($opsChosen)] = $alt;
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
