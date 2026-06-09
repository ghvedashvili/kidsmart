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

        $text   = $this->template_text;
        $formula = $this->correct_formula;

        foreach ($vars as $k => $v) {
            $text    = str_replace("{{{$k}}}", $v, $text);
            $formula = str_replace($k, $v, $formula);
        }

        $correct = eval("return (int)({$formula});");

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
