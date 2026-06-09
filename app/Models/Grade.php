<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    protected $fillable = ['number', 'name'];

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }
}
