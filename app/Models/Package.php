<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'name', 'slug', 'description',
        'price_monthly', 'price_yearly',
        'max_children', 'max_difficulty',
        'is_free', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_free'        => 'boolean',
        'is_active'      => 'boolean',
        'price_monthly'  => 'float',
        'price_yearly'   => 'float',
        'max_children'   => 'integer',
        'max_difficulty' => 'integer',
        'sort_order'     => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function maxChildrenLabel(): string
    {
        return $this->max_children === 0 ? 'შეუზღუდავი' : (string) $this->max_children;
    }
}
