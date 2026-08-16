<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionTemplate extends Model
{
    protected $fillable = ['topic_id', 'theme_id', 'difficulty', 'answer_type', 'template_text', 'hint_text', 'correct_formula', 'num_config', 'distractors', 'conditions'];

    protected $casts = ['num_config' => 'array', 'distractors' => 'array', 'conditions' => 'array'];

    private function evalExpr(string $expr, array $vars): int
    {
        $e = $expr;
        foreach ($vars as $k => $v) {
            $e = str_replace($k, (string) $v, $e);
        }
        $e = preg_replace('/[^0-9+\-*\/()\s]/', '', $e);
        $result = @eval("return (int)({$e});");
        return ($result === false || !is_numeric($result)) ? 0 : (int) $result;
    }

    private function conditionsMet(array $conditions, array $vars): bool
    {
        foreach ($conditions as $c) {
            $l = $this->evalExpr((string) ($c['left']  ?? '0'), $vars);
            $r = $this->evalExpr((string) ($c['right'] ?? '0'), $vars);
            $ok = match($c['op'] ?? '') {
                '>'  => $l > $r,
                '<'  => $l < $r,
                '>=' => $l >= $r,
                '<=' => $l <= $r,
                '!=' => $l !== $r,
                '%0' => $r !== 0 && $l % $r === 0,
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

    public function generate(Theme $theme): array
    {
        return $this->answer_type === 'text'
            ? $this->generateText($theme)
            : $this->generateNumeric($theme);
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
        $baseFormula = preg_replace('/\{\{(\w+)\}\}/', '$1', $this->correct_formula);

        $conditions = $this->conditions ?? [];
        $numVars = [];
        $correct = 0;
        for ($attempt = 0; $attempt < 40; $attempt++) {
            $numVars = [];
            foreach ($numConf as $key => $conf) {
                $step = max(1, (int) ($conf['step'] ?? 1));
                $steps = (int) (($conf['max'] - $conf['min']) / $step);
                $numVars[$key] = $conf['min'] + rand(0, $steps) * $step;
            }
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

        $hintRaw = preg_replace('/\{\{(\w+)\}\}(?!\})/', '{{$1}}', $this->hint_text ?? '');
        foreach ($vars as $k => $v) {
            $hintRaw = str_replace("{{{$k}}}", (string) $v, $hintRaw);
        }
        $hint = trim(preg_replace('/\{\{\w+\}\}/', '?', $hintRaw)) ?: null;

        return [
            'question_text'  => $text,
            'hint_text'      => $hint,
            'options'        => array_map('strval', $options),
            'correct_answer' => $correctAnswer,
        ];
    }
}
