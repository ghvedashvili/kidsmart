<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionTemplate extends Model
{
    protected $fillable = ['topic_id', 'difficulty', 'template_text', 'correct_formula', 'num_config'];

    protected $casts = ['num_config' => 'array'];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function generate(Theme $theme): array
    {
        $varMap   = $theme->variableMap();
        $numConf  = $this->num_config ?? [];
        $vars     = [];

        foreach ($numConf as $key => $conf) {
            $vars[$key] = rand($conf['min'], $conf['max']);
        }

        foreach ($varMap as $name => $values) {
            $vars[$name] = $values[array_rand($values)];
        }

        // normalize text: fix single-brace typos like {{N3} → {{N3}}
        $text    = preg_replace('/\{\{(\w+)\}(?!\})/', '{{$1}}', $this->template_text);
        $formula = preg_replace('/\{\{(\w+)\}\}/', '$1', $this->correct_formula);

        foreach ($vars as $k => $v) {
            $text    = str_replace("{{{$k}}}", $v, $text);
            $formula = str_replace($k, (string) $v, $formula);
        }

        // remove any leftover {{...}} that had no matching var
        $text = preg_replace('/\{\{\w+\}\}/', '?', $text);

        $correct = @eval("return (int)({$formula});");
        if ($correct === false || $correct === null) {
            $correct = 0;
        }

        $wrong = [];
        $offsets = [1, -1, 2, -2, 3, -3, 5, -5, 10, -10];
        shuffle($offsets);
        foreach ($offsets as $off) {
            $candidate = $correct + $off;
            if ($candidate > 0 && ! in_array($candidate, $wrong) && $candidate !== $correct) {
                $wrong[] = $candidate;
                if (count($wrong) === 4) break;
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
