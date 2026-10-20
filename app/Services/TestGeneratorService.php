<?php

namespace App\Services;

use App\Models\ChildSetting;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestQuestionCount;
use App\Models\Theme;
use App\Models\Topic;
use App\Models\QuestionTemplate;
use App\Models\User;
use App\Exceptions\QuestionGenerationException;
use Illuminate\Support\Facades\Log;

class TestGeneratorService
{
    /**
     * @param bool $persist When false (admin "what would this child get right now"
     *     preview), nothing is written to the database — no Test/TestQuestion rows,
     *     and the child's topic/template rotation cursors are left untouched — the
     *     returned 'test'/'questions' are unsaved in-memory instances for display only.
     */
    public function generate(User $child, bool $persist = true): array
    {
        $setting = $child->childSetting;

        if (! $setting || ! $setting->grade_id) {
            return ['error' => 'მშობელმა ჯერ კლასი და დონე არ დააყენა'];
        }

        $childTopicIds = $child->topics()->pluck('id')->toArray();

        $baseQuery = fn () => QuestionTemplate::where('difficulty', $setting->difficulty)
            ->where('is_olympiad', false)
            ->where('is_active', true)
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

        // For each picked topic occurrence, draw one template from that topic's own pool —
        // cycling fairly across tests the same way topics themselves do (pickTemplateFromTopic()),
        // while still avoiding reusing the same template twice within this one test.
        $usedTemplateIds = [];
        $selectedTemplates = collect();

        foreach ($topicPicks as $topicId) {
            $topicTemplates = $templatesByTopic->get($topicId, collect());
            if ($topicTemplates->isEmpty()) continue;

            $picked = $this->pickTemplateFromTopic($setting, $topicId, $topicTemplates, $usedTemplateIds);
            if (! $picked) continue;

            $usedTemplateIds[] = $picked->id;
            $selectedTemplates->push($picked);
        }

        // Final question order follows each question's topic sequence (ascending,
        // admin-assigned priority) — topics with no sequence are shuffled among
        // themselves and appended after the sequenced ones, same policy as the
        // topic/template rotations above. `topic` is already eager-loaded above.
        $sequenced   = $selectedTemplates->filter(fn ($t) => $t->topic && $t->topic->sequence !== null)
            ->sortBy(fn ($t) => $t->topic->sequence)->values();
        $unsequenced = $selectedTemplates->filter(fn ($t) => ! $t->topic || $t->topic->sequence === null)
            ->shuffle()->values();
        $selectedTemplates = $sequenced->concat($unsequenced)->values();

        $testAttrs = [
            'child_id'        => $child->id,
            'theme_id'        => $theme?->id,
            'grade_id'        => $setting->grade_id,
            'scheduled_at'    => now(),
            'total_questions' => $selectedTemplates->count(),
        ];
        $test = $persist ? Test::create($testAttrs) : new Test($testAttrs);

        $order = 0;
        $questions = collect();
        foreach ($selectedTemplates as $template) {
            // A template whose formula never resolves (typo, stale variable
            // reference) must never reach the child as a question with a
            // meaningless "correct" answer — skip it and shrink the test instead.
            try {
                $generated = $template->generate($theme);
            } catch (QuestionGenerationException $e) {
                Log::error('TestGeneratorService: skipping broken template', ['template_id' => $template->id, 'error' => $e->getMessage()]);
                if ($persist) {
                    $test->decrement('total_questions');
                } else {
                    $test->total_questions--;
                }
                continue;
            }
            $order++;
            $tqAttrs = [
                'test_id'        => $persist ? $test->id : null,
                'template_id'    => $template->id,
                'question_type'  => $template->question_type ?? 'multiple_choice',
                'question_text'  => $generated['question_text'],
                'hint_text'      => $generated['hint_text'] ?? null,
                'options'        => $generated['options'],
                'correct_answer' => $generated['correct_answer'],
                'order'          => $order,
            ];
            if ($persist) {
                TestQuestion::create($tqAttrs);
            } else {
                $tq = new TestQuestion($tqAttrs);
                $tq->setRelation('template', $template);
                $questions->push($tq);
            }
        }

        if ($persist) {
            $setting->save(); // persist the topic_rotation/template_rotation state updated above
            return ['test' => $test];
        }

        return ['test' => $test, 'questions' => $questions];
    }

