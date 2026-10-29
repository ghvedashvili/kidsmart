<?php

namespace App\Http\Controllers;

use App\Models\ChildSetting;
use App\Models\PointRule;
use App\Models\PracticeAnswerLog;
use App\Models\QuestionTemplate;
use App\Models\Theme;
use App\Models\Topic;
use App\Services\TestGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    private const QUEUE_TTL_HOURS    = 6;
    private const ANSWER_TTL_MINUTES = 20;

    // ── Practice hub (topics screen was replaced by a single auto-rotating entry) ──
    public function topics()
    {
        $child   = auth()->user();
        $gradeId = $child->childSetting?->grade_id;

        $videoTopics = Topic::where('grade_id', $gradeId)
            ->with(['videos' => fn($q) => $q->orderBy('order')])
            ->whereHas('videos')
            ->orderBy('name')
            ->get();

        return view('child.practice-topics', compact('videoTopics'));
    }

    // ── Practice shell page ─────────────────────────────────────────────────
    public function show(string $slug)
    {
        $child = auth()->user();

        if ($slug === 'pyramid') {
            return view('child.practice', ['type' => 'pyramid', 'topic' => null, 'slug' => 'pyramid']);
        }

        if ($slug === 'auto') {
            return view('child.practice', ['type' => 'auto', 'topic' => null, 'slug' => 'auto']);
        }

        $topic = Topic::findOrFail((int) $slug);
        abort_if($topic->grade_id !== $child->childSetting?->grade_id, 403);
        abort_if(! $topic->is_active, 404);

        return view('child.practice', ['type' => 'topic', 'topic' => $topic, 'slug' => $slug]);
    }

    // ── AJAX: next question ──────────────────────────────────────────────────
    public function question(string $slug): JsonResponse
    {
        $child   = auth()->user();
        $setting = $child->childSetting;

        if ($slug === 'pyramid') {
            if (! $setting) {
                return response()->json(['error' => 'მშობელმა ჯერ კლასი და დონე არ დააყენა'], 404);
            }
            $config = self::pyramidConfig($setting->difficulty);
            $key    = "pq_{$child->id}_pyramid_" . uniqid();
            return response()->json($this->buildPyramid($config, $key, '🔺 პირამიდა'));
        }

        if ($slug === 'auto') {
            return $this->questionFromQueue($child);
        }

        $topic = Topic::findOrFail((int) $slug);
        abort_if($topic->grade_id !== $setting?->grade_id, 403);
        abort_if(! $topic->is_active, 404);
        return $this->questionFromTopic($setting, $topic);
    }

    /**
     * "auto" practice — serves questions one at a time from a pre-built,
     * test-like ordered batch (TestGeneratorService::generatePracticeBatch(),
     * which picks topics/templates with the exact same difficulty/fairness/
     * sequence rules a real test uses, against its OWN rotation columns so it
     * never disturbs the real test's progress). A fresh batch is built once
     * the cached one is exhausted.
     */
    private function questionFromQueue($child): JsonResponse
    {
        $queueKey = "practice_queue_{$child->id}";
        $queue    = cache()->get($queueKey);

        if (! $queue || ! isset($queue['items'][$queue['cursor']])) {
            $result = (new TestGeneratorService())->generatePracticeBatch($child);
            if (isset($result['error']) || empty($result['items'])) {
                return response()->json(['error' => $result['error'] ?? 'კითხვები ვერ მოიძებნა'], 404);
            }
            $items = array_map(fn ($it) => [
                'question_type' => $it['template']->question_type ?? 'multiple_choice',
                'topic_id'      => $it['template']->topic_id,
                'topic_name'    => $it['template']->topic?->name ?? '',
                'generated'     => $it['generated'],
            ], $result['items']);
            $queue = ['items' => $items, 'cursor' => 0];
            cache()->put($queueKey, $queue, now()->addHours(self::QUEUE_TTL_HOURS));
        }

        $item = $queue['items'][$queue['cursor']];
        return response()->json($this->buildQuestionResponse($child->id, $item, $queueKey));
    }

    /**
     * Topic-focused practice — one question at a time from a single topic,
     * cycling fairly through that topic's whole template pool
     * (TestGeneratorService::pickTemplateFromTopic(), same fairness real
     * tests use) against its own rotation column, instead of the old pure
     * `inRandomOrder()` pick.
     */
    private function questionFromTopic(?ChildSetting $setting, Topic $topic): JsonResponse
    {
        if (! $setting) {
            return response()->json(['error' => 'მშობელმა ჯერ კლასი და დონე არ დააყენა'], 404);
        }

        $service = new TestGeneratorService();

        $topicTemplates = QuestionTemplate::where('topic_id', $topic->id)
            ->where('difficulty', $setting->difficulty)
            ->where('is_olympiad', false)
            ->where('is_active', true)
            ->whereNotIn('question_type', ['scale'])
            ->with('theme')
            ->get();

        if ($topicTemplates->isEmpty()) {
            $topicTemplates = QuestionTemplate::where('topic_id', $topic->id)
                ->where('is_olympiad', false)
                ->where('is_active', true)
                ->whereNotIn('question_type', ['scale'])
                ->with('theme')
                ->get();
        }

        $template = $service->pickTemplateFromTopic($setting, $topic->id, $topicTemplates, [], 'practice_template_rotation');
        if (! $template) {
            return response()->json(['error' => 'კითხვები ვერ მოიძებნა'], 404);
        }
        $setting->save(); // persist the practice_template_rotation cursor pickTemplateFromTopic() updated

        $theme = $template->theme ?? Theme::first();
        try {
            $generated = $template->generate($theme);
        } catch (\App\Exceptions\QuestionGenerationException $e) {
            \Illuminate\Support\Facades\Log::error('PracticeController: broken template', ['template_id' => $template->id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'კითხვა ვერ მოიძებნა — სცადე თავიდან'], 404);
        }

        $item = [
            'question_type' => $template->question_type ?? 'multiple_choice',
            'topic_id'       => $topic->id,
            'topic_name'     => $topic->name,
            'generated'      => $generated,
        ];
        return response()->json($this->buildQuestionResponse($setting->user_id, $item, null));
    }

    /**
     * Builds the per-type JSON the child-facing JS renders (pyramid/code/
     * crossword/mc/input — mirrors generateScale()'s siblings' shapes) from
     * an already-generated item, and caches its answer key. $advanceQueueKey,
     * when given, is read back in answer() to move that queue's cursor to
     * the next item once this one is graded.
     */
    private function buildQuestionResponse(int $childId, array $item, ?string $advanceQueueKey): array
    {
        $generated = $item['generated'];
        $qtype     = $item['question_type'];
        $topicName = $item['topic_name'];
        $meta      = ['topic_id' => $item['topic_id'], 'topic_name' => $topicName];
        $key       = "pq_{$childId}_{$item['topic_id']}_" . uniqid();

        if ($qtype === 'pyramid') {
            $rows      = json_decode($generated['question_text'], true) ?? [];
            $solutions = json_decode($generated['correct_answer'], true) ?? [];
            cache()->put($key, ['topic_id' => $item['topic_id'], 'qtype' => 'pyramid', 'data' => $solutions, 'prompt' => "🔺 პირამიდა — {$topicName}", 'advance_queue' => $advanceQueueKey], now()->addMinutes(self::ANSWER_TTL_MINUTES));
            return array_merge(['type' => 'pyramid', 'key' => $key, 'rows' => $rows, 'height' => count($rows), 'hint' => $generated['hint_text'] ?? null], $meta);
        }

        if ($qtype === 'code') {
            $q          = json_decode($generated['question_text'], true) ?? [];
            $correctArr = json_decode($generated['correct_answer'], true) ?? [];
            cache()->put($key, ['topic_id' => $item['topic_id'], 'qtype' => 'code', 'data' => $correctArr, 'prompt' => '🔢 კოდური ამოცანა — მიზანი: ' . ($q['target'] ?? ''), 'advance_queue' => $advanceQueueKey], now()->addMinutes(self::ANSWER_TTL_MINUTES));
            return array_merge([
                'type'      => 'code',
                'key'       => $key,
                'symbols'   => $q['symbols'] ?? [],
                'equations' => $q['equations'] ?? [],
                'target'    => $q['target'] ?? [],
                'hint'      => $generated['hint_text'] ?? null,
            ], $meta);
        }

        if ($qtype === 'crossword') {
            $q          = json_decode($generated['question_text'], true) ?? [];
            $correctArr = json_decode($generated['correct_answer'], true) ?? [];
            cache()->put($key, ['topic_id' => $item['topic_id'], 'qtype' => 'crossword', 'data' => $correctArr, 'prompt' => "🧩 კროსვორდი — {$topicName}", 'advance_queue' => $advanceQueueKey], now()->addMinutes(self::ANSWER_TTL_MINUTES));
            $revealed     = $q['revealed'] ?? [];
            $revealedVals = [];
            foreach ($revealed as $pos) {
                $revealedVals[(string) $pos] = $correctArr[(string) $pos] ?? null;
            }
            return array_merge([
                'type'            => 'crossword',
                'key'             => $key,
                'rows'            => $q['rows'] ?? 2,
                'cols'            => $q['cols'] ?? 2,
                'row_ops'         => $q['row_ops'] ?? ['+', '+'],
                'col_ops'         => $q['col_ops'] ?? ['+', '+'],
                'row_results'     => $q['row_results'] ?? [],
                'col_results'     => $q['col_results'] ?? [],
                'revealed'        => $revealed,
                'revealed_values' => $revealedVals,
                'hint'            => $generated['hint_text'] ?? null,
            ], $meta);
        }

        // "numeric_input" (⌨️ child types the number) has no options — same cached
        // shape as "mc" otherwise, just a different `type` so the frontend renders
        // a free-number field instead of option buttons.
        $isInput = empty($generated['options']);
        cache()->put($key, ['topic_id' => $item['topic_id'], 'qtype' => $isInput ? 'input' : 'mc', 'data' => ['type' => $isInput ? 'input' : 'mc', 'correct' => $generated['correct_answer']], 'prompt' => $generated['question_text'], 'advance_queue' => $advanceQueueKey], now()->addMinutes(self::ANSWER_TTL_MINUTES));
        return array_merge([
            'type'     => $isInput ? 'input' : 'mc',
            'key'      => $key,
            'question' => $generated['question_text'],
            'options'  => $generated['options'],
            'hint'     => $generated['hint_text'] ?? null,
        ], $meta);
    }

    // ── AJAX: submit answer ──────────────────────────────────────────────────
    public function answer(Request $request, string $slug): JsonResponse
    {
        $child  = auth()->user();
        $key    = $request->input('key');
        $cached = cache()->pull($key);

        if (! $cached) {
            return response()->json(['error' => 'კითხვის ვადა გავიდა'], 422);
        }

        $topicId = $cached['topic_id'] ?? null;
        $payload = $cached['data'];
        $qtype   = $cached['qtype'] ?? ($payload['type'] ?? 'mc');
        $prompt  = $cached['prompt'] ?? '';

        $isCorrect = false;
        $feedback  = null;
        $given     = null;
        $correct   = null;

        if (isset($payload['type']) && $payload['type'] === 'mc') {
            $isCorrect = (string) $request->input('answer') === (string) $payload['correct'];
            $feedback  = ['correct_answer' => $payload['correct']];
            $given     = (string) $request->input('answer');
            $correct   = (string) $payload['correct'];
        } elseif (isset($payload['type']) && $payload['type'] === 'input') {
            // Numeric-aware compare (trimmed, leading zeros / "8.0" vs "8" both match)
            // since the child typed this in freely rather than picking a rendered option.
            $given     = trim((string) $request->input('answer'));
            $correct   = (string) $payload['correct'];
            $isCorrect = $given !== '' && is_numeric($given) && is_numeric($correct)
                ? (float) $given === (float) $correct
                : $given === $correct;
            $feedback  = ['correct_answer' => $correct];
        } elseif ($request->has('code_answers')) {
            // code: payload = [pos => value]
            $codeAnswers = $request->input('code_answers', []);
            $result    = \App\Services\CodeService::check(json_encode($payload), $codeAnswers);
            $isCorrect = $result['ok'];
            $feedback  = ['results' => $result['results']];
            $given     = implode(', ', $codeAnswers);
            $correct   = implode(', ', $payload);
        } elseif ($request->has('crossword_answers')) {
            // crossword: payload = ['0'=>a, '1'=>b, '2'=>c, '3'=>d]
            $crosswordAnswers = $request->input('crossword_answers', []);
            $result    = \App\Services\CrosswordService::check(json_encode($payload), $crosswordAnswers);
            $isCorrect = $result['ok'];
            $feedback  = ['results' => $result['results']];
            $given     = implode(', ', $crosswordAnswers);
            $correct   = implode(', ', $payload);
        } else {
            // pyramid: payload = ['r,c' => value, ...]
            $userAnswers = $request->input('answers', []);
            $results     = [];
            $allOk       = true;
            foreach ($payload as $pos => $val) {
                $ok            = intval($userAnswers[$pos] ?? PHP_INT_MIN) === $val;
                $results[$pos] = ['correct' => $ok, 'value' => $val];
                if (! $ok) $allOk = false;
            }
            $isCorrect = $allOk;
            $feedback  = ['results' => $results];
            $given     = implode(', ', $userAnswers);
            $correct   = implode(', ', $payload);
        }

        PracticeAnswerLog::create([
            'child_id'   => $child->id,
            'topic_id'   => $topicId,
            'type'       => $qtype,
            'is_correct' => $isCorrect,
            'prompt'     => $prompt,
            'given'      => $given,
            'correct'    => $correct,
        ]);

        // A queue-backed ("auto") question advances that queue's cursor so the
        // next loadQuestion() serves the next item in the pre-built, test-like
        // ordered batch — practice never re-generates/reshuffles mid-batch.
        if (! empty($cached['advance_queue'])) {
            $queue = cache()->get($cached['advance_queue']);
            if ($queue) {
                $queue['cursor']++;
                cache()->put($cached['advance_queue'], $queue, now()->addHours(self::QUEUE_TTL_HOURS));
            }
        }

        // Coins — points-per-correct is admin-configurable per grade/level for practice too.
        // Uses the child's REAL difficulty (same as AchievementService does for real tests) —
        // practice never has its own level, and never changes this one.
        $setting = $child->childSetting;
        $points  = 0;
        if ($isCorrect) {
            $points = PointRule::resolve($setting?->grade_id, $setting?->difficulty, 'practice');
            if ($setting && $points > 0) {
                $setting->increment('coins', $points);
            }
        }

        $newAchievements = (new \App\Services\AchievementService())->handlePracticeAnswer($child, $isCorrect);

        return response()->json(array_merge($feedback, [
            'correct'          => $isCorrect,
            'coins'            => $points,
            'new_achievements' => $newAchievements,
        ]));
    }

    // ── Pyramid config by level ──────────────────────────────────────────────
    private static function pyramidConfig(int $level): array
    {
        return match (true) {
            $level <= 1 => ['height' => 3, 'max_base' => 9, 'hidden_count' => 2],
            $level === 2 => ['height' => 3, 'max_base' => 9, 'hidden_count' => 4],
            $level === 3 => ['height' => 4, 'max_base' => 9, 'hidden_count' => 3],
            $level === 4 => ['height' => 4, 'max_base' => 9, 'hidden_count' => 6],
            default      => ['height' => 5, 'max_base' => 9, 'hidden_count' => 6],
        };
    }

    // ── Pyramid generator ────────────────────────────────────────────────────
    private function buildPyramid(array $config, string $key, string $prompt = '🔺 პირამიდა'): array
    {
        $result = \App\Services\PyramidService::build(
            $config['height'],
            $config['max_base'] ?? 9,
            $config['hidden_count']
        );

        cache()->put($key, ['topic_id' => null, 'qtype' => 'pyramid', 'data' => $result['solutions'], 'prompt' => $prompt], now()->addMinutes(self::ANSWER_TTL_MINUTES));

        return ['type' => 'pyramid', 'key' => $key, 'rows' => $result['rows'], 'height' => $config['height']];
    }
}
