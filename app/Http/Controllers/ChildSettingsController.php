<?php

namespace App\Http\Controllers;

use App\Models\ChildGradeHistory;
use App\Models\ChildSetting;
use App\Models\QuestionTemplate;
use App\Models\Test;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Http\Request;

class ChildSettingsController extends Controller
{
    private function authorizeChild(User $child): void
    {
        abort_if($child->role !== 'child', 403);
        abort_if(! $child->parents()->where('users.id', auth()->id())->exists(), 403);
    }

    public function stats(User $child)
    {
        $this->authorizeChild($child);

        $currentGradeId = $child->childSetting?->grade_id;

        // Test history / topic breakdown below reflect the CURRENT grade only —
        // anything from a grade the child has since left surfaces in the
        // "ძველი კლასები" accordion instead (built further down).
        $tests = $child->tests()
            ->with('theme')
            ->where('is_olympiad', false)
            ->when($currentGradeId, fn ($q) => $q->where('grade_id', $currentGradeId))
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->get();

        $olympiadTests = $child->tests()
            ->with('theme')
            ->where('is_olympiad', true)
            ->when($currentGradeId, fn ($q) => $q->where('grade_id', $currentGradeId))
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->get();

        $totalTests  = $tests->count();
        $avgScore    = $totalTests > 0
            ? round($tests->avg(fn($t) => $t->correct_count / max($t->total_questions, 1) * 100))
            : null;
        $todayCount  = $tests->filter(fn($t) => $t->completed_at->isToday())->count();
        $required    = $child->childSetting?->tests_per_week ?? 0;

        $topicStats = \App\Models\TestAnswer::query()
            ->join('test_questions', 'test_answers.test_question_id', '=', 'test_questions.id')
            ->join('tests', 'test_answers.test_id', '=', 'tests.id')
            ->join('question_templates', 'test_questions.template_id', '=', 'question_templates.id')
            ->join('topics', 'question_templates.topic_id', '=', 'topics.id')
            ->where('tests.child_id', $child->id)
            ->where('tests.is_olympiad', false)
            ->when($currentGradeId, fn ($q) => $q->where('tests.grade_id', $currentGradeId))
            ->whereNotNull('tests.completed_at')
            ->selectRaw('topics.id as topic_id, topics.name as topic_name, question_templates.difficulty as difficulty, SUM(test_answers.is_correct) as correct, COUNT(*) as total')
            ->groupBy('topics.id', 'topics.name', 'question_templates.difficulty')
            ->orderBy('topics.name')
            ->orderBy('question_templates.difficulty')
            ->get()
            ->map(fn ($r) => (object) [
                'topic_id'   => $r->topic_id,
                'topic_name' => $r->topic_name,
                'difficulty' => $r->difficulty,
                'correct'    => $r->correct,
                'total'      => $r->total,
                'pct'        => $r->total > 0 ? round($r->correct / $r->total * 100) : 0,
            ])
            ->groupBy('topic_name');

        // Old grades: every grade this child has test/history data for, other than
        // the current one — one expandable card per grade in the view.
        $oldGradeIds = collect()
            ->merge($child->tests()->whereNotNull('grade_id')->pluck('grade_id'))
            ->merge(ChildGradeHistory::where('user_id', $child->id)->pluck('grade_id'))
            ->filter()
            ->unique()
            ->reject(fn ($id) => $currentGradeId && $id == $currentGradeId);

        $oldGrades = \App\Models\Grade::whereIn('id', $oldGradeIds)->orderBy('number')->get();

        $oldGradeTests = $child->tests()
            ->with('theme')
            ->where('is_olympiad', false)
            ->whereIn('grade_id', $oldGrades->pluck('id'))
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->get()
            ->groupBy('grade_id');

        $oldGradeOlympiadTests = $child->tests()
            ->with('theme')
            ->where('is_olympiad', true)
            ->whereIn('grade_id', $oldGrades->pluck('id'))
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->get()
            ->groupBy('grade_id');

        $gradeHistory = ChildGradeHistory::where('user_id', $child->id)->orderBy('created_at')->get()->keyBy('grade_id');

        return view('parent.child-stats', compact(
            'child', 'tests', 'totalTests', 'avgScore', 'todayCount', 'required', 'topicStats', 'olympiadTests',
            'oldGrades', 'oldGradeTests', 'oldGradeOlympiadTests', 'gradeHistory'
        ));
    }

