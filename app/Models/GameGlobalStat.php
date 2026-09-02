<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameGlobalStat extends Model
{
    protected $fillable = [
        'game', 'total_wins', 'total_losses',
    ];

    public static function forGame(string $game): self
    {
        return static::firstOrCreate(
            ['game' => $game],
            ['total_wins' => 0, 'total_losses' => 0]
        );
    }
}
