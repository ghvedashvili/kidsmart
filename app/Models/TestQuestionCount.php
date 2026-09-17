<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestQuestionCount extends Model
{
    protected $fillable = ['grade_id', 'difficulty', 'theme_id', 'questions_count'];

    /** Used when no admin override matches at all. */
    public const DEFAULT_COUNT = 15;

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * Resolve how many questions a test should have for this grade+difficulty(+theme).
     * Priority: exact (grade,difficulty,theme) > (grade,difficulty, any theme) >
     * (grade, any difficulty, theme) > (grade, any difficulty, any theme) > global default.
     * A null difficulty/theme on a stored row means "applies to any" for that dimension.
     */
    public static function resolve(int $gradeId, int $difficulty, ?int $themeId): int
    {
        if ($themeId) {
            $exact = static::where('grade_id', $gradeId)
                ->where('difficulty', $difficulty)
                ->where('theme_id', $themeId)
                ->value('questions_count');
            if ($exact) return $exact;
        }

        $general = static::where('grade_id', $gradeId)
            ->where('difficulty', $difficulty)
            ->whereNull('theme_id')
            ->value('questions_count');
        if ($general) return $general;

        if ($themeId) {
            $anyDifficulty = static::where('grade_id', $gradeId)
                ->whereNull('difficulty')
                ->where('theme_id', $themeId)
                ->value('questions_count');
            if ($anyDifficulty) return $anyDifficulty;
        }

        $anyDifficultyGeneral = static::where('grade_id', $gradeId)
            ->whereNull('difficulty')
            ->whereNull('theme_id')
            ->value('questions_count');
        if ($anyDifficultyGeneral) return $anyDifficultyGeneral;

        return self::DEFAULT_COUNT;
    }
}
