<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index()
    {
        $topics = Topic::with([
            'grade', 'videos',
            'questionTemplates' => fn ($q) => $q->orderByRaw('sequence IS NULL, sequence')->orderBy('id'),
            'questionTemplates.theme',
        ])->withCount('questionTemplates')->orderBy('grade_id')->get();
        $grades = Grade::orderBy('number')->get();

        $topicsJson = $topics->map(fn($t) => [
            'id'       => $t->id,
            'name'     => $t->name,
            'grade_id' => $t->grade_id,
            'count'    => $t->question_templates_count,
        ])->values()->all();

        $gradesJson = $grades->map(fn($g) => [
            'id'   => $g->id,
            'name' => $g->name,
        ])->values()->all();

        $namesByGrade = $topics->groupBy('grade_id')
            ->map(fn($g) => $g->pluck('name')->values()->all())
            ->all();

        return view('admin.topics.index', compact('topics', 'grades', 'topicsJson', 'gradesJson', 'namesByGrade'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'name'     => 'required|string|max:100',
            'sequence' => 'nullable|integer|min:1',
        ]);
        Topic::create($data);
        return back()->with('success', 'თოპიქი დაემატა');
    }

    public function update(Request $request, Topic $topic)
    {
        $data = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'name'     => 'required|string|max:100',
            'sequence' => 'nullable|integer|min:1',
        ]);
        $topic->update($data);
        return back()->with('success', '«' . $topic->name . '» განახლდა');
    }

    public function destroy(Topic $topic)
    {
        $topic->delete();
        return back()->with('success', 'წაიშალა');
    }

    /**
     * Pauses/resumes a topic — an inactive topic is excluded from everything a
     * child receives NEW questions from (TestGeneratorService's real-test,
     * practice, and olympiad selection all filter on topics.is_active), while
     * existing history (Test/TestQuestion/PracticeAnswerLog rows already
     * reference topic_id directly) and admin question-authoring for this topic
     * are both untouched.
     */
    public function toggleActive(Topic $topic)
    {
        $topic->update(['is_active' => ! $topic->is_active]);
        return back();
    }

    public function moveToGrade(Request $request)
    {
        $data = $request->validate([
            'target_grade_id' => 'required|exists:grades,id',
            'topic_ids'       => 'required|array|min:1',
            'topic_ids.*'     => 'integer|exists:topics,id',
        ]);

        $targetNames = Topic::where('grade_id', $data['target_grade_id'])
            ->pluck('name')
            ->map(fn($n) => mb_strtolower(trim($n)))
            ->flip()
            ->toArray();

        $copied = 0;
        Topic::with('questionTemplates')
            ->whereIn('id', $data['topic_ids'])
            ->get()
            ->each(function (Topic $topic) use ($data, $targetNames, &$copied) {
                if ($topic->questionTemplates->isEmpty()) return;
                if (isset($targetNames[mb_strtolower(trim($topic->name))])) return;

                $newTopic = Topic::create([
                    'grade_id' => $data['target_grade_id'],
                    'name'     => $topic->name,
                ]);

                foreach ($topic->questionTemplates as $tpl) {
                    \App\Models\QuestionTemplate::create([
                        'topic_id'        => $newTopic->id,
                        'theme_id'        => $tpl->theme_id,
                        'difficulty'      => $tpl->difficulty,
                        'template_text'   => $tpl->template_text,
                        'hint_text'       => $tpl->hint_text,
                        'correct_formula' => $tpl->correct_formula,
                        'num_config'      => $tpl->num_config,
                        'distractors'     => $tpl->distractors,
                        'conditions'      => $tpl->conditions,
                    ]);
                }

                $copied++;
            });

        return back()->with('success', $copied . ' თემა დაკოპირდა');
    }
}
