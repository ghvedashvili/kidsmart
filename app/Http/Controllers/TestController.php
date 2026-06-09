<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\TestAnswer;
use App\Services\TestGeneratorService;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function start(TestGeneratorService $generator)
    {
        $child = auth()->user();

        $existing = Test::where('child_id', $child->id)
            ->whereNull('completed_at')
            ->latest()
            ->first();

        if ($existing) {
            return redirect()->route('test.show', $existing);
        }

        $setting  = $child->childSetting;
        $required = $setting?->tests_per_week ?? 0;

        if ($required > 0) {
            $todayCount = Test::where('child_id', $child->id)
                ->whereNotNull('completed_at')
                ->whereDate('completed_at', today())
                ->count();

            if ($todayCount >= $required) {
                return redirect()->route('dashboard')
                    ->with('test_done', 'დღეს ' . $required . ' ტესტი შეასრულე! ✓');
            }
        }

        $result = $generator->generate($child);

        if (isset($result['error'])) {
            return redirect()->route('dashboard')->with('test_error', $result['error']);
        }

        return redirect()->route('test.show', $result['test']);
    }

    public function show(Test $test)
    {
        abort_if($test->child_id !== auth()->id(), 403);

        if ($test->isCompleted()) {
            return redirect()->route('test.result', $test);
        }

        $questions = $test->questions()->get();

        if ($questions->isEmpty()) {
            $test->delete();
            return redirect()->route('dashboard')->with('test_error', 'ტესტი ვერ ჩაიტვირთა — სცადე თავიდან');
        }

        return view('child.test', [
            'test'      => $test->load('theme'),
            'questions' => $questions,
        ]);
    }

    public function submit(Request $request, Test $test)
    {
        abort_if($test->child_id !== auth()->id(), 403);
        abort_if($test->isCompleted(), 403);

        $answers  = $request->input('answers', []);
        $correct  = 0;
        $questions = $test->questions()->get();

        foreach ($questions as $q) {
            $selected  = $answers[$q->id] ?? null;
            $isCorrect = $selected !== null && $selected === $q->correct_answer;
            if ($isCorrect) $correct++;

            TestAnswer::create([
                'test_id'          => $test->id,
                'test_question_id' => $q->id,
                'selected_answer'  => $selected,
                'is_correct'       => $isCorrect,
                'answered_at'      => now(),
            ]);
        }

        $test->update([
            'completed_at'  => now(),
            'correct_count' => $correct,
        ]);

        return redirect()->route('test.result', $test);
    }

    public function result(Test $test)
    {
        abort_if($test->child_id !== auth()->id(), 403);
        abort_if(! $test->isCompleted(), 403);

        $questions = $test->questions()->get();
        $answers   = $test->answers()->get()->keyBy('test_question_id');

        return view('child.result', [
            'test'      => $test->load('theme'),
            'questions' => $questions,
            'answers'   => $answers,
        ]);
    }
}
