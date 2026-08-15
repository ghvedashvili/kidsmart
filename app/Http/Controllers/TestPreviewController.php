<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Topic;
use App\Models\Theme;
use App\Models\QuestionTemplate;
use Illuminate\Http\Request;

class TestPreviewController extends Controller
{
    public function show(Request $request)
    {
        $user = auth()->user();
        if (in_array($user->role, ['parent', 'child'])) {
            abort(403);
        }

        $grades = Grade::orderBy('number')->get();
        $themes = Theme::with('variables')->get();
        $topics = Topic::orderBy('grade_id')->orderBy('name')->get();

        $questions        = null;
        $selectedTheme    = null;
        $error            = null;
        $selectedGradeId  = $request->input('grade_id');
        $selectedDiff     = (int) $request->input('difficulty', 3);
        $selectedTopicId  = $request->input('topic_id');
        $selectedThemeId  = $request->input('theme_id');

        if ($request->filled('grade_id')) {
            $validated = $request->validate([
                'grade_id'   => 'required|exists:grades,id',
                'difficulty' => 'required|integer|min:1|max:5',
                'topic_id'   => 'nullable|exists:topics,id',
                'theme_id'   => 'nullable|exists:themes,id',
            ]);

            $templates = QuestionTemplate::where('difficulty', $validated['difficulty'])
                ->whereHas('topic', fn($q) => $q->where('grade_id', $validated['grade_id']))
                ->when(!empty($validated['topic_id']), fn($q) => $q->where('topic_id', $validated['topic_id']))
                ->with('topic')
                ->get();

            if ($templates->isEmpty()) {
                $error = 'ამ პარამეტრებისთვის კითხვები ჯერ არ დამატებულა';
            } else {
                $selectedTheme = !empty($validated['theme_id'])
                    ? Theme::with('variables')->find($validated['theme_id'])
                    : null;

                if (!$selectedTheme || $selectedTheme->variables->isEmpty()) {
                    $selectedTheme = $themes->filter(fn($t) => $t->variables->isNotEmpty())->random()
                        ?? $themes->first();
                }

                if (!$selectedTheme) {
                    $error = 'თემა არ არის — ადმინმა უნდა დაამატოს';
                } else {
                    $questions = $templates->shuffle()->map(fn($t) => array_merge(
                        ['topic_name' => $t->topic->name],
                        $t->generate($selectedTheme)
                    ))->values();
                }
            }
        }

        return view('teacher.test-preview', compact(
            'grades', 'themes', 'topics',
            'questions', 'selectedTheme', 'error',
            'selectedGradeId', 'selectedDiff', 'selectedTopicId', 'selectedThemeId'
        ));
    }
}
