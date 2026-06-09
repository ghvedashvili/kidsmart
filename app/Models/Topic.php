<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    protected $fillable = ['grade_id', 'name'];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function questionTemplates(): HasMany
    {
        return $this->hasMany(QuestionTemplate::class);
    }
}
