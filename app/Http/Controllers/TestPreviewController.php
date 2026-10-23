<?php

namespace App\Http\Controllers;

use App\Exceptions\QuestionGenerationException;
use App\Models\Grade;
use App\Models\Topic;
use App\Models\Theme;
use App\Models\QuestionTemplate;
use App\Models\User;
use App\Services\TestGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $children = User::where('role', 'child')
            ->with('childSetting.grade', 'themes')
            ->orderBy('name')
            ->get();

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
        $selectedChild    = null;
        $error            = null;
        $brokenTemplates  = null;
        $selectedGradeId  = $request->input('grade_id');
        $selectedDiff     = (int) $request->input('difficulty', 1);
        $selectedTopicId  = $request->input('topic_id');
        $selectedThemeId  = $request->input('theme_id');
        $selectedChildId  = $request->input('child_id');

        if ($request->boolean('check_templates')) {
            $brokenTemplates = $this->checkAllTemplates();
        } elseif ($request->filled('child_id')) {
            $selectedChild = User::where('role', 'child')->find($request->input('child_id'));
            if (! $selectedChild) {
                $error = 'ბავშვი ვერ მოიძებნა';
            } else {
                $result = (new TestGeneratorService())->generate($selectedChild, persist: false);
                if (isset($result['error'])) {
                    $error = $result['error'];
                } else {
                    $questions = $result['questions']->map(fn ($tq) => [
                        'topic_name'     => $tq->template->topic->name,
                        'question_type'  => $tq->question_type,
                        'question_text'  => $tq->question_text,
                        'hint_text'      => $tq->hint_text,
                        'options'        => $tq->options,
                        'correct_answer' => $tq->correct_answer,
                    ])->values();
                    if ($questions->isEmpty()) {
                        $error = 'ამ ბავშვისთვის ამჯერად კითხვა ვერ დააგენერირდა';
                    }
                }
            }
        } elseif ($request->filled('grade_id')) {
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
                $mcTemplates = $templates->filter(fn($t) => !$t->isPyramid() && !$t->isCode() && !$t->isCrossword());

                if ($mcTemplates->isNotEmpty()) {
                    $selectedTheme = $themeId
                        ? Theme::find($themeId)
                        : Theme::find($mcTemplates->pluck('theme_id')->filter()->first());

                    if (!$selectedTheme) {
                        $error = 'თემა ვერ მოიძებნა — გთხოვთ თემატიკა აირჩიოთ';
                    }
                }

                if (!$error) {
                    // A broken template (formula never resolves) shouldn't take down
                    // the whole preview page — skip just that one and keep the rest.
                    $questions = $templates->shuffle()->map(function($t) use ($selectedTheme) {
                        try {
                            if ($t->isPyramid()) {
                                $data = $t->generatePyramid();
                            } elseif ($t->isCode()) {
                                $data = $t->generateCode();
                            } elseif ($t->isCrossword()) {
                                $data = $t->generateCrossword();
                            } else {
                                $data = $t->generate($selectedTheme);
                            }
                        } catch (QuestionGenerationException $e) {
                            Log::error('TestPreviewController: skipping broken template', ['template_id' => $t->id, 'error' => $e->getMessage()]);
                            return null;
                        }
                        return array_merge(['topic_name' => $t->topic->name, 'question_type' => $t->question_type], $data);
                    })->filter()->values();
                }
            }
        }

        return view('teacher.test-preview', compact(
            'grades', 'themes', 'topics', 'templateCombos', 'children',
            'questions', 'selectedTheme', 'selectedChild', 'error', 'brokenTemplates',
            'selectedGradeId', 'selectedDiff', 'selectedTopicId', 'selectedThemeId', 'selectedChildId'
        ));
    }

    /**
     * Runs generate() once for every active template, catching ANY failure (not
     * just the formula-specific QuestionGenerationException, in case a future bug
     * takes a different shape) — this is what a child would actually be served,
     * so it's the most direct way for an admin to find a broken template before
     * a child does. Beyond a thrown exception, a successful-looking call can
     * still hand back a question that's silently unusable (empty answer, an
     * unresolved "{{...}}" token, a correct answer missing from its own
     * multiple-choice options, duplicate options) — answerLooksSane() catches
     * those too, since none of them throw.
     *
     * @return array<int, array{template: QuestionTemplate, error: string}>
     */
    private function checkAllTemplates(): array
    {
        $templates = QuestionTemplate::where('is_active', true)
            ->with('topic.grade', 'theme')
            ->get();

        $broken = [];
        foreach ($templates as $t) {
            try {
                if ($t->isPyramid()) {
                    $result = $t->generatePyramid();
                } elseif ($t->isCode()) {
                    $result = $t->generateCode();
                } elseif ($t->isCrossword()) {
                    $result = $t->generateCrossword();
                } else {
                    $result = $t->generate($t->theme ?? Theme::first());
                }
                if ($issue = $this->answerLooksSane($result)) {
                    $broken[] = ['template' => $t, 'error' => $issue];
                }
            } catch (\Throwable $e) {
                $broken[] = ['template' => $t, 'error' => $e->getMessage()];
            }
        }

        return $broken;
    }

    /**
     * Sanity-checks a generate() result that didn't throw — returns a
     * Georgian description of the problem, or null if the answer looks usable.
     */
    private function answerLooksSane(array $result): ?string
    {
        $correct = $result['correct_answer'] ?? null;
        if ($correct === null || $correct === '') {
            return 'სწორი პასუხი არ დაგენერირდა (ცარიელია)';
        }

        // Pyramid/code/crossword encode a per-cell answer set as a JSON array
        // string ("options" is always null for these) — decode it so an empty
        // array or a leftover unresolved slot is caught too, not just a
        // missing/empty top-level string.
        if (is_string($correct) && (str_starts_with($correct, '[') || str_starts_with($correct, '{'))) {
            $decoded = json_decode($correct, true);
            if (is_array($decoded)) {
                if (! $decoded) {
                    return 'სწორი პასუხი ცარიელი მასივია';
                }
                foreach ($decoded as $v) {
                    if ($v === null || $v === '' || $v === '?') {
                        return 'სწორი პასუხის ერთი ველი ამოუხსნელია';
                    }
                }
                return null;
            }
        }

        $correctStr = (string) $correct;
        if (str_contains($correctStr, '{{') || $correctStr === '?') {
            return "სწორი პასუხი ბოლომდე არ ჩანაცვლდა: \"{$correctStr}\"";
        }

        $options = $result['options'] ?? null;
        if (is_array($options) && $options) {
            if (count($options) < 2) {
                return 'ვარიანტები მხოლოდ ' . count($options) . ' ცალია (საჭიროა მინ. 2)';
            }
            if (count($options) !== count(array_unique($options))) {
                return 'ვარიანტებში დუბლირებული პასუხია';
            }
            if (! in_array($correct, $options, true)) {
                return 'სწორი პასუხი ვარიანტებში არ ფიგურირებს';
            }
            foreach ($options as $opt) {
                if (is_string($opt) && (str_contains($opt, '{{') || $opt === '?')) {
                    return "ვარიანტი ბოლომდე არ ჩანაცვლდა: \"{$opt}\"";
                }
            }
        }

        return null;
    }
}
