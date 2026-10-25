<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\ChildAchievement;
use App\Models\ChildGradeHistory;
use App\Models\ChildSetting;
use App\Models\GameSession;
use App\Models\Grade;
use App\Models\LevelUpRule;
use App\Models\MarketPurchase;
use App\Models\PointRule;
use App\Models\PracticeAnswerLog;
use App\Models\Test;
use App\Models\Topic;
use App\Models\User;
use Carbon\Carbon;

class AchievementService
{
    /** Every game slug GameController currently exposes — see all_games_tried. */
    private const GAME_SLUGS = ['rock_paper_scissors', 'wolves_and_hare', 'memory', 'quoridor'];

    /**
     * Builds everything the achievements "wall" needs — used by both the child's own
     * page and the parent's read-only view of it, so the two never drift apart.
     * Achievements are scoped to the child's CURRENT grade; anything earned under a
     * grade they've since left is grouped separately under $oldGradeAchievements,
     * mirroring the "ძველი კლასები" pattern already used on the stats pages.
     * 'games' achievements are grade-INDEPENDENT (grade_id null — game skill isn't
     * curriculum-grade-specific), so they're merged into $earned alongside the
     * current grade's rows rather than reset on every grade change.
     */
    public function wallFor(User $child): array
    {
        $setting        = $child->childSetting;
        $currentGradeId = $setting?->grade_id;
        $themeId        = $this->resolveChildThemeId($child);

        $achievements = Achievement::with('tiers')->where('is_active', true)
            ->where(function ($q) use ($themeId) {
                $q->whereNull('theme_id');
                if ($themeId) $q->orWhere('theme_id', $themeId);
            })
            ->get();

        $earned = ChildAchievement::where('child_id', $child->id)
            ->where(function ($q) use ($currentGradeId) {
                $q->where('grade_id', $currentGradeId)->orWhereNull('grade_id');
            })
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

    /**
     * A child's assigned theme is a single-select despite the underlying many-to-many
     * pivot (the edit-child form only ever keeps one active) — this is what decides
     * which theme-exclusive achievements are visible/earnable for them right now.
     */
    private function resolveChildThemeId(User $child): ?int
    {
        return $child->themes()->value('themes.id');
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
        $context = $test->is_olympiad ? 'olympiad' : 'test';
        $metrics = $this->computeTestMetrics($child, $test, $setting, $pct);
        $newAchievements = $this->checkAchievements($child, $setting, $context, $metrics);

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
     * Called once per practice answer (PracticeController::answer()) — practice
     * never awards coins here (that already happens in the controller, same as
     * before) or touches difficulty; this only checks 'practice'/'any' achievements.
     */
    public function handlePracticeAnswer(User $child, bool $isCorrect): array
    {
        $setting = $child->childSetting;
        if (! $setting || ! $setting->grade_id) return [];

        $metrics = $this->computePracticeMetrics($child, $setting, $isCorrect);

        return $this->checkAchievements($child, $setting, 'practice', $metrics);
    }

    /**
     * Called once per finished game round (GameController) — win or lose, a tried
     * memory round, anything — all_games_tried/game_wins/game_win_streak are
     * recomputed straight from GameSession, which the caller has already updated
     * by this point, so there's no win/loss flag to pass in here. 'games'
     * achievements are grade-INDEPENDENT (see wallFor()'s docblock), so
     * $gradeScoped is false regardless of whether the child even has a grade set.
     */
    public function handleGameFinish(User $child, string $game): array
    {
        $setting = $child->childSetting;
        if (! $setting) return [];

        $metrics = $this->computeGameMetrics($child, $setting);

        return $this->checkAchievements($child, $setting, 'games', $metrics, $game, gradeScoped: false);
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
     * Evaluates every active Achievement in $context (plus every 'any'-context one,
     * which isn't tied to a single activity — e.g. day_streak/coins_earned/
     * difficulty_reached) against $metrics, and records any newly-reached tier.
     * No daily rate limit — a child can unlock as many achievements, in as many
     * categories, in one day as their thresholds allow.
     *
     * $gradeScoped=false (only 'games' today) records/looks up this $context's
     * OWN achievements with grade_id=null instead of the child's current grade
     * — game skill isn't reset by a grade change the way curriculum-tied
     * progress is. 'any'-context achievements (day_streak/coins_earned/
     * difficulty_reached) always track under the child's ACTUAL current grade
     * regardless of $gradeScoped, so the same progress is shared no matter
     * which activity (test, practice, or a grade-independent one like games)
     * happens to check it — otherwise a game trigger would fork off a second,
     * grade_id=null copy of day_streak's progress alongside the real one.
     * $game further restricts 'games' achievements to ones with a matching
     * (or null/"any game") `game` column.
     */
    private function checkAchievements(User $child, ChildSetting $setting, string $context, array $metrics, ?string $game = null, bool $gradeScoped = true): array
    {
        $contextGradeId = $gradeScoped ? $setting->grade_id : null;
        if ($gradeScoped && ! $contextGradeId) return [];
        $anyGradeId = $setting->grade_id;

        $earnedByGrade = ChildAchievement::where('child_id', $child->id)
            ->where(function ($q) use ($contextGradeId, $anyGradeId) {
                $q->where('grade_id', $contextGradeId)->orWhere('grade_id', $anyGradeId);
            })
            ->get()
            ->keyBy('slug');

        $themeId = $this->resolveChildThemeId($child);
        $achievementsQuery = Achievement::with('tiers')->where('is_active', true)
            ->whereIn('context', [$context, 'any'])
            ->where(function ($q) use ($themeId) {
                $q->whereNull('theme_id');
                if ($themeId) $q->orWhere('theme_id', $themeId);
            });
        if ($context === 'games') {
            $achievementsQuery->where(function ($q) use ($game) {
                $q->whereNull('game');
                if ($game) $q->orWhere('game', $game);
            });
        }
        $achievements = $achievementsQuery->get();

        $new = [];

        foreach ($achievements as $achievement) {
            $current     = $earnedByGrade->get($achievement->slug);
            $currentTier = $current->tier_level ?? 0;

            foreach ($achievement->tiers as $tier) {
                if ($tier->level <= $currentTier) continue;

                // tiers are ordered by ascending difficulty — if this one isn't met,
                // no higher tier will be either, so stop checking this achievement
                if (! $this->metricMeets($achievement->condition_type, $achievement->condition_config, $metrics, $tier->threshold)) {
                    break;
                }

                ChildAchievement::updateOrCreate(
                    ['child_id' => $child->id, 'slug' => $achievement->slug, 'grade_id' => $achievement->context === 'any' ? $anyGradeId : $contextGradeId],
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

                break; // one tier advance per achievement per trigger — no silent bronze→gold jumps
            }
        }

        return $new;
    }

    private function computeTestMetrics(User $child, Test $test, ChildSetting $setting, float $pct): array
    {
        $gradeId = $setting->grade_id;

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

        $speedSeconds = $test->is_olympiad ? null : $test->created_at->diffInSeconds($test->completed_at);

        $grade           = Grade::find($gradeId);
        $maxLevelReached = $grade && $setting->difficulty >= $grade->max_level;

        $olympiadCompleted = Test::where('child_id', $child->id)
            ->where('grade_id', $gradeId)
            ->where('is_olympiad', true)
            ->whereNotNull('completed_at')
            ->count();
        $olympiadPerfect = $test->is_olympiad && $pct >= 1.0;

        return [
            'test_count'          => $testCount,
            'perfect_score'       => ! $test->is_olympiad && $pct >= 1.0,
            'comeback'            => $comeback,
            'speed_seconds'       => $speedSeconds,
            'consecutive_correct' => $consecCorrect,
            'max_level_reached'   => $maxLevelReached,
            'olympiad_completed'  => $olympiadCompleted,
            'olympiad_perfect'    => $olympiadPerfect,
            'now'                 => now(),
        ] + $this->computeAnyContextMetrics($child, $setting);
    }

    /**
     * $isCorrect is the answer just logged by PracticeController (its
     * PracticeAnswerLog row already exists by the time this runs), so the
     * "before this answer" state for practice_comeback is everything from the
     * 2nd log onward in $recentLogs, not the 1st.
     */
    private function computePracticeMetrics(User $child, ChildSetting $setting, bool $isCorrect): array
    {
        $gradeId = $setting->grade_id;

        $baseQuery = fn () => PracticeAnswerLog::where('child_id', $child->id)
            ->whereHas('topic', fn ($q) => $q->where('grade_id', $gradeId));

        $practiceCount = $baseQuery()->count();

        $recentLogs = $baseQuery()->latest('id')->take(60)->get();

        $consecCorrect = 0;
        foreach ($recentLogs as $log) {
            if ($log->is_correct) { $consecCorrect++; } else { break; }
        }

        $comeback = false;
        if ($isCorrect) {
            $wrongRun = 0;
            foreach ($recentLogs->skip(1) as $log) {
                if (! $log->is_correct) { $wrongRun++; } else { break; }
            }
            $comeback = $wrongRun >= 3;
        }

        // Mirrors TestGeneratorService::generate()'s own topic pool: the child's
        // admin-assigned topics, falling back to every topic in the grade when
        // none are specifically assigned.
        $childTopicIds = $child->topics()->pluck('topics.id');
        if ($childTopicIds->isEmpty()) {
            $childTopicIds = Topic::where('grade_id', $gradeId)->pluck('id');
        }
        $answeredTopicIds = $baseQuery()->distinct()->pluck('topic_id');
        $fullRotation = $childTopicIds->isNotEmpty() && $childTopicIds->diff($answeredTopicIds)->isEmpty();

        return [
            'practice_count'               => $practiceCount,
            'practice_consecutive_correct' => $consecCorrect,
            'practice_comeback'            => $comeback,
            'practice_full_rotation'       => $fullRotation,
            'now'                          => now(),
        ] + $this->computeAnyContextMetrics($child, $setting);
    }

    private function computeGameMetrics(User $child, ChildSetting $setting): array
    {
        $sessions = GameSession::where('child_id', $child->id)->get();

        return [
            'game_wins'       => (int) $sessions->sum('wins'),
            'game_win_streak' => (int) $sessions->max('current_streak'),
            'all_games_tried' => $sessions->pluck('game')->unique()->intersect(self::GAME_SLUGS)->count() >= count(self::GAME_SLUGS),
            'now'             => now(),
        ] + $this->computeAnyContextMetrics($child, $setting);
    }

    /**
     * Metrics shared by every trigger (test/practice/game) so 'any'-context
     * achievements can always be evaluated, whichever activity just happened.
     * day_streak counts a day as "active" if it had a completed test OR at
     * least one practice answer (not games — see the brainstorm: a day streak
     * is about curriculum practice, not game-playing).
     */
    private function computeAnyContextMetrics(User $child, ChildSetting $setting): array
    {
        $gradeId = $setting->grade_id;

        $testDays = Test::where('child_id', $child->id)
            ->where('grade_id', $gradeId)
            ->where('is_olympiad', false)
            ->whereNotNull('completed_at')
            ->get()
            ->map(fn ($t) => $t->completed_at->toDateString());

        $practiceDays = PracticeAnswerLog::where('child_id', $child->id)
            ->whereHas('topic', fn ($q) => $q->where('grade_id', $gradeId))
            ->get()
            ->map(fn ($l) => $l->created_at->toDateString());

        // collect()->merge() (plain Collection), not Eloquent Collection::merge()
        // — the latter assumes every item is a model with a getKey(), which
        // breaks once .map() above has already turned these into date strings.
        $activeDays = collect($testDays->all())->merge($practiceDays->all())->unique()->flip();

        $dayStreak = 0;
        for ($d = 0; $d < 30; $d++) {
            if (isset($activeDays[now()->subDays($d)->toDateString()])) {
                $dayStreak++;
            } else {
                break;
            }
        }

        $grade = Grade::find($gradeId);

        return [
            'day_streak'         => $dayStreak,
            'coins_earned'       => $setting->coins,
            'difficulty_reached' => $setting->difficulty,
        ];
    }

    private function metricMeets(string $conditionType, ?array $config, array $metrics, ?int $threshold): bool
    {
        return match ($conditionType) {
            'test_count'                   => $metrics['test_count'] >= $threshold,
            'perfect_score'                => $metrics['perfect_score'],
            'comeback'                     => $metrics['comeback'],
            'speed_seconds'                => $metrics['speed_seconds'] !== null && $metrics['speed_seconds'] <= $threshold,
            'day_streak'                   => $metrics['day_streak'] >= $threshold,
            'consecutive_correct'          => $metrics['consecutive_correct'] >= $threshold,
            'max_level_reached'            => $metrics['max_level_reached'],
            'difficulty_reached'           => $metrics['difficulty_reached'] >= $threshold,
            'olympiad_completed'           => $metrics['olympiad_completed'] >= $threshold,
            'olympiad_perfect'             => $metrics['olympiad_perfect'],
            'coins_earned'                 => $metrics['coins_earned'] >= $threshold,
            'time_of_day'                  => $this->timeOfDayMet($config, $metrics['now']),
            'practice_count'               => $metrics['practice_count'] >= $threshold,
            'practice_consecutive_correct' => $metrics['practice_consecutive_correct'] >= $threshold,
            'practice_full_rotation'       => $metrics['practice_full_rotation'],
            'practice_comeback'            => $metrics['practice_comeback'],
            'game_wins'                    => $metrics['game_wins'] >= $threshold,
            'game_win_streak'              => $metrics['game_win_streak'] >= $threshold,
            'all_games_tried'              => $metrics['all_games_tried'],
            default                        => false,
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
