<?php

namespace App\Services;

use App\Models\ChildAchievement;
use App\Models\ChildSetting;
use App\Models\Test;
use App\Models\User;

class AchievementService
{
    // Coins per difficulty × performance tier
    private const COINS = [
        1 => ['base' => 10, 'perfect' => 15, 'good' => 12, 'ok' => 10, 'low' => 6],
        2 => ['base' => 20, 'perfect' => 30, 'good' => 24, 'ok' => 20, 'low' => 12],
        3 => ['base' => 35, 'perfect' => 52, 'good' => 42, 'ok' => 35, 'low' => 20],
        4 => ['base' => 55, 'perfect' => 82, 'good' => 66, 'ok' => 55, 'low' => 33],
        5 => ['base' => 80, 'perfect' => 120, 'good' => 96, 'ok' => 80, 'low' => 48],
    ];

    // All achievement definitions
    public const ACHIEVEMENTS = [
        // სტიკერები
        'first_test'    => ['emoji' => '⭐', 'name' => 'პირველი ტესტი!',      'desc' => 'პირველი ტესტი დაასრულე',              'type' => 'sticker'],
        'first_perfect' => ['emoji' => '💫', 'name' => 'იდეალური!',           'desc' => 'ყველა კითხვა სწორად გასცე',           'type' => 'sticker'],
        'perfect_hard'  => ['emoji' => '🔥', 'name' => 'გენიოსი!',            'desc' => '100% სირთულე 4+ ზე',                 'type' => 'sticker'],
        'comeback'      => ['emoji' => '💪', 'name' => 'დაბრუნება!',          'desc' => 'ცუდი ტესტის შემდეგ 80%+ მოიპოვე',   'type' => 'sticker'],
        'early_bird'    => ['emoji' => '🐦', 'name' => 'ადრეული ჩიტი',       'desc' => 'ტესტი დილის 8:00-მდე',              'type' => 'sticker'],
        'night_owl'     => ['emoji' => '🦉', 'name' => 'ღამის ბუ',           'desc' => 'ტესტი 21:00-ის შემდეგ',             'type' => 'sticker'],
        // მედლები — ტესტები
        'tests_5'       => ['emoji' => '🥉', 'name' => '5 ტესტი',            'desc' => 'სულ 5 ტესტი დაასრულე',              'type' => 'medal'],
        'tests_25'      => ['emoji' => '🥈', 'name' => '25 ტესტი',           'desc' => 'სულ 25 ტესტი დაასრულე',             'type' => 'medal'],
        'tests_100'     => ['emoji' => '🥇', 'name' => '100 ტესტი',          'desc' => 'სულ 100 ტესტი დაასრულე',            'type' => 'medal'],
        // მედლები — მონეტები
        'coins_100'     => ['emoji' => '💰', 'name' => 'პირველი საფულე',     'desc' => '100 მონეტა დააგროვე',               'type' => 'medal'],
        'coins_500'     => ['emoji' => '💎', 'name' => 'მდიდარი',            'desc' => '500 მონეტა დააგროვე',               'type' => 'medal'],
        'coins_2000'    => ['emoji' => '👑', 'name' => 'მეფე',               'desc' => '2000 მონეტა დააგროვე',              'type' => 'medal'],
        // მედლები — სერია
        'streak_3'      => ['emoji' => '🔥', 'name' => 'ცეცხლი!',           'desc' => '3 ტესტი ზედიზედ 80%+',             'type' => 'medal'],
        'streak_7'      => ['emoji' => '⚡', 'name' => 'ელვა!',             'desc' => '7 ტესტი ზედიზედ 80%+',             'type' => 'medal'],
        // მედლები — სირთულე
        'expert'        => ['emoji' => '🚀', 'name' => 'ექსპერტი',          'desc' => 'სირთულე 5 დაიმსახურე',             'type' => 'medal'],
    ];

