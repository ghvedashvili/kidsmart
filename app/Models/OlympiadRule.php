<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OlympiadRule extends Model
{
    protected $fillable = ['grade_id', 'olympiad_date', 'tests_required', 'days_window', 'questions_count'];

    protected $casts = ['olympiad_date' => 'date'];

    public const DEFAULT_TESTS_REQUIRED  = 5;
    public const DEFAULT_DAYS_WINDOW     = 14;
    public const DEFAULT_QUESTIONS_COUNT = 20;

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Resolve the olympiad rule for a grade: a per-grade override takes priority
     * over the global default (grade_id null), which falls back to the class
     * defaults. A null olympiad_date means "no olympiad scheduled".
     *
     * @return array{olympiad_date:?\Illuminate\Support\Carbon, tests_required:int, days_window:int, questions_count:int}
     */
    public static function resolve(?int $gradeId): array
    {
        $rule = $gradeId ? static::where('grade_id', $gradeId)->first() : null;
        $rule = $rule ?? static::whereNull('grade_id')->first();

        return [
            'olympiad_date'   => $rule->olympiad_date ?? null,
            'tests_required'  => $rule->tests_required  ?? self::DEFAULT_TESTS_REQUIRED,
            'days_window'     => $rule->days_window     ?? self::DEFAULT_DAYS_WINDOW,
            'questions_count' => $rule->questions_count ?? self::DEFAULT_QUESTIONS_COUNT,
        ];
    }
}
