<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketItem extends Model
{
    protected $fillable = ['child_id', 'title', 'icon', 'category', 'coin_cost', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function child(): BelongsTo
    {
        return $this->belongsTo(User::class, 'child_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(MarketPurchase::class);
    }

    public function pendingPurchase(int $childId): ?MarketPurchase
    {
        return $this->purchases()->where('child_id', $childId)->where('status', 'pending')->first();
    }
}
