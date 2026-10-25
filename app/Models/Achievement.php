<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Achievement extends Model
{
    protected $fillable = ['slug', 'theme_id', 'context', 'game', 'name', 'description', 'condition_type', 'condition_config', 'is_active'];

    protected $casts = [
        'condition_config' => 'array',
        'is_active'        => 'boolean',
    ];

    /**
     * Which activity category this achievement belongs to — 'any' is checked
     * alongside whichever specific category just triggered (test/practice/
     * olympiad/games), since metrics like day_streak/coins_earned aren't tied
     * to one activity. See AchievementService::checkAchievements().
     */
    public const CONTEXTS = [
        'test'     => 'ტესტი',
        'practice' => 'ვარჯიში',
        'olympiad' => 'ოლიმპიადა',
        'games'    => 'თამაშები',
        'any'      => 'საერთო',
    ];

    public const CONDITION_TYPES = [
        'test_count'                   => 'ტესტების რაოდენობა',
        'perfect_score'                => 'იდეალური შედეგი (100%)',
        'day_streak'                   => 'ზედიზედ დღეები (ტესტი+ვარჯიში)',
        'speed_seconds'                => 'სისწრაფე (წამებში)',
        'consecutive_correct'          => 'ზედიზედ სწორი პასუხები',
        'comeback'                     => 'დაბრუნება ცუდი ტესტის შემდეგ',
        'time_of_day'                  => 'დღის დრო',
        'max_level_reached'            => 'მაქსიმალური დონე (კლასის ჭერი)',
        'difficulty_reached'           => 'კონკრეტულ დონეს მიღწევა',
        'olympiad_completed'           => 'ოლიმპიადაში მონაწილეობა',
        'olympiad_perfect'             => 'იდეალური შედეგი ოლიმპიადაში',
        'coins_earned'                 => 'დაგროვილი მონეტები',
        'practice_count'               => 'ვარჯიშის პასუხების რაოდენობა',
        'practice_consecutive_correct' => 'ზედიზედ სწორი პასუხი ვარჯიშში',
        'practice_full_rotation'       => 'ყველა თემა ნანახია ვარჯიშში',
        'practice_comeback'            => 'დაბრუნება ვარჯიშში',
        'game_wins'                    => 'მოგებების რაოდენობა (თამაშებში)',
        'game_win_streak'              => 'ზედიზედ მოგება (თამაშებში)',
        'all_games_tried'              => 'ყველა თამაში ნაცადია',
    ];

    /** Condition types that don't use a numeric threshold on their tier(s). */
    public const BINARY_TYPES = [
        'comeback', 'time_of_day', 'perfect_score', 'max_level_reached',
        'olympiad_perfect', 'practice_full_rotation', 'practice_comeback', 'all_games_tried',
    ];

    public function tiers(): HasMany
    {
        return $this->hasMany(AchievementTier::class)->orderBy('level');
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }
}
