<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\OlympiadRule;
use App\Models\Test;
use App\Models\User;

class OlympiadService
{
    /**
     * Resolves whether the child can write the Olympiad today, and everything the
     * dashboard card / status page need to render: the resolved rule, why they're
     * not eligible (if not), a breakdown of each requirement (for a checklist UI),
     * and whether they've already started/finished today's attempt.
     *
     * @return array{eligible_today:bool, reason:?string, rule:array, already_attempted_today:bool, todays_test:?Test, level_met:bool, recent_count:int, tests_met:bool}
     */
    public function statusFor(User $child): array
    {
        $setting = $child->childSetting;
        $grade   = $setting?->grade_id ? Grade::find($setting->grade_id) : null;
        $rule    = OlympiadRule::resolve($setting?->grade_id);

        $todaysTest = Test::where('child_id', $child->id)
            ->where('is_olympiad', true)
            ->whereDate('created_at', today())
            ->latest()
            ->first();

        // difficulty is clamped to max_level by AchievementService::adjustLevelIfDue —
        // === is equivalent to >=, but states the fixed condition more clearly
        $levelMet = $setting && $grade && $setting->difficulty === $grade->max_level;

        $recentCount = Test::where('child_id', $child->id)
            ->where('is_olympiad', false)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subDays($rule['days_window']))
            ->count();
        $testsMet = $recentCount >= $rule['tests_required'];

        $base = [
            'rule'                    => $rule,
            'todays_test'             => $todaysTest,
            'already_attempted_today' => (bool) $todaysTest,
            'level_met'               => $levelMet,
            'recent_count'            => $recentCount,
            'tests_met'               => $testsMet,
        ];

        if (! $setting || ! $grade) {
            return $base + ['eligible_today' => false, 'reason' => 'მშობელმა ჯერ კლასი არ დააყენა'];
        }

        if (! $rule['olympiad_date'] || ! $rule['olympiad_date']->isToday()) {
            return $base + ['eligible_today' => false, 'reason' => 'ოლიმპიადა დღეს არ იმართება'];
        }

        if (! $levelMet) {
            return $base + ['eligible_today' => false, 'reason' => 'საჭიროა მაქსიმალური დონე შენს კლასში'];
        }

        if (! $testsMet) {
            return $base + ['eligible_today' => false,
                'reason' => "საჭიროა {$rule['tests_required']} ტესტი ბოლო {$rule['days_window']} დღეში"];
        }

        return $base + ['eligible_today' => true, 'reason' => null];
    }
}
