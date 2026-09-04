<?php

namespace App\Services;

use App\Models\ChildAchievement;
use App\Models\ChildSetting;
use App\Models\Test;
use App\Models\User;

class AchievementService
{
    // All achievement definitions
    public const ACHIEVEMENTS = [
        // სტიკერები
        'first_test'    => ['emoji' => '⭐', 'name' => 'პირველი ტესტი!',      'desc' => 'პირველი ტესტი დაასრულე',                    'type' => 'sticker'],
        'first_perfect' => ['emoji' => '🎯', 'name' => 'პედრი!',           'desc' => 'ყველა პასუხი პედრის პასებივით სწორეა',                 'type' => 'sticker'],
        'comeback'      => ['emoji' => '💪', 'name' => 'დაბრუნება!',          'desc' => 'ცუდი ტესტის შემდეგ 80%+ მოიპოვე',          'type' => 'sticker'],
        'early_bird'    => ['emoji' => '🌅', 'name' => 'დილა მშვიდობისა',    'desc' => 'გააკეთე ტესტი დილის 9:00-მდე',                     'type' => 'sticker'],
        'night_owl'     => ['emoji' => '🌙', 'name' => 'ძილისნებისა',        'desc' => 'გააკეთე ტესტი 22:30-ის შემდეგ',                    'type' => 'sticker'],
        'ronaldo'       => ['emoji' => '⚡', 'name' => 'კრისტიანო რონალდო',  'desc' => 'ტესტი 1 წუთში დაასრულე — იყავი რონალდოსავით სწრაფი!',  'type' => 'sticker'],
        'messi'         => ['emoji' => '🐐', 'name' => 'ლეო მესი',           'desc' => '10 დღე ზედიზედ მინიმუმ 1 ტესტი',           'type' => 'sticker'],
        'yamal'         => ['emoji' => '🌟', 'name' => 'ლამინე იამალი',      'desc' => '19 კითხვა ზედიზედ სწორად',                 'type' => 'sticker'],
    ];

    public function handleTestCompletion(Test $test, User $child): array
    {
        $setting = $child->childSetting;
        $total   = $test->total_questions;
        $correct = $test->correct_count ?? 0;
        $pct     = $total > 0 ? $correct / $total : 0;

        // 1. Coins — 1 coin per correct answer
        $coins = $correct;
        $test->update(['coins_earned' => $coins]);
        $setting->increment('coins', $coins);
        $setting->refresh();

        // 3. Achievements
        $newAchievements = $this->checkAchievements($child, $test, $setting, $pct);

        // 4. Level (difficulty) re-evaluation — every 7 completed tests
        $levelChange = $this->adjustLevelEvery7Tests($setting, $child);

        return [
            'coins'             => $coins,
            'total_coins'       => $setting->coins,
            'new_achievements'  => $newAchievements,
            'difficulty'        => $setting->difficulty,
            'level_change'      => $levelChange,
        ];
    }

    /**
     * Re-evaluates the child's level every 7 completed tests, based on their combined
     * correct-answer rate across those 7 tests: >85% moves up a level, <60% moves down,
     * 60–85% (inclusive) stays. Capped between level 1 and 3. The counter resets to 0
     * after each evaluation so this fires again after the next 7 tests, not on a rolling
     * window.
     */
    private function adjustLevelEvery7Tests(ChildSetting $setting, User $child): ?string
    {
        $setting->increment('tests_since_level_review');
        $setting->refresh();

        if ($setting->tests_since_level_review < 7) {
            return null;
        }

        $recentTests = Test::where('child_id', $child->id)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->take(7)
            ->get();

        $totalQuestions = $recentTests->sum('total_questions');
        $totalCorrect   = $recentTests->sum('correct_count');
        $pct            = $totalQuestions > 0 ? ($totalCorrect / $totalQuestions) * 100 : 0;

        $before = $setting->difficulty;
        $after  = $before;

        if ($pct > 85) {
            $after = min(3, $before + 1);
        } elseif ($pct < 60) {
            $after = max(1, $before - 1);
        }

        $setting->update([
            'difficulty'                => $after,
            'tests_since_level_review'  => 0,
        ]);

        if ($after > $before) return 'up';
        if ($after < $before) return 'down';
        return 'same';
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

        // Messi: 10 consecutive days with at least 1 test
        $dayStreak = 0;
        $testDays  = Test::where('child_id', $child->id)
            ->whereNotNull('completed_at')
            ->get()
            ->groupBy(fn($t) => $t->completed_at->toDateString())
            ->keys()
            ->flip()
            ->toArray();
        for ($d = 0; $d < 10; $d++) {
            if (isset($testDays[now()->subDays($d)->toDateString()])) {
                $dayStreak++;
            } else {
                break;
            }
        }

        // Yamal: 19 consecutive correct answers across recent 100% tests
        $consecCorrect = 0;
        foreach ($lastTests as $t) {
            if ($t->total_questions > 0 && $t->correct_count === $t->total_questions) {
                $consecCorrect += $t->correct_count;
            } else {
                break;
            }
        }

        // Ronaldo: test completed within 60 seconds
        $testSeconds = $test->created_at->diffInSeconds($test->completed_at);

        $candidates = [
            'first_test'    => $totalTests === 1,
            'first_perfect' => $pct >= 1.0,
            'comeback'      => $prevPct !== null && $prevPct <= 0.4 && $pct >= 0.8,
            'early_bird'    => now()->hour < 9,
            'night_owl'     => now()->hour > 22 || (now()->hour === 22 && now()->minute >= 30),
            'ronaldo'       => $testSeconds <= 60,
            'messi'         => $dayStreak >= 10,
            'yamal'         => $consecCorrect >= 19,
        ];

        $stickerSlugs = array_keys(array_filter(self::ACHIEVEMENTS, fn($a) => $a['type'] === 'sticker'));
        $stickerEarnedToday = ChildAchievement::where('child_id', $child->id)
            ->whereIn('slug', $stickerSlugs)
            ->whereDate('earned_at', today())
            ->exists();

        $new = [];
        $awardedStickerNow = false;

        foreach ($candidates as $slug => $met) {
            if (! $met || isset($earned[$slug])) continue;

            $isSticker = (self::ACHIEVEMENTS[$slug]['type'] ?? '') === 'sticker';

            if ($isSticker && ($stickerEarnedToday || $awardedStickerNow)) continue;

            ChildAchievement::create([
                'child_id'  => $child->id,
                'slug'      => $slug,
                'earned_at' => now(),
            ]);
            $new[] = array_merge(['slug' => $slug], self::ACHIEVEMENTS[$slug]);

            if ($isSticker) $awardedStickerNow = true;
        }

        return $new;
    }
}
