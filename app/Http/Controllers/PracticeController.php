<?php

namespace App\Http\Controllers;

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

    // ── Topic selection ─────────────────────────────────────────────────────
    public function topics()
    {
        $child   = auth()->user();
        $gradeId = $child->childSetting?->grade_id;

        $topics = Topic::where('grade_id', $gradeId)
            ->whereHas('questionTemplates')
            ->orderBy('name')
            ->get();

        $sessions = PracticeSession::where('child_id', $child->id)
            ->get()
            ->keyBy(fn($s) => $s->session_type === 'pyramid' ? 'pyramid' : $s->topic_id);

        return view('child.practice-topics', compact('topics', 'sessions'));
    }

    // ── Practice shell page ─────────────────────────────────────────────────
    public function show(string $slug)
    {
        $child = auth()->user();

        if ($slug === 'pyramid') {
            $session = PracticeSession::forChild($child->id, null, 'pyramid');
            return view('child.practice', ['type' => 'pyramid', 'topic' => null, 'session' => $session, 'slug' => 'pyramid']);
        }

        $topic = Topic::findOrFail((int) $slug);
        abort_if($topic->grade_id !== $child->childSetting?->grade_id, 403);
        $session = PracticeSession::forChild($child->id, $topic->id, 'topic');

        return view('child.practice', ['type' => 'topic', 'topic' => $topic, 'session' => $session, 'slug' => $slug]);
    }

    // ── AJAX: next question ──────────────────────────────────────────────────
    public function question(string $slug): JsonResponse
    {
        $child = auth()->user();

        if ($slug === 'pyramid') {
            $session = PracticeSession::forChild($child->id, null, 'pyramid');
            $config  = self::pyramidConfig($session->level);
            $key     = "pq_{$child->id}_pyramid_" . uniqid();
            return response()->json($this->buildPyramid($config, $key));
        }

        $topic   = Topic::findOrFail((int) $slug);
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

        if ($template->isPyramid()) {
            $key  = "pq_{$child->id}_{$topic->id}_" . uniqid();
            $data = $template->generatePyramid();
            cache()->put($key, $data['solutions'], now()->addMinutes(20));
            return response()->json(['type' => 'pyramid', 'key' => $key, 'rows' => $data['rows'], 'height' => count($data['rows'])]);
        }

        if ($template->isCode()) {
            $key  = "pq_{$child->id}_{$topic->id}_" . uniqid();
            $data = $template->generateCode();
            $q    = json_decode($data['question_text'], true);
            cache()->put($key, json_decode($data['correct_answer'], true), now()->addMinutes(20));
            return response()->json([
                'type'      => 'code',
                'key'       => $key,
                'symbols'   => $q['symbols'],
                'equations' => $q['equations'],
                'target'    => $q['target'],
            ]);
        }

        if ($template->isCrossword()) {
            $key         = "pq_{$child->id}_{$topic->id}_" . uniqid();
            $data        = $template->generateCrossword();
            $q           = json_decode($data['question_text'], true);
            $correctArr  = json_decode($data['correct_answer'], true) ?? [];
            cache()->put($key, $correctArr, now()->addMinutes(20));
            $revealed     = $q['revealed'] ?? [];
            $revealedVals = [];
            foreach ($revealed as $pos) {
                $revealedVals[(string)$pos] = $correctArr[(string)$pos] ?? null;
            }
            return response()->json([
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
            ]);
        }

        $theme     = $template->theme ?? Theme::first();
        $generated = $template->generate($theme);

        $key = "pq_{$child->id}_{$topic->id}_" . uniqid();
        cache()->put($key, ['type' => 'mc', 'correct' => $generated['correct_answer']], now()->addMinutes(20));

        return response()->json([
            'type'     => 'mc',
            'key'      => $key,
            'question' => $generated['question_text'],
            'options'  => $generated['options'],
            'hint'     => $generated['hint_text'] ?? null,
        ]);
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

        $isCorrect = false;
        $feedback  = null;

        if (isset($cached['type']) && $cached['type'] === 'mc') {
            $isCorrect = (string) $request->input('answer') === (string) $cached['correct'];
            $feedback  = ['correct_answer' => $cached['correct']];
        } elseif ($request->has('code_answers')) {
            // code: cached = [pos => value]
            $result    = \App\Services\CodeService::check(json_encode($cached), $request->input('code_answers', []));
            $isCorrect = $result['ok'];
            $feedback  = ['results' => $result['results']];
        } elseif ($request->has('crossword_answers')) {
            // crossword: cached = ['0'=>a, '1'=>b, '2'=>c, '3'=>d]
            $result    = \App\Services\CrosswordService::check(json_encode($cached), $request->input('crossword_answers', []));
            $isCorrect = $result['ok'];
            $feedback  = ['results' => $result['results']];
        } else {
            // pyramid: cached = ['r,c' => value, ...]
            $userAnswers = $request->input('answers', []);
            $results     = [];
            $allOk       = true;
            foreach ($cached as $pos => $val) {
                $ok            = intval($userAnswers[$pos] ?? PHP_INT_MIN) === $val;
                $results[$pos] = ['correct' => $ok, 'value' => $val];
                if (! $ok) $allOk = false;
            }
            $isCorrect = $allOk;
            $feedback  = ['results' => $results];
        }

        // Update session
        $session = $slug === 'pyramid'
            ? PracticeSession::forChild($child->id, null, 'pyramid')
            : PracticeSession::forChild($child->id, Topic::findOrFail((int) $slug)->id, 'topic');

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

        return response()->json(array_merge($feedback, [
            'correct'    => $isCorrect,
            'level'      => $session->level,
            'streak'     => $session->streak,
            'leveled_up' => $leveledUp,
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
    private function buildPyramid(array $config, string $key): array
    {
        $result = \App\Services\PyramidService::build(
            $config['height'],
            $config['max_base'] ?? 9,
            $config['hidden_count']
        );

        cache()->put($key, $result['solutions'], now()->addMinutes(20));

        return ['type' => 'pyramid', 'key' => $key, 'rows' => $result['rows'], 'height' => $config['height']];
    }
}
