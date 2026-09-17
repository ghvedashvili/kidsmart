<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LevelUpRule extends Model
{
    protected $fillable = ['grade_id', 'tests_required', 'up_threshold', 'down_threshold'];

    public const DEFAULT_TESTS_REQUIRED = 7;
    public const DEFAULT_UP_THRESHOLD   = 85;
    public const DEFAULT_DOWN_THRESHOLD = 60;

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Resolve the level-up rule for a grade, falling back to the global defaults
     * when the admin hasn't configured that grade specifically.
     *
     * @return array{tests_required:int, up_threshold:int, down_threshold:int}
     */
    public static function resolve(?int $gradeId): array
    {
        $rule = $gradeId ? static::where('grade_id', $gradeId)->first() : null;

        return [
            'tests_required' => $rule->tests_required ?? self::DEFAULT_TESTS_REQUIRED,
            'up_threshold'   => $rule->up_threshold   ?? self::DEFAULT_UP_THRESHOLD,
            'down_threshold' => $rule->down_threshold ?? self::DEFAULT_DOWN_THRESHOLD,
        ];
    }
}
