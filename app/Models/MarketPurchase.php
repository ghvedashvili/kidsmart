<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketPurchase extends Model
{
    protected $fillable = ['market_item_id', 'child_id', 'status', 'coins_spent'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(MarketItem::class, 'market_item_id');
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(User::class, 'child_id');
    }
}
