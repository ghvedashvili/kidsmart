<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameSession extends Model
{
    protected $fillable = [
        'child_id', 'game', 'player_score', 'computer_score', 'wins', 'losses',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(User::class, 'child_id');
    }

    public static function forChild(int $childId, string $game): self
    {
        return static::firstOrCreate(
            ['child_id' => $childId, 'game' => $game],
            ['player_score' => 0, 'computer_score' => 0, 'wins' => 0, 'losses' => 0]
        );
    }
}