    /**
     * Builds a one-off Olympiad test from is_olympiad-tagged templates for the child's
     * grade. Unlike generate(), this does not persist a topic_rotation cursor — an
     * Olympiad happens at most once a year, so fairness across sittings isn't meaningful.
     * Selection is still fair within a single test: one template per topic first, then
     * extras drawn from the whole pool to reach $rule['questions_count'].
     */
    public function generateOlympiad(User $child, array $rule): array
    {
        $setting = $child->childSetting;

        if (! $setting || ! $setting->grade_id) {
            return ['error' => 'მშობელმა ჯერ კლასი და დონე არ დააყენა'];
        }

        $childTopicIds = $child->topics()->pluck('id')->toArray();

        $baseQuery = fn () => QuestionTemplate::where('is_olympiad', true)
            ->where('is_active', true)
            ->whereHas('topic', fn ($q) => $q->where('grade_id', $setting->grade_id))
            ->when(! empty($childTopicIds), fn ($q) => $q->whereIn('topic_id', $childTopicIds));

        $specialTemplates = $baseQuery()
            ->whereIn('question_type', ['pyramid', 'code', 'crossword'])
            ->with('topic')
            ->get();

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
            return ['error' => 'ამ კლასისთვის ოლიმპიადის კითხვები ჯერ არ დამატებულა'];
        }

        $questionsNeeded  = min($rule['questions_count'], $pool->count());
        $templatesByTopic = $pool->groupBy('topic_id');
        $usedTemplateIds  = [];
        $selectedTemplates = collect();

        // one per topic first, shuffled so no topic is favored
        foreach ($templatesByTopic->keys()->shuffle() as $topicId) {
            if ($selectedTemplates->count() >= $questionsNeeded) break;
            $candidates = $templatesByTopic->get($topicId)
                ->reject(fn ($t) => in_array($t->id, $usedTemplateIds, true));
            if ($candidates->isEmpty()) continue;
            $picked = $candidates->random();
            $usedTemplateIds[] = $picked->id;
            $selectedTemplates->push($picked);
        }

        // fill any remaining slots from the whole pool, avoiding reuse where possible
        while ($selectedTemplates->count() < $questionsNeeded) {
            $candidates = $pool->reject(fn ($t) => in_array($t->id, $usedTemplateIds, true));
            if ($candidates->isEmpty()) {
                $candidates = $pool; // reuse unavoidable — pool smaller than questionsNeeded
            }
            $picked = $candidates->random();
            $usedTemplateIds[] = $picked->id;
            $selectedTemplates->push($picked);
        }

        $selectedTemplates = $selectedTemplates->shuffle()->values();

        $test = Test::create([
            'child_id'        => $child->id,
            'theme_id'        => $theme?->id,
            'grade_id'        => $setting->grade_id,
            'scheduled_at'    => now(),
            'total_questions' => $selectedTemplates->count(),
            'is_olympiad'     => true,
        ]);

        $order = 0;
        foreach ($selectedTemplates as $template) {
            try {
                $generated = $template->generate($theme);
            } catch (QuestionGenerationException $e) {
                Log::error('TestGeneratorService: skipping broken template (olympiad)', ['template_id' => $template->id, 'error' => $e->getMessage()]);
                $test->decrement('total_questions');
                continue;
            }
            $order++;
            TestQuestion::create([
                'test_id'        => $test->id,
                'template_id'    => $template->id,
                'question_type'  => $template->question_type ?? 'multiple_choice',
                'question_text'  => $generated['question_text'],
                'hint_text'      => $generated['hint_text'] ?? null,
                'options'        => $generated['options'],
                'correct_answer' => $generated['correct_answer'],
                'order'          => $order,
            ]);
        }

