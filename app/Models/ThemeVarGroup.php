<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThemeVarGroup extends Model
{
    protected $fillable = ['theme_id', 'name', 'values'];

    protected $casts = ['values' => 'array'];

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function variables(): HasMany
    {
        return $this->hasMany(ThemeVariable::class, 'group_id');
    }
}
