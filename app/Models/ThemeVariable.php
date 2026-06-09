<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeVariable extends Model
{
    protected $fillable = ['theme_id', 'variable_name', 'values'];

    protected $casts = ['values' => 'array'];

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }
}