        return ['test' => $test];
    }

    /**
     * Pick $count topic ids from $availableTopicIds for one test, cycling fairly across
     * tests (an order is persisted per child and consumed with a moving cursor) so no
     * topic repeats while any other still hasn't been seen. The order itself follows
     * each topic's admin-assigned `sequence` when set (ascending, ties broken by id) —
     * topics with no sequence are shuffled and appended after the sequenced ones
     * (buildTopicOrder()). If $count exceeds the number of available topics, every
     * topic is used at least once and the extra picks ("repeats") rotate too — the
     * topic that repeated in the previous test is pushed to the back of the repeat
     * queue so a different topic repeats next time.
     */
    private function pickTopicIdsForTest(ChildSetting $setting, array $availableTopicIds, int $count): array
    {
        $availableTopicIds = array_values(array_unique($availableTopicIds));
        $n = count($availableTopicIds);
        if ($n === 0) return [];

        $sequenceById = Topic::whereIn('id', $availableTopicIds)->pluck('sequence', 'id')->all();
        $signature = $availableTopicIds;
        sort($signature);
        $signature = array_map(fn ($id) => [$id, $sequenceById[$id] ?? null], $signature);

        $rotation     = $setting->topic_rotation ?? [];
        $order        = $rotation['order'] ?? [];
        $cursor       = (int) ($rotation['cursor'] ?? 0);
        $lastRepeated = $rotation['last_repeated_topic_id'] ?? null;

        // rebuild whenever the available topic set OR any sequence number changed —
        // not just on add/remove — so a newly-assigned sequence takes effect right away
        if (($rotation['signature'] ?? null) !== $signature) {
            $order  = $this->buildTopicOrder($availableTopicIds, $sequenceById);
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
            'signature'              => $signature,
        ];

        return $picked;
    }

    /**
     * Builds the topic rotation order: topics with an admin-assigned `sequence` come
     * first (ascending, ties broken by id — deterministic, not random), followed by
     * the remaining un-sequenced topics in a freshly shuffled order.
     */
    private function buildTopicOrder(array $availableTopicIds, array $sequenceById): array
    {
        $numbered = array_values(array_filter($availableTopicIds, fn ($id) => ($sequenceById[$id] ?? null) !== null));
        usort($numbered, fn ($a, $b) => ($sequenceById[$a] <=> $sequenceById[$b]) ?: ($a <=> $b));

        $unnumbered = array_values(array_diff($availableTopicIds, $numbered));
        shuffle($unnumbered);

        return array_merge($numbered, $unnumbered);
    }

    /**
     * Picks one template from $topicTemplates for a single topic slot, cycling fairly
     * across tests the same way pickTopicIdsForTest() cycles topics — an order + cursor
     * is persisted per child *per topic* (ChildSetting.template_rotation), so every
     * template in a topic is shown once before any of them repeats. Topic priority is
     * unaffected: this only decides which template fills a topic slot that
     * pickTopicIdsForTest() already chose.
     *
     * The order itself follows each template's admin-assigned `sequence` when set
     * (ascending, ties broken by id) — templates with no sequence are shuffled and
     * appended after the sequenced ones (buildTemplateOrder()). A signature of every
     * template's (id, sequence) pair is stored alongside the order so it's rebuilt
     * whenever the topic's pool OR any sequence number changes — not just on add/remove.
     *
     * The rotation always covers the topic's *whole* template pool, regardless of what's
     * already used elsewhere in this one test — $usedTemplateIds only skips ahead to the
     * next not-yet-used-this-test template without disturbing the persisted cursor logic,
     * falling back to the next-in-line template (reuse unavoidable) if the topic's entire
     * pool has already appeared in this test.
     */
    private function pickTemplateFromTopic(ChildSetting $setting, int $topicId, \Illuminate\Support\Collection $topicTemplates, array $usedTemplateIds): ?QuestionTemplate
    {
        $n = $topicTemplates->count();
        if ($n === 0) return null;

        $signature = $topicTemplates
            ->map(fn ($t) => [(int) $t->id, $t->sequence])
            ->sortBy(fn ($pair) => $pair[0])
            ->values()
            ->all();

        $rotationAll = $setting->template_rotation ?? [];
        $rotation    = $rotationAll[$topicId] ?? [];
        $order       = $rotation['order'] ?? [];
        $cursor      = (int) ($rotation['cursor'] ?? 0);

        // rebuild whenever this topic's template set OR any sequence number changed
        if (($rotation['signature'] ?? null) !== $signature) {
            $order  = $this->buildTemplateOrder($topicTemplates);
            $cursor = 0;
        }

        $pickedId = null;
        for ($i = 0; $i < $n; $i++) {
            $candidateId = $order[($cursor + $i) % $n];
            if (! in_array($candidateId, $usedTemplateIds, true)) {
                $pickedId = $candidateId;
                $cursor   = ($cursor + $i + 1) % $n;
                break;
            }
        }
        if ($pickedId === null) {
            // the topic's whole pool already appeared in this test — reuse unavoidable
            $pickedId = $order[$cursor % $n];
            $cursor   = ($cursor + 1) % $n;
        }

        $rotationAll[$topicId] = ['order' => $order, 'cursor' => $cursor, 'signature' => $signature];
        $setting->template_rotation = $rotationAll;

        return $topicTemplates->first(fn ($t) => (int) $t->id === $pickedId);
    }

    /**
     * Builds one topic's rotation order: templates with an admin-assigned `sequence`
     * come first (ascending, ties broken by id — deterministic, not random), followed
     * by the remaining un-sequenced templates in a freshly shuffled order.
     */
    private function buildTemplateOrder(\Illuminate\Support\Collection $topicTemplates): array
    {
        $numbered = $topicTemplates->whereNotNull('sequence')->values()->all();
        usort($numbered, fn ($a, $b) => ($a->sequence <=> $b->sequence) ?: ($a->id <=> $b->id));
        $numberedIds = array_map(fn ($t) => (int) $t->id, $numbered);

        $unnumberedIds = $topicTemplates->whereNull('sequence')->pluck('id')->map(fn ($id) => (int) $id)->all();
        shuffle($unnumberedIds);

        return array_merge($numberedIds, $unnumberedIds);
    }
}
