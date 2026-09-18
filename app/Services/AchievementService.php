<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\ChildAchievement;
use App\Models\ChildGradeHistory;
use App\Models\ChildSetting;
use App\Models\Grade;
use App\Models\LevelUpRule;
use App\Models\MarketPurchase;
use App\Models\PointRule;
use App\Models\Test;
use App\Models\User;
use Carbon\Carbon;

class AchievementService
{
    /**
     * Builds everything the achievements "wall" needs — used by both the child's own
     * page and the parent's read-only view of it, so the two never drift apart.
     * Achievements are scoped to the child's CURRENT grade; anything earned under a
     * grade they've since left is grouped separately under $oldGradeAchievements,
     * mirroring the "ძველი კლასები" pattern already used on the stats pages.
     */
    public function wallFor(User $child): array
    {
        $setting        = $child->childSetting;
        $currentGradeId = $setting?->grade_id;

        $achievements = Achievement::with('tiers')->where('is_active', true)->get();

        $earned = ChildAchievement::where('child_id', $child->id)
            ->where('grade_id', $currentGradeId)
            ->get()
            ->keyBy('slug');

        $totalTests = Test::where('child_id', $child->id)
            ->where('is_olympiad', false)
            ->when($currentGradeId, fn ($q) => $q->where('grade_id', $currentGradeId))
            ->whereNotNull('completed_at')
            ->count();

        $marketRewards = MarketPurchase::where('child_id', $child->id)
            ->where('status', 'approved')
            ->with('item')
            ->latest()
            ->get();

        $oldGradeIds = collect()
            ->merge(ChildAchievement::where('child_id', $child->id)->whereNotNull('grade_id')->pluck('grade_id'))
            ->merge(ChildGradeHistory::where('user_id', $child->id)->pluck('grade_id'))
            ->filter()
            ->unique()
            ->reject(fn ($id) => $currentGradeId && $id == $currentGradeId);

        $oldGrades = Grade::whereIn('id', $oldGradeIds)->orderBy('number')->get();

        $oldGradeAchievements = ChildAchievement::where('child_id', $child->id)
            ->whereIn('grade_id', $oldGrades->pluck('id'))
            ->get()
            ->groupBy('grade_id')
            ->map(fn ($rows) => $rows->keyBy('slug'));

        return compact('setting', 'achievements', 'earned', 'totalTests', 'marketRewards', 'oldGrades', 'oldGradeAchievements');
    }

    public function handleTestCompletion(Test $test, User $child): array
    {
        $setting = $child->childSetting;
        $total   = $test->total_questions;
        $correct = $test->correct_count ?? 0;
        $pct     = $total > 0 ? $correct / $total : 0;

        // 1. Coins — points-per-correct is admin-configurable per grade/difficulty/context
        $pointsPerCorrect = PointRule::resolve($setting->grade_id, $setting->difficulty, $test->is_olympiad ? 'olympiad' : 'test');
        $coins = $correct * $pointsPerCorrect;
        $test->update(['coins_earned' => $coins]);
        $setting->increment('coins', $coins);
        $setting->refresh();

        // 3. Achievements
        $newAchievements = $this->checkAchievements($child, $test, $setting, $pct);

        // 4. Level (difficulty) re-evaluation — every N completed tests (per-grade configurable).
        // Olympiad attempts never feed into or reset this counter: eligibility already
        // requires max level, and a bad Olympiad score must not demote the child.
        $levelChange = $test->is_olympiad ? null : $this->adjustLevelIfDue($setting, $child);

        return [
            'coins'             => $coins,
            'total_coins'       => $setting->coins,
            'new_achievements'  => $newAchievements,
            'difficulty'        => $setting->difficulty,
            'level_change'      => $levelChange,
        ];
    }

