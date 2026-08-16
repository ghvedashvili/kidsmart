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
        $themes = Theme::orderBy('name')->get();
        $topics = Topic::orderBy('grade_id')->orderBy('name')->get();

        // all template combinations for cascading filters in JS
        $templateCombos = QuestionTemplate::select('topic_id', 'theme_id', 'difficulty')
            ->with('topic:id,grade_id')
            ->get()
            ->map(fn($t) => [
                'grade_id'   => $t->topic->grade_id,
                'topic_id'   => $t->topic_id,
                'theme_id'   => $t->theme_id,
                'difficulty' => $t->difficulty,
            ])->values();

        $questions        = null;
        $selectedTheme    = null;
        $error            = null;
        $selectedGradeId  = $request->input('grade_id');
        $selectedDiff     = (int) $request->input('difficulty', 1);
        $selectedTopicId  = $request->input('topic_id');
        $selectedThemeId  = $request->input('theme_id');

        if ($request->filled('grade_id')) {
            $validated = $request->validate([
                'grade_id'   => 'required|exists:grades,id',
                'difficulty' => 'required|integer|min:1|max:5',
                'topic_id'   => 'nullable|exists:topics,id',
                'theme_id'   => 'nullable|exists:themes,id',
            ]);

            $themeId = $validated['theme_id'] ?? null;

            $templates = QuestionTemplate::where('difficulty', $validated['difficulty'])
                ->whereHas('topic', fn($q) => $q->where('grade_id', $validated['grade_id']))
                ->when(!empty($validated['topic_id']), fn($q) => $q->where('topic_id', $validated['topic_id']))
                ->when(!empty($themeId), fn($q) => $q->where('theme_id', $themeId))
                ->with('topic')
                ->get();

            if ($templates->isEmpty()) {
                $error = 'ამ პარამეტრებისთვის კითხვები ჯერ არ დამატებულა';
            } else {
                $selectedTheme = $themeId
                    ? Theme::find($themeId)
                    : Theme::find($templates->pluck('theme_id')->filter()->first());

                if (!$selectedTheme) {
                    $error = 'თემა ვერ მოიძებნა — გთხოვთ თემატიკა აირჩიოთ';
                } else {
                    $questions = $templates->shuffle()->map(fn($t) => array_merge(
                        ['topic_name' => $t->topic->name],
                        $t->generate($selectedTheme)
                    ))->values();
                }
            }
        }

        return view('teacher.test-preview', compact(
            'grades', 'themes', 'topics', 'templateCombos',
            'questions', 'selectedTheme', 'error',
            'selectedGradeId', 'selectedDiff', 'selectedTopicId', 'selectedThemeId'
        ));
    }
}
