<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestQuestion extends Model
{
    public $timestamps = false;

    protected $fillable = ['test_id', 'template_id', 'question_text', 'options', 'correct_answer', 'order'];

    protected $casts = ['options' => 'array'];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
