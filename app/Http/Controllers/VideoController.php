<?php

namespace App\Http\Controllers;

use App\Models\ChildGradeHistory;
use App\Models\Grade;
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

        $currentGradeId = $child->childSetting?->grade_id;

        $tests = $child->tests()
            ->with('theme')
            ->where('is_olympiad', false)
            ->when($currentGradeId, fn ($q) => $q->where('grade_id', $currentGradeId))
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->get();

        $totalTests = $tests->count();
        $avgScore   = $totalTests > 0
            ? round($tests->avg(fn($t) => $t->correct_count / max($t->total_questions, 1) * 100))
            : null;

        $activeTest = $child->tests()->where('is_olympiad', false)->whereNull('completed_at')->latest()->first();

        $oldGradeIds = collect()
            ->merge($child->tests()->whereNotNull('grade_id')->pluck('grade_id'))
            ->merge(ChildGradeHistory::where('user_id', $child->id)->pluck('grade_id'))
            ->filter()
            ->unique()
            ->reject(fn ($id) => $currentGradeId && $id == $currentGradeId);

        $oldGrades = Grade::whereIn('id', $oldGradeIds)->orderBy('number')->get();

        $oldGradeTests = $child->tests()
            ->with('theme')
            ->where('is_olympiad', false)
            ->whereIn('grade_id', $oldGrades->pluck('id'))
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->get()
            ->groupBy('grade_id');

        $gradeHistory = ChildGradeHistory::where('user_id', $child->id)->orderBy('created_at')->get()->keyBy('grade_id');

        return view('child.history', compact(
            'child', 'tests', 'totalTests', 'avgScore', 'activeTest', 'oldGrades', 'oldGradeTests', 'gradeHistory'
        ));
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
