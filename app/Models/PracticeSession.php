<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticeSession extends Model
{
    protected $fillable = [
        'child_id', 'topic_id', 'session_type',
        'level', 'streak', 'total_answered', 'total_correct', 'last_activity_at',
    ];

    protected $casts = ['last_activity_at' => 'datetime'];

    public function child(): BelongsTo
    {
        return $this->belongsTo(User::class, 'child_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public static function forChild(int $childId, ?int $topicId, string $type): self
    {
        return static::firstOrCreate(
            ['child_id' => $childId, 'topic_id' => $topicId, 'session_type' => $type],
            ['level' => 1, 'streak' => 0, 'total_answered' => 0, 'total_correct' => 0]
        );
    }
}
