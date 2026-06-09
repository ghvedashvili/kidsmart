<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    protected $fillable = ['name', 'icon'];

    public function variables(): HasMany
    {
        return $this->hasMany(ThemeVariable::class);
    }

    public function variableMap(): array
    {
        return $this->variables->pluck('values', 'variable_name')->toArray();
    }
}
