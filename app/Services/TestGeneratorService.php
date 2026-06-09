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

        $templates = QuestionTemplate::where('difficulty', $setting->difficulty)
            ->whereHas('topic', fn($q) => $q->where('grade_id', $setting->grade_id))
            ->when(! empty($topicIds), fn($q) => $q->whereIn('topic_id', $topicIds))
            ->with('topic')
            ->get();

        if ($templates->isEmpty()) {
            return ['error' => 'ამ პარამეტრებისთვის კითხვები ჯერ არ დამატებულა'];
        }

        $themes = $child->themes()->with('variables')->get();
        if ($themes->isEmpty()) {
            $themes = Theme::with('variables')->get();
        }
        if ($themes->isEmpty()) {
            return ['error' => 'თემა არ არის — ადმინმა უნდა დაამატოს'];
        }

        $theme = $themes->filter(fn($t) => $t->variables->isNotEmpty())->random()
            ?? $themes->random();

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
                'options'        => $generated['options'],
                'correct_answer' => $generated['correct_answer'],
                'order'          => $i + 1,
            ]);
        }

        return ['test' => $test];
    }
}