    public function showTest(User $child, Test $test)
    {
        $this->authorizeChild($child);
        abort_if($test->child_id !== $child->id, 404);

        $questions = $test->questions()->with(['template.topic.videos'])->get();
        $answers   = $test->answers()->get()->keyBy('test_question_id');

        return view('parent.child-test', compact('child', 'test', 'questions', 'answers'));
    }

    public function updateAvatar(Request $request, User $child)
    {
        $this->authorizeChild($child);

        $data = $request->validate([
            'avatar' => 'required|in:boy,girl',
        ]);

        $child->update(['avatar' => $data['avatar']]);

        return redirect()->route('dashboard');
    }

    public function destroy(User $child)
    {
        $parent = auth()->user();
        abort_if(! $parent->children()->where('users.id', $child->id)->exists(), 403);

        $parent->children()->detach($child->id);

        if ($child->parents()->count() === 0) {
            $child->delete();
            return redirect()->route('dashboard')->with('success', $child->name . '-ის პროფილი სრულად წაიშალა');
        }

        return redirect()->route('dashboard')->with('success', $child->name . ' შენს სიაში წაიშალა');
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

        $oldSetting   = ChildSetting::where('user_id', $child->id)->first();
        $gradeChanged = $oldSetting && $oldSetting->grade_id && $oldSetting->grade_id != ($data['grade_id'] ?? null);
        $warnings     = [];

        if ($gradeChanged) {
            ChildGradeHistory::create([
                'user_id'         => $child->id,
                'grade_id'        => $oldSetting->grade_id,
                'difficulty'      => $oldSetting->difficulty,
                'tests_completed' => Test::where('child_id', $child->id)
                    ->where('grade_id', $oldSetting->grade_id)
                    ->where('is_olympiad', false)
                    ->whereNotNull('completed_at')
                    ->count(),
            ]);
            $warnings[] = 'დონე დაუბრუნდა თავიდან და ტესტების მთვლელი განულდა — ძველი კლასის ნამუშევრები სტატისტიკაში შენარჩუნებულია.';
        }

        $themeIds = $data['theme_ids'] ?? [];
        if ($gradeChanged && !empty($themeIds) && !empty($data['grade_id'])) {
            $hasTemplates = QuestionTemplate::whereIn('theme_id', $themeIds)
                ->whereHas('topic', fn ($q) => $q->where('grade_id', $data['grade_id']))
                ->exists();
            if (! $hasTemplates) {
                $oldNames = Theme::whereIn('id', $themeIds)->pluck('name')->implode(', ');
                $defaultThemeId = Theme::where('name', 'სტანდარტი')->value('id');
                $themeIds = $defaultThemeId ? [$defaultThemeId] : [];
                $warnings[] = "თემატიკა („{$oldNames}“) ახალ კლასში ხელმისაწვდომი არ არის — გადართულია სტანდარტულზე.";
            }
        }

        ChildSetting::updateOrCreate(
            ['user_id' => $child->id],
            [
                'grade_id'       => $data['grade_id'] ?? null,
                'difficulty'     => $gradeChanged ? 1 : $data['difficulty'],
                'tests_per_week' => $data['tests_per_week'],
                ...($gradeChanged ? ['tests_since_level_review' => 0] : []),
            ]
        );

        $child->themes()->sync($themeIds);
        $child->topics()->sync($data['topic_ids'] ?? []);

        return redirect()->route('dashboard')
            ->with('success', $child->name . '-ის პარამეტრები შეინახა')
            ->with('grade_change_notice', $warnings);
    }
}
