<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id', 'package_id', 'billing_cycle',
        'starts_at', 'expires_at', 'assigned_by', 'note',
    ];

    protected $casts = [
        'starts_at'  => 'date',
        'expires_at' => 'date',
    ];

    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function package(): BelongsTo  { return $this->belongsTo(Package::class); }
    public function assignedBy(): BelongsTo { return $this->belongsTo(User::class, 'assigned_by'); }

    public function isActive(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }
}
