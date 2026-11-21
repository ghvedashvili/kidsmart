<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** ადმინის პირადი ჩანაწერები („ჩანაწერები“ პანელი ადმინის გვერდებზე) */
class AdminNote extends Model
{
    protected $fillable = ['user_id', 'body', 'is_open'];

    protected $casts = ['is_open' => 'boolean'];

    public static function for(int $userId): self
    {
        return static::firstOrNew(['user_id' => $userId], ['body' => '', 'is_open' => false]);
    }
}
