<?php

namespace App\Http\Controllers;

use App\Models\ChildSetting;
use App\Models\PointRule;
use App\Models\PracticeAnswerLog;
use App\Models\PracticeSession;
use App\Models\QuestionTemplate;
use App\Models\Theme;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    private const LEVEL_UP_STREAK = 3;
    private const MAX_LEVEL       = 5;

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
            $session = PracticeSession::forChild($child->id, null, 'pyramid');
            return view('child.practice', ['type' => 'pyramid', 'topic' => null, 'session' => $session, 'slug' => 'pyramid']);
        }

        if ($slug === 'auto') {
            return view('child.practice', ['type' => 'auto', 'topic' => null, 'session' => null, 'slug' => 'auto']);
        }

        $topic = Topic::findOrFail((int) $slug);
        abort_if($topic->grade_id !== $child->childSetting?->grade_id, 403);
        $session = PracticeSession::forChild($child->id, $topic->id, 'topic');

        return view('child.practice', ['type' => 'topic', 'topic' => $topic, 'session' => $session, 'slug' => $slug]);
    }

    /**
     * Picks the next topic for auto-rotating practice: a shuffled, persisted-cursor
     * order that never repeats a topic until every available topic has had a turn —
     * the same strategy as TestGeneratorService::pickTopicIdsForTest(), but consuming
     * one topic per call instead of N upfront, and stored under its own rotation
     * column (practice_topic_rotation) so it never interferes with test generation's.
     */
    private function pickNextPracticeTopicId(ChildSetting $setting, array $availableTopicIds): ?int
    {
        $availableTopicIds = array_values(array_unique($availableTopicIds));
        $n = count($availableTopicIds);
        if ($n === 0) return null;

        $rotation = $setting->practice_topic_rotation ?? [];
        $order    = $rotation['order']  ?? [];
        $cursor   = (int) ($rotation['cursor'] ?? 0);

        $sortedStored    = $order; sort($sortedStored);
        $sortedAvailable = $availableTopicIds; sort($sortedAvailable);
        if ($sortedStored !== $sortedAvailable) {
            $order  = $availableTopicIds;
            shuffle($order);
            $cursor = 0;
        }

        $topicId = $order[$cursor % $n];
        $setting->practice_topic_rotation = ['order' => $order, 'cursor' => ($cursor + 1) % $n];
        $setting->save();

        return $topicId;
    }

    // ── AJAX: next question ──────────────────────────────────────────────────
    public function question(string $slug): JsonResponse
    {
        $child = auth()->user();

        if ($slug === 'pyramid') {
            $session = PracticeSession::forChild($child->id, null, 'pyramid');
            $config  = self::pyramidConfig($session->level);
            $key     = "pq_{$child->id}_pyramid_" . uniqid();
            return response()->json($this->buildPyramid($config, $key, '🔺 პირამიდა'));
        }

        if ($slug === 'auto') {
            $setting = $child->childSetting;
            $gradeId = $setting?->grade_id;
            $availableTopicIds = Topic::where('grade_id', $gradeId)->whereHas('questionTemplates')->pluck('id')->all();
            $topicId = $setting ? $this->pickNextPracticeTopicId($setting, $availableTopicIds) : null;

            if (! $topicId) {
                return response()->json(['error' => 'თემები ვერ მოიძებნა'], 404);
            }
            $topic = Topic::find($topicId);
        } else {
            $topic = Topic::findOrFail((int) $slug);
            abort_if($topic->grade_id !== $child->childSetting?->grade_id, 403);
        }

        $session = PracticeSession::forChild($child->id, $topic->id, 'topic');
        $diff    = min($session->level, 5);

        $template = QuestionTemplate::where('topic_id', $topic->id)
            ->where('difficulty', $diff)
            ->inRandomOrder()
            ->first()
            ?? QuestionTemplate::where('topic_id', $topic->id)->inRandomOrder()->first();

        if (! $template) {
            return response()->json(['error' => 'კითხვები ვერ მოიძებნა'], 404);
        }

        $meta = ['topic_id' => $topic->id, 'topic_name' => $topic->name, 'level' => $session->level, 'streak' => $session->streak];

        if ($template->isPyramid()) {
            $key  = "pq_{$child->id}_{$topic->id}_" . uniqid();
            $data = $template->generatePyramid();
            cache()->put($key, ['topic_id' => $topic->id, 'qtype' => 'pyramid', 'data' => $data['solutions'], 'prompt' => "🔺 პირამიდა — {$topic->name}"], now()->addMinutes(20));
            return response()->json(array_merge(['type' => 'pyramid', 'key' => $key, 'rows' => $data['rows'], 'height' => count($data['rows'])], $meta));
        }

        if ($template->isCode()) {
            $key  = "pq_{$child->id}_{$topic->id}_" . uniqid();
            $data = $template->generateCode();
            $q    = json_decode($data['question_text'], true);
            cache()->put($key, ['topic_id' => $topic->id, 'qtype' => 'code', 'data' => json_decode($data['correct_answer'], true), 'prompt' => "🔢 კოდური ამოცანა — მიზანი: {$q['target']}"], now()->addMinutes(20));
            return response()->json(array_merge([
                'type'      => 'code',
                'key'       => $key,
                'symbols'   => $q['symbols'],
                'equations' => $q['equations'],
                'target'    => $q['target'],
            ], $meta));
        }

        if ($template->isCrossword()) {
            $key         = "pq_{$child->id}_{$topic->id}_" . uniqid();
            $data        = $template->generateCrossword();
            $q           = json_decode($data['question_text'], true);
            $correctArr  = json_decode($data['correct_answer'], true) ?? [];
            cache()->put($key, ['topic_id' => $topic->id, 'qtype' => 'crossword', 'data' => $correctArr, 'prompt' => "🧩 კროსვორდი — {$topic->name}"], now()->addMinutes(20));
            $revealed     = $q['revealed'] ?? [];
            $revealedVals = [];
            foreach ($revealed as $pos) {
                $revealedVals[(string)$pos] = $correctArr[(string)$pos] ?? null;
            }
            return response()->json(array_merge([
                'type'           => 'crossword',
                'key'            => $key,
                'rows'           => $q['rows'] ?? 2,
                'cols'           => $q['cols'] ?? 2,
                'row_ops'        => $q['row_ops'] ?? ['+', '+'],
                'col_ops'        => $q['col_ops'] ?? ['+', '+'],
                'row_results'    => $q['row_results'] ?? [],
                'col_results'    => $q['col_results'] ?? [],
                'revealed'       => $revealed,
                'revealed_values'=> $revealedVals,
            ], $meta));
        }

        $theme     = $template->theme ?? Theme::first();
        $generated = $template->generate($theme);

        $key = "pq_{$child->id}_{$topic->id}_" . uniqid();
        cache()->put($key, ['topic_id' => $topic->id, 'qtype' => 'mc', 'data' => ['type' => 'mc', 'correct' => $generated['correct_answer']], 'prompt' => $generated['question_text']], now()->addMinutes(20));

        return response()->json(array_merge([
            'type'     => 'mc',
            'key'      => $key,
            'question' => $generated['question_text'],
            'options'  => $generated['options'],
            'hint'     => $generated['hint_text'] ?? null,
        ], $meta));
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

        // Update session
        $session = $topicId
            ? PracticeSession::forChild($child->id, $topicId, 'topic')
            : PracticeSession::forChild($child->id, null, 'pyramid');

        $session->total_answered++;
        $leveledUp = false;

        if ($isCorrect) {
            $session->total_correct++;
            $session->streak++;
            if ($session->streak >= self::LEVEL_UP_STREAK && $session->level < self::MAX_LEVEL) {
                $session->level++;
                $session->streak  = 0;
                $leveledUp        = true;
            }
        } else {
            $session->streak = 0;
        }

        $session->last_activity_at = now();
        $session->save();

        // Coins — points-per-correct is admin-configurable per grade/level for practice too
        $points = 0;
        if ($isCorrect) {
            $setting = $child->childSetting;
            $points  = PointRule::resolve($setting?->grade_id, $session->level, 'practice');
            if ($setting && $points > 0) {
                $setting->increment('coins', $points);
            }
        }

        return response()->json(array_merge($feedback, [
            'correct'    => $isCorrect,
            'level'      => $session->level,
            'streak'     => $session->streak,
            'leveled_up' => $leveledUp,
            'coins'      => $points,
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

        cache()->put($key, ['topic_id' => null, 'qtype' => 'pyramid', 'data' => $result['solutions'], 'prompt' => $prompt], now()->addMinutes(20));

        return ['type' => 'pyramid', 'key' => $key, 'rows' => $result['rows'], 'height' => $config['height']];
    }
}
