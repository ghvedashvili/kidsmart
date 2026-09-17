<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildGradeHistory extends Model
{
    protected $table = 'child_grade_history';

    protected $fillable = ['user_id', 'grade_id', 'difficulty', 'tests_completed'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
}