    /**
     * Re-evaluates the child's level once at least N completed tests have accumulated
     * since the last level change, based on the combined correct-answer rate across
     * the N most recent tests. N and the up/down % thresholds are configurable per
     * grade by an admin (see LevelUpRule); a grade with no rule set falls back to the
     * defaults (7 tests, >85% up, <60% down, 60–85% inclusive stays). Capped between
     * level 1 and that grade's max_level (see Grade). Once warmed up (counter >= N), this checks the trailing N-test
     * window on every subsequent test — not just every Nth one — so a level change can
     * land as soon as the window qualifies. The counter only resets to 0 when the
     * level actually changes; while it stays the same, counting is not restarted.
     */
    private function adjustLevelIfDue(ChildSetting $setting, User $child): ?string
    {
        $rule = LevelUpRule::resolve($setting->grade_id);

        $setting->increment('tests_since_level_review');
        $setting->refresh();

        if ($setting->tests_since_level_review < $rule['tests_required']) {
            return null;
        }

        $recentTests = Test::where('child_id', $child->id)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->take($rule['tests_required'])
            ->get();

        $totalQuestions = $recentTests->sum('total_questions');
        $totalCorrect   = $recentTests->sum('correct_count');
        $pct            = $totalQuestions > 0 ? ($totalCorrect / $totalQuestions) * 100 : 0;

        $maxLevel = Grade::find($setting->grade_id)->max_level ?? Grade::DEFAULT_MAX_LEVEL;

        $before = $setting->difficulty;
        $after  = $before;

        if ($pct > $rule['up_threshold']) {
            $after = min($maxLevel, $before + 1);
        } elseif ($pct < $rule['down_threshold']) {
            $after = max(1, $before - 1);
        }

        if ($after === $before) {
            return null;
        }

        $setting->update([
            'difficulty'                => $after,
            'tests_since_level_review'  => 0,
        ]);

        if ($after > $before) return 'up';
        if ($after < $before) return 'down';
        return 'same';
    }

    /**
     * Evaluates every active Achievement's tiers against the child's current-grade
     * metrics and records any newly-reached tier. Achievements (and their metrics)
     * are scoped to the child's CURRENT grade — this is what makes progress restart
     * cleanly after a grade change (ChildSettingsController::update()) with no
     * explicit reset step: a new grade simply has no Test rows yet to count.
     * `coins_earned` is the one metric that is NOT grade-scoped, since coins are a
     * single wallet shared across grades.
     */
    private function checkAchievements(User $child, Test $test, ChildSetting $setting, float $pct): array
    {
        $gradeId = $setting->grade_id;
        if (! $gradeId) return [];

        $metrics = $this->computeMetrics($child, $test, $gradeId, $pct, $setting);

        $earnedByGrade = ChildAchievement::where('child_id', $child->id)
            ->where('grade_id', $gradeId)
            ->get()
            ->keyBy('slug');

        $achievements = Achievement::with('tiers')->where('is_active', true)->get();

        // "1 new achievement per day" only gates the FIRST-ever unlock of a daily-limited
        // achievement — tier_level=1 is the only tier a fresh unlock can grant, so filtering
        // on it (rather than just "touched today") keeps later tier upgrades from being
        // miscounted as a first unlock. Continued progress on something already started
        // is never blocked by this gate, only brand-new surprises are rationed.
        $dailyLimitSlugs = $achievements->where('daily_limit', true)->pluck('slug');
        $dailyLimitUsedToday = ChildAchievement::where('child_id', $child->id)
            ->where('grade_id', $gradeId)
            ->whereIn('slug', $dailyLimitSlugs)
            ->where('tier_level', 1)
            ->whereDate('earned_at', today())
            ->exists();

        $new = [];
        $awardedDailyLimitNow = false;

        foreach ($achievements as $achievement) {
            $current        = $earnedByGrade->get($achievement->slug);
            $currentTier    = $current->tier_level ?? 0;
            $isFirstEverNow = $currentTier === 0;

            if ($isFirstEverNow && $achievement->daily_limit && ($dailyLimitUsedToday || $awardedDailyLimitNow)) continue;

            foreach ($achievement->tiers as $tier) {
                if ($tier->level <= $currentTier) continue;

                // tiers are ordered by ascending difficulty — if this one isn't met,
                // no higher tier will be either, so stop checking this achievement
                if (! $this->metricMeets($achievement->condition_type, $achievement->condition_config, $metrics, $tier->threshold)) {
                    break;
                }

                ChildAchievement::updateOrCreate(
                    ['child_id' => $child->id, 'slug' => $achievement->slug, 'grade_id' => $gradeId],
                    ['tier_level' => $tier->level, 'earned_at' => now()]
                );

                $new[] = [
                    'slug'  => $achievement->slug,
                    'name'  => $tier->label,
                    'desc'  => $tier->threshold !== null
                        ? str_replace('{n}', (string) $tier->threshold, $achievement->description ?? '')
                        : ($achievement->description ?? ''),
                    'image' => $tier->imageUrl(),
                    'tier'  => $tier->level,
                ];

                if ($isFirstEverNow && $achievement->daily_limit) $awardedDailyLimitNow = true;

                break; // one tier advance per achievement per test — no silent bronze→gold jumps
            }
        }

        return $new;
    }

