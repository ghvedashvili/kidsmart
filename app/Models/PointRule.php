<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointRule extends Model
{
    protected $fillable = ['grade_id', 'difficulty', 'context', 'points_per_correct'];

    protected $casts = ['points_per_correct' => 'float'];

    public const CONTEXTS = ['test', 'practice', 'olympiad'];

    public const DEFAULT_POINTS = 1.0;

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Resolve points-per-correct-answer for a grade+difficulty+context, falling back
     * through progressively broader tiers: exact grade+difficulty override → grade-wide
     * default (any difficulty) → difficulty-wide default (any grade) → global default
     * (any grade/difficulty) → hardcoded constant (1, matching the app's original
     * behavior before this was configurable). points_per_correct is decimal, so this
     * can return fractional values (e.g. 0.5).
     */
    public static function resolve(?int $gradeId, ?int $difficulty, string $context): float
    {
        if ($gradeId && $difficulty) {
            $exact = static::where('grade_id', $gradeId)
                ->where('difficulty', $difficulty)
                ->where('context', $context)
                ->value('points_per_correct');
            if ($exact !== null) return (float) $exact;
        }

        if ($gradeId) {
            $gradeDefault = static::where('grade_id', $gradeId)
                ->whereNull('difficulty')
                ->where('context', $context)
                ->value('points_per_correct');
            if ($gradeDefault !== null) return (float) $gradeDefault;
        }

        if ($difficulty) {
            $difficultyDefault = static::whereNull('grade_id')
                ->where('difficulty', $difficulty)
                ->where('context', $context)
                ->value('points_per_correct');
            if ($difficultyDefault !== null) return (float) $difficultyDefault;
        }

        $global = static::whereNull('grade_id')
            ->whereNull('difficulty')
            ->where('context', $context)
            ->value('points_per_correct');

        return $global !== null ? (float) $global : self::DEFAULT_POINTS;
    }
}
