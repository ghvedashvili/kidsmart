<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticeAnswerLog extends Model
{
    protected $fillable = [
        'child_id', 'topic_id', 'type', 'is_correct', 'prompt', 'given', 'correct',
    ];

    protected $casts = ['is_correct' => 'boolean'];

    public function child(): BelongsTo
    {
        return $this->belongsTo(User::class, 'child_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}
