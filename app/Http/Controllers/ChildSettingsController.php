<?php

namespace App\Http\Controllers;

use App\Models\ChildSetting;
use App\Models\Grade;
use App\Models\Test;
use App\Models\Theme;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;

class ChildSettingsController extends Controller
{
    private function authorizeChild(User $child): void
    {
        abort_if(
            $child->parent_id !== auth()->id() || $child->role !== 'child',
            403
        );
    }

    public function stats(User $child)
    {
        $this->authorizeChild($child);

        $tests = $child->tests()
            ->with('theme')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->get();

        $totalTests  = $tests->count();
        $avgScore    = $totalTests > 0
            ? round($tests->avg(fn($t) => $t->correct_count / max($t->total_questions, 1) * 100))
            : null;
        $todayCount  = $tests->filter(fn($t) => $t->completed_at->isToday())->count();
        $required    = $child->childSetting?->tests_per_week ?? 0;

        return view('parent.child-stats', compact(
            'child', 'tests', 'totalTests', 'avgScore', 'todayCount', 'required'
        ));
    }

    public function showTest(User $child, Test $test)
    {
        $this->authorizeChild($child);
        abort_if($test->child_id !== $child->id, 404);

        $questions = $test->questions()->with([])->get();
        $answers   = $test->answers()->get()->keyBy('test_question_id');

        return view('parent.child-test', compact('child', 'test', 'questions', 'answers'));
    }

    public function edit(User $child)
    {
        $this->authorizeChild($child);

        $setting  = $child->childSetting ?? new ChildSetting(['difficulty' => 1, 'tests_per_week' => 3]);
        $grades   = Grade::orderBy('number')->get();
        $themes   = Theme::all();
        $topics   = Topic::with('grade')->orderBy('grade_id')->get();

        $selectedThemes = $child->themes->pluck('id')->toArray();
        $selectedTopics = $child->topics->pluck('id')->toArray();

        return view('parent.child-settings', compact(
            'child', 'setting', 'grades', 'themes', 'topics',
            'selectedThemes', 'selectedTopics'
        ));
    }

    public function destroy(User $child)
    {
        $this->authorizeChild($child);
        $child->delete();
        return redirect()->route('dashboard')->with('success', 'ბავშვის პროფილი წაიშალა');
    }

    public function update(Request $request, User $child)
    {
        $this->authorizeChild($child);

        $data = $request->validate([
            'name'          => 'nullable|string|max:50',
            'grade_id'      => 'nullable|exists:grades,id',
            'difficulty'    => 'required|integer|min:1|max:5',
            'tests_per_week'=> 'required|integer|min:1|max:7',
            'theme_ids'     => 'nullable|array',
            'theme_ids.*'   => 'exists:themes,id',
            'topic_ids'     => 'nullable|array',
            'topic_ids.*'   => 'exists:topics,id',
        ]);

        if (!empty($data['name'])) {
            $child->update(['name' => trim($data['name'])]);
        }

        ChildSetting::updateOrCreate(
            ['user_id' => $child->id],
            [
                'grade_id'       => $data['grade_id'] ?? null,
                'difficulty'     => $data['difficulty'],
                'tests_per_week' => $data['tests_per_week'],
            ]
        );

        $child->themes()->sync($data['theme_ids'] ?? []);
        $child->topics()->sync($data['topic_ids'] ?? []);

        return redirect()->route('dashboard')->with('success', $child->name . '-ის პარამეტრები შეინახა');
    }
}
