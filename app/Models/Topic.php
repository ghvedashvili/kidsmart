<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    protected $fillable = ['grade_id', 'name', 'sequence', 'is_active'];

    protected $casts = ['sequence' => 'integer', 'is_active' => 'boolean'];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function questionTemplates(): HasMany
    {
        return $this->hasMany(QuestionTemplate::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(TopicVideo::class)->orderBy('order');
    }
}
