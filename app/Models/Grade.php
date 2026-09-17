<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    protected $fillable = ['number', 'name', 'is_active', 'max_level'];

    protected $casts = ['is_active' => 'boolean', 'max_level' => 'integer'];

    public const DEFAULT_MAX_LEVEL = 3;

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }
}
