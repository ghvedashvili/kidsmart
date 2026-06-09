<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionTemplate extends Model
{
    protected $fillable = ['topic_id', 'difficulty', 'template_text', 'correct_formula', 'num_config', 'distractors'];

    protected $casts = ['num_config' => 'array', 'distractors' => 'array'];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function generate(Theme $theme): array
    {
        $varMap  = $theme->variableMap();
        $numConf = $this->num_config ?? [];
        $baseFormula = preg_replace('/\{\{(\w+)\}\}/', '$1', $this->correct_formula);

        // retry until correct answer is a positive integer (max 20 attempts)
        $numVars = [];
        $correct = 0;
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $numVars = [];
            foreach ($numConf as $key => $conf) {
                $numVars[$key] = rand($conf['min'], $conf['max']);
            }
            $f = $baseFormula;
            foreach ($numVars as $k => $v) {
                $f = str_replace($k, (string) $v, $f);
            }
            $result = @eval("return (int)({$f});");
            if ($result !== false && $result > 0) {
                $correct = $result;
                break;
            }
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

        return [
            'question_text'  => $text,
            'options'        => array_map('strval', $options),
            'correct_answer' => (string) $correct,
        ];
    }
}
