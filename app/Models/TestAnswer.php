<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = ['test_id', 'test_question_id', 'selected_answer', 'is_correct', 'answered_at'];

    protected $casts = ['answered_at' => 'datetime'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(TestQuestion::class, 'test_question_id');
    }
}
