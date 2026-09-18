<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Achievement extends Model
{
    protected $fillable = ['slug', 'theme_id', 'name', 'description', 'condition_type', 'condition_config', 'daily_limit', 'is_active'];

    protected $casts = [
        'condition_config' => 'array',
        'daily_limit'      => 'boolean',
        'is_active'        => 'boolean',
    ];

    public const CONDITION_TYPES = [
        'test_count'          => 'ტესტების რაოდენობა',
        'perfect_score'       => 'იდეალური შედეგი (100%)',
        'day_streak'          => 'ზედიზედ დღეები',
        'speed_seconds'       => 'სისწრაფე (წამებში)',
        'consecutive_correct' => 'ზედიზედ სწორი პასუხები',
        'comeback'            => 'დაბრუნება ცუდი ტესტის შემდეგ',
        'time_of_day'         => 'დღის დრო',
        'max_level_reached'   => 'მაქსიმალური დონე',
        'olympiad_completed'  => 'ოლიმპიადაში მონაწილეობა',
        'coins_earned'        => 'დაგროვილი მონეტები',
    ];

    /** Condition types that don't use a numeric threshold on their tier(s). */
    public const BINARY_TYPES = ['comeback', 'time_of_day', 'perfect_score', 'max_level_reached'];

    public function tiers(): HasMany
    {
        return $this->hasMany(AchievementTier::class)->orderBy('level');
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }
}
