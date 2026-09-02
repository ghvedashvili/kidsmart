<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Topic;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function library()
    {
        $child   = auth()->user()->load('childSetting.grade');
        $gradeId = $child->childSetting?->grade_id;

        if (! $gradeId) {
            return view('child.videos', ['topics' => collect(), 'child' => $child]);
        }

        $topics = Topic::where('grade_id', $gradeId)
            ->with(['videos' => fn($q) => $q->orderBy('order')])
            ->whereHas('videos')
            ->orderBy('name')
            ->get();

        return view('child.videos', compact('topics', 'child'));
    }

    public function myHistory()
    {
        $child = auth()->user();
        $tests = $child->tests()
            ->with('theme')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->get();

        $totalTests = $tests->count();
        $avgScore   = $totalTests > 0
            ? round($tests->avg(fn($t) => $t->correct_count / max($t->total_questions, 1) * 100))
            : null;

        $activeTest = $child->tests()->whereNull('completed_at')->latest()->first();

        return view('child.history', compact('child', 'tests', 'totalTests', 'avgScore', 'activeTest'));
    }

    public function myTest(Test $test)
    {
        $child = auth()->user();
        abort_if($test->child_id !== $child->id, 403);
        abort_if(! $test->completed_at, 404);

        $questions = $test->questions()
            ->with(['template.topic.videos'])
            ->get();
        $answers = $test->answers()->get()->keyBy('test_question_id');

        return view('parent.child-test', [
            'child'     => $child,
            'test'      => $test,
            'questions' => $questions,
            'answers'   => $answers,
            'isChild'   => true,
        ]);
    }
}
