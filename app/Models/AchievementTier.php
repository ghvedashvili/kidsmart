<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AchievementTier extends Model
{
    protected $fillable = ['achievement_id', 'level', 'label', 'threshold', 'image_path'];

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? \Storage::url($this->image_path) : null;
    }
}
