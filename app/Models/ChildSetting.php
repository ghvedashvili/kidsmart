<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildSetting extends Model
{
    protected $fillable = [
        'user_id', 'grade_id', 'difficulty', 'tests_per_week', 'coins', 'difficulty_streak',
        'topic_rotation', 'tests_since_level_review',
    ];

    protected $casts = [
        'topic_rotation' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
}