    public function handleTestCompletion(Test $test, User $child): array
    {
        $setting = $child->childSetting;
        $total   = $test->total_questions;
        $correct = $test->correct_count ?? 0;
        $pct     = $total > 0 ? $correct / $total : 0;

        // 1. Coins
        $coins = $this->calcCoins($setting->difficulty, $pct);
        $test->update(['coins_earned' => $coins]);
        $setting->increment('coins', $coins);
        $setting->refresh();

        // 2. Difficulty auto-adjust
        $this->adjustDifficulty($setting, $pct);
        $setting->refresh();

        // 3. Achievements
        $newAchievements = $this->checkAchievements($child, $test, $setting, $pct);

        return [
            'coins'           => $coins,
            'total_coins'     => $setting->coins,
            'new_achievements' => $newAchievements,
            'difficulty'      => $setting->difficulty,
        ];
    }

    private function calcCoins(int $difficulty, float $pct): int
    {
        $tiers = self::COINS[$difficulty] ?? self::COINS[1];
        return match(true) {
            $pct >= 1.0 => $tiers['perfect'],
            $pct >= 0.8 => $tiers['good'],
            $pct >= 0.6 => $tiers['ok'],
            default     => $tiers['low'],
        };
    }

    private function adjustDifficulty(ChildSetting $setting, float $pct): void
    {
        $streak = $setting->difficulty_streak;

        if ($pct >= 0.8) {
            $streak = $streak >= 0 ? $streak + 1 : 1;
        } elseif ($pct <= 0.4) {
            $streak = $streak <= 0 ? $streak - 1 : -1;
        } else {
            $streak = 0;
        }

        $diff = $setting->difficulty;
        if ($streak >= 3) {
            $diff   = min(5, $diff + 1);
            $streak = 0;
        } elseif ($streak <= -3) {
            $diff   = max(1, $diff - 1);
            $streak = 0;
        }

        $setting->update(['difficulty' => $diff, 'difficulty_streak' => $streak]);
    }

    private function checkAchievements(User $child, Test $test, ChildSetting $setting, float $pct): array
    {
        $earned   = ChildAchievement::where('child_id', $child->id)->pluck('slug')->flip()->toArray();
        $totalTests = Test::where('child_id', $child->id)->whereNotNull('completed_at')->count();

        // Last 7 tests' performance for streak
        $lastTests  = Test::where('child_id', $child->id)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->take(7)
            ->get();

        $streak = 0;
        foreach ($lastTests as $t) {
            if ($t->total_questions > 0 && $t->correct_count / $t->total_questions >= 0.8) {
                $streak++;
            } else break;
        }

        // Previous test for comeback check
        $prevTest = Test::where('child_id', $child->id)
            ->whereNotNull('completed_at')
            ->where('id', '!=', $test->id)
            ->latest('completed_at')
            ->first();
        $prevPct = $prevTest && $prevTest->total_questions > 0
            ? $prevTest->correct_count / $prevTest->total_questions
            : null;

        $candidates = [
            'first_test'    => $totalTests === 1,
            'first_perfect' => $pct >= 1.0,
            'perfect_hard'  => $pct >= 1.0 && $setting->difficulty >= 4,
            'comeback'      => $prevPct !== null && $prevPct <= 0.4 && $pct >= 0.8,
            'early_bird'    => now()->hour < 8,
            'night_owl'     => now()->hour >= 21,
            'tests_5'       => $totalTests >= 5,
            'tests_25'      => $totalTests >= 25,
            'tests_100'     => $totalTests >= 100,
            'coins_100'     => $setting->coins >= 100,
            'coins_500'     => $setting->coins >= 500,
            'coins_2000'    => $setting->coins >= 2000,
            'streak_3'      => $streak >= 3,
            'streak_7'      => $streak >= 7,
            'expert'        => $setting->difficulty >= 5,
        ];

        $new = [];
        foreach ($candidates as $slug => $met) {
            if ($met && ! isset($earned[$slug])) {
                ChildAchievement::create([
                    'child_id'  => $child->id,
                    'slug'      => $slug,
                    'earned_at' => now(),
                ]);
                $new[] = array_merge(['slug' => $slug], self::ACHIEVEMENTS[$slug]);
            }
        }

        return $new;
    }
}
