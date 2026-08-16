<?php

namespace App\Services;

use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\Theme;
use App\Models\QuestionTemplate;
use App\Models\User;

class TestGeneratorService
{
    public function generate(User $child): array
    {
        $setting = $child->childSetting;

        if (! $setting || ! $setting->grade_id) {
            return ['error' => 'მშობელმა ჯერ კლასი და დონე არ დააყენა'];
        }

        $topicIds = $child->topics()->pluck('id')->toArray();

        $baseQuery = fn() => QuestionTemplate::where('difficulty', $setting->difficulty)
            ->whereHas('topic', fn($q) => $q->where('grade_id', $setting->grade_id))
            ->when(! empty($topicIds), fn($q) => $q->whereIn('topic_id', $topicIds));

        // theme IDs that actually have templates for this child's settings
        $availableThemeIds = $baseQuery()->whereNotNull('theme_id')->pluck('theme_id')->unique();

        if ($availableThemeIds->isEmpty()) {
            return ['error' => 'ამ პარამეტრებისთვის კითხვები ჯერ არ დამატებულა'];
        }

        // prefer themes assigned to this child; fall back to any available theme
        $childThemeIds = $child->themes()->pluck('id');
        $candidateIds  = $availableThemeIds->intersect($childThemeIds);
        if ($candidateIds->isEmpty()) {
            $candidateIds = $availableThemeIds;
        }

        $themeId   = $candidateIds->random();
        $theme     = Theme::find($themeId);

        if (! $theme) {
            return ['error' => 'თემა ვერ მოიძებნა'];
        }

        $templates = $baseQuery()->where('theme_id', $themeId)->with('topic')->get();

        $pool = $templates->shuffle();

        $test = Test::create([
            'child_id'        => $child->id,
            'theme_id'        => $theme->id,
            'scheduled_at'    => now(),
            'total_questions' => $pool->count(),
        ]);

        foreach ($pool as $i => $template) {
            $generated = $template->generate($theme);
            TestQuestion::create([
                'test_id'        => $test->id,
                'template_id'    => $template->id,
                'question_text'  => $generated['question_text'],
                'hint_text'      => $generated['hint_text'] ?? null,
                'options'        => $generated['options'],
                'correct_answer' => $generated['correct_answer'],
                'order'          => $i + 1,
            ]);
        }

        return ['test' => $test];
    }
}