    private function computeMetrics(User $child, Test $test, int $gradeId, float $pct, ChildSetting $setting): array
    {
        $baseQuery = fn () => Test::where('child_id', $child->id)
            ->where('grade_id', $gradeId)
            ->where('is_olympiad', false)
            ->whereNotNull('completed_at');

        $testCount = $baseQuery()->count();

        $lastTests = $baseQuery()->latest('completed_at')->take(7)->get();

        $consecCorrect = 0;
        foreach ($lastTests as $t) {
            if ($t->total_questions > 0 && $t->correct_count === $t->total_questions) {
                $consecCorrect += $t->correct_count;
            } else {
                break;
            }
        }

        $prevTest = $baseQuery()->where('id', '!=', $test->id)->latest('completed_at')->first();
        $prevPct  = $prevTest && $prevTest->total_questions > 0
            ? $prevTest->correct_count / $prevTest->total_questions
            : null;
        $comeback = ! $test->is_olympiad && $prevPct !== null && $prevPct <= 0.4 && $pct >= 0.8;

        $testDays = $baseQuery()->get()
            ->groupBy(fn ($t) => $t->completed_at->toDateString())
            ->keys()->flip()->toArray();
        $dayStreak = 0;
        for ($d = 0; $d < 30; $d++) {
            if (isset($testDays[now()->subDays($d)->toDateString()])) {
                $dayStreak++;
            } else {
                break;
            }
        }

        $speedSeconds = $test->is_olympiad ? null : $test->created_at->diffInSeconds($test->completed_at);

        $grade           = Grade::find($gradeId);
        $maxLevelReached = $grade && $setting->difficulty >= $grade->max_level;

        $olympiadCompleted = Test::where('child_id', $child->id)
            ->where('grade_id', $gradeId)
            ->where('is_olympiad', true)
            ->whereNotNull('completed_at')
            ->count();

        return [
            'test_count'          => $testCount,
            'perfect_score'       => ! $test->is_olympiad && $pct >= 1.0,
            'comeback'            => $comeback,
            'speed_seconds'       => $speedSeconds,
            'day_streak'          => $dayStreak,
            'consecutive_correct' => $consecCorrect,
            'max_level_reached'   => $maxLevelReached,
            'olympiad_completed'  => $olympiadCompleted,
            'coins_earned'        => $setting->coins,
            'now'                 => now(),
        ];
    }

    private function metricMeets(string $conditionType, ?array $config, array $metrics, ?int $threshold): bool
    {
        return match ($conditionType) {
            'test_count'          => $metrics['test_count'] >= $threshold,
            'perfect_score'       => $metrics['perfect_score'],
            'comeback'            => $metrics['comeback'],
            'speed_seconds'       => $metrics['speed_seconds'] !== null && $metrics['speed_seconds'] <= $threshold,
            'day_streak'          => $metrics['day_streak'] >= $threshold,
            'consecutive_correct' => $metrics['consecutive_correct'] >= $threshold,
            'max_level_reached'   => $metrics['max_level_reached'],
            'olympiad_completed'  => $metrics['olympiad_completed'] >= $threshold,
            'coins_earned'        => $metrics['coins_earned'] >= $threshold,
            'time_of_day'         => $this->timeOfDayMet($config, $metrics['now']),
            default               => false,
        };
    }

    private function timeOfDayMet(?array $config, Carbon $now): bool
    {
        if (isset($config['before'])) {
            return $now->format('H:i') < $config['before'];
        }
        if (isset($config['after'])) {
            return $now->format('H:i') >= $config['after'];
        }
        return false;
    }
}
