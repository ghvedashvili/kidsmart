<?php

namespace App\Services;

use App\Models\ChildSetting;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestQuestionCount;
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

        $childTopicIds = $child->topics()->pluck('id')->toArray();

        $baseQuery = fn () => QuestionTemplate::where('difficulty', $setting->difficulty)
            ->whereHas('topic', fn ($q) => $q->where('grade_id', $setting->grade_id))
            ->when(! empty($childTopicIds), fn ($q) => $q->whereIn('topic_id', $childTopicIds));

        // Pyramid, code, and crossword templates are theme-independent — always included
        $specialTemplates = $baseQuery()
            ->whereIn('question_type', ['pyramid', 'code', 'crossword'])
            ->with('topic')
            ->get();

        // MC templates require a theme
        $availableThemeIds = $baseQuery()->whereNotNull('theme_id')->pluck('theme_id')->unique();

        $theme       = null;
        $mcTemplates = collect();

        if ($availableThemeIds->isNotEmpty()) {
            $childThemeIds = $child->themes()->pluck('id');
            $candidateIds  = $availableThemeIds->intersect($childThemeIds);
            if ($candidateIds->isEmpty()) {
                $candidateIds = $availableThemeIds;
            }
            $themeId = $candidateIds->random();
            $theme   = Theme::find($themeId);
            if ($theme) {
                $mcTemplates = $baseQuery()->where('theme_id', $themeId)->with('topic')->get();
            }
        }

        $pool = $mcTemplates->merge($specialTemplates);

        if ($pool->isEmpty()) {
            return ['error' => 'ამ პარამეტრებისთვის კითხვები ჯერ არ დამატებულა'];
        }

        // Group available templates by topic, so we can pick "one per topic" fairly
        $templatesByTopic = $pool->groupBy('topic_id');
        $availableTopicIds = $templatesByTopic->keys()->all();

        $questionsNeeded = TestQuestionCount::resolve($setting->grade_id, $setting->difficulty, $theme?->id);

        $topicPicks = $this->pickTopicIdsForTest($setting, $availableTopicIds, $questionsNeeded);

        // For each picked topic occurrence, draw one template from that topic's own pool,
        // avoiding reusing the same template twice within this test where possible.
        $usedTemplateIds = [];
        $selectedTemplates = collect();

        foreach ($topicPicks as $topicId) {
            $candidates = $templatesByTopic->get($topicId, collect())
                ->reject(fn ($t) => in_array($t->id, $usedTemplateIds, true));

            if ($candidates->isEmpty()) {
                // topic's pool exhausted within this test — reuse is unavoidable
                $candidates = $templatesByTopic->get($topicId, collect());
            }
            if ($candidates->isEmpty()) continue;

            $picked = $candidates->random();
            $usedTemplateIds[] = $picked->id;
            $selectedTemplates->push($picked);
        }

        $selectedTemplates = $selectedTemplates->shuffle()->values();

        $test = Test::create([
            'child_id'        => $child->id,
            'theme_id'        => $theme?->id,
            'scheduled_at'    => now(),
            'total_questions' => $selectedTemplates->count(),
        ]);

        foreach ($selectedTemplates as $i => $template) {
            $generated = $template->generate($theme);
            TestQuestion::create([
                'test_id'        => $test->id,
                'template_id'    => $template->id,
                'question_type'  => $template->question_type ?? 'multiple_choice',
                'question_text'  => $generated['question_text'],
                'hint_text'      => $generated['hint_text'] ?? null,
                'options'        => $generated['options'],
                'correct_answer' => $generated['correct_answer'],
                'order'          => $i + 1,
            ]);
        }

        $setting->save(); // persist the topic_rotation state updated by pickTopicIdsForTest()

        return ['test' => $test];
    }

    /**
     * Pick $count topic ids from $availableTopicIds for one test, cycling fairly across
     * tests (a shuffled order is persisted per child and consumed with a moving cursor).
     * If $count exceeds the number of available topics, every topic is used at least
     * once and the extra picks ("repeats") rotate too — the topic that repeated in the
     * previous test is pushed to the back of the repeat queue so a different topic
     * repeats next time.
     */
    private function pickTopicIdsForTest(ChildSetting $setting, array $availableTopicIds, int $count): array
    {
        $availableTopicIds = array_values(array_unique($availableTopicIds));
        $n = count($availableTopicIds);
        if ($n === 0) return [];

        $rotation     = $setting->topic_rotation ?? [];
        $order        = $rotation['order'] ?? [];
        $cursor       = (int) ($rotation['cursor'] ?? 0);
        $lastRepeated = $rotation['last_repeated_topic_id'] ?? null;

        $sortedStored    = $order;
        sort($sortedStored);
        $sortedAvailable = $availableTopicIds;
        sort($sortedAvailable);

        // reshuffle whenever the available topic set has changed (new/removed topics,
        // grade change, admin edits, etc.) so the rotation always covers exactly what's there
        if ($sortedStored !== $sortedAvailable) {
            $order = $availableTopicIds;
            shuffle($order);
            $cursor = 0;
        }

        $picked = [];

        if ($count <= $n) {
            for ($i = 0; $i < $count; $i++) {
                $picked[] = $order[($cursor + $i) % $n];
            }
            $cursor = ($cursor + $count) % $n;
        } else {
            // everyone gets one first, then hand out the extras fairly
            $picked = $order;
            $extra  = $count - $n;

            $repeatOrder = $order;
            if ($lastRepeated !== null) {
                $idx = array_search($lastRepeated, $repeatOrder, true);
                if ($idx !== false) {
                    // rotate so the topic that repeated last time is picked last, not first
                    $repeatOrder = array_merge(
                        array_slice($repeatOrder, $idx + 1),
                        array_slice($repeatOrder, 0, $idx + 1)
                    );
                }
            }
            for ($i = 0; $i < $extra; $i++) {
                $picked[]     = $repeatOrder[$i % $n];
                $lastRepeated = $repeatOrder[$i % $n];
            }
            $cursor = 0;
        }

        $setting->topic_rotation = [
            'order'                  => $order,
            'cursor'                 => $cursor,
            'last_repeated_topic_id' => $lastRepeated,
        ];

        return $picked;
    }
}
