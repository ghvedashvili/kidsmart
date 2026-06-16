<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionTemplate extends Model
{
    protected $fillable = ['topic_id', 'difficulty', 'template_text', 'hint_text', 'correct_formula', 'num_config', 'distractors', 'conditions'];

    protected $casts = ['num_config' => 'array', 'distractors' => 'array', 'conditions' => 'array'];

    private function conditionsMet(array $conditions, array $vars): bool
    {
        foreach ($conditions as $c) {
            $l = is_numeric($c['left'])  ? (int)$c['left']  : (int)($vars[$c['left']]  ?? 0);
            $r = is_numeric($c['right']) ? (int)$c['right'] : (int)($vars[$c['right']] ?? 0);
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

    public function generate(Theme $theme): array
    {
        $varMap  = $theme->variableMap();
        $numConf = $this->num_config ?? [];
        $baseFormula = preg_replace('/\{\{(\w+)\}\}/', '$1', $this->correct_formula);

        // retry until correct answer is a positive integer AND all conditions pass (max 40 attempts)
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

        // theme vars (strings — only used in text, not formula)
        $vars = $numVars;
        foreach ($varMap as $name => $values) {
            $vars[$name] = $values[array_rand($values)];
        }

        // normalize text: fix single-brace typos like {{N3} → {{N3}}
        $text = preg_replace('/\{\{(\w+)\}(?!\})/', '{{$1}}', $this->template_text);
        foreach ($vars as $k => $v) {
            $text = str_replace("{{{$k}}}", $v, $text);
        }
        // remove any leftover {{...}} that had no matching var
        $text = preg_replace('/\{\{\w+\}\}/', '?', $text);

        $dist    = $this->distractors;
        $dMin    = max(1, (int) ($dist['min'] ?? 1));
        $dMax    = max($dMin, (int) ($dist['max'] ?? 10));

        $wrong   = [];
        $attempts = 0;
        while (count($wrong) < 4 && $attempts < 100) {
            $attempts++;
            $delta     = rand($dMin, $dMax);
            $sign      = rand(0, 1) ? 1 : -1;
            $candidate = $correct + ($sign * $delta);
            if ($candidate > 0 && ! in_array($candidate, $wrong) && $candidate !== $correct) {
                $wrong[] = $candidate;
            }
        }

        $options = array_merge([$correct], $wrong);
        shuffle($options);

        $hintRaw = preg_replace('/\{\{(\w+)\}\}(?!\})/', '{{$1}}', $this->hint_text ?? '');
        foreach ($vars as $k => $v) {
            $hintRaw = str_replace("{{{$k}}}", $v, $hintRaw);
        }
        $hint = trim(preg_replace('/\{\{\w+\}\}/', '?', $hintRaw)) ?: null;

        return [
            'question_text'  => $text,
            'hint_text'      => $hint,
            'options'        => array_map('strval', $options),
            'correct_answer' => (string) $correct,
        ];
    }
}
