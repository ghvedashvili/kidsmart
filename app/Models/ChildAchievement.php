<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildAchievement extends Model
{
    public $timestamps = false;

    protected $fillable = ['child_id', 'slug', 'earned_at'];

    protected $casts = ['earned_at' => 'datetime'];

    public function child(): BelongsTo
    {
        return $this->belongsTo(User::class, 'child_id');
    }
}
