<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\QuestionTemplate;
use App\Models\Theme;
use App\Models\ThemeVarGroup;
use App\Models\ThemeVariable;
use App\Models\Topic;
use Illuminate\Http\Request;

class QuestionTemplateController extends Controller
{
    public function index(Request $request)
    {
        $showTrashed = $request->boolean('trashed');

        $query = $showTrashed
            ? QuestionTemplate::onlyTrashed()->with('topic.grade', 'theme')
            : QuestionTemplate::with('topic.grade', 'theme');

        if ($request->filled('grade_id')) {
            $query->whereHas('topic', fn($q) => $q->where('grade_id', $request->grade_id));
        }
        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->topic_id);
        }
        if ($request->filled('theme_id')) {
            $query->where('theme_id', $request->theme_id);
        }
        if ($request->filled('difficulty')) {
            if ($request->difficulty === 'olympiad') {
                $query->where('is_olympiad', true);
            } else {
                $query->where('difficulty', $request->difficulty)->where('is_olympiad', false);
            }
        }

        return view('admin.questions.index', [
            'templates'   => $query->latest()->paginate(20),
            'grades'      => Grade::orderBy('number')->get(),
            'topics'      => Topic::with('grade')->orderBy('grade_id')->get(),
            'themes'      => Theme::orderBy('name')->get(),
            'filters'     => $request->only('grade_id', 'topic_id', 'theme_id', 'difficulty'),
            'themeVarMap' => $this->themeVarMap(),
            'showTrashed' => $showTrashed,
        ]);
    }

    public function create()
    {
        return view('admin.questions.form', [
            'template'       => null,
            'grades'         => Grade::orderBy('number')->get(),
            'topics'         => Topic::with('grade')->orderBy('grade_id')->get(),
            'themes'         => Theme::orderBy('name')->get(),
            'themeVarMap'    => $this->themeVarMap(),
            'allThemesData'  => $this->allThemesData(),
            'defaultThemeId' => Theme::where('name', 'სტანდარტი')->value('id'),
            'loadableTemplates' => QuestionTemplate::with('topic.grade')
                ->orderBy('topic_id')->orderBy('id')
                ->get(['id', 'topic_id', 'question_type', 'title', 'template_text']),
        ]);
    }

    /**
     * Full data for one template, used by the create page's "load an existing
     * template as a starting point" picker (fetched on demand, not embedded for
     * every row, since only the short label is needed until one is picked).
     */
    public function data(QuestionTemplate $question)
    {
        return response()->json($question->only([
            'topic_id', 'theme_id', 'difficulty', 'is_olympiad', 'is_active', 'sequence',
            'question_type', 'answer_type', 'title', 'template_text', 'hint_text', 'correct_formula',
            'num_config', 'distractors', 'conditions',
        ]));
    }

    /**
     * Live-preview button on the questions list ("👁") — runs the REAL generator
     * (the same one a child's test uses), not a JS re-implementation, so this can
     * never drift out of sync with what actually gets served.
     */
    public function preview(QuestionTemplate $question)
    {
        try {
            $data = $question->generate($question->theme ?? \App\Models\Theme::first());
        } catch (\App\Exceptions\QuestionGenerationException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok'             => true,
            'question_type'  => $question->question_type ?? 'multiple_choice',
            'question_text'  => $data['question_text'] ?? null,
            'hint_text'      => $data['hint_text'] ?? null,
            'options'        => $data['options'] ?? null,
            'correct_answer' => $data['correct_answer'] ?? null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        QuestionTemplate::create($data);
        return redirect()->route('admin.questions.index')->with('success', 'შაბლონი დაემატა');
    }

    public function edit(QuestionTemplate $question)
    {
        return view('admin.questions.form', [
            'template'       => $question->load('topic'),
            'grades'         => Grade::orderBy('number')->get(),
            'topics'         => Topic::with('grade')->orderBy('grade_id')->get(),
            'themes'         => Theme::orderBy('name')->get(),
            'themeVarMap'    => $this->themeVarMap(),
            'allThemesData'  => $this->allThemesData(),
            'defaultThemeId' => null,
        ]);
    }

    public function update(Request $request, QuestionTemplate $question)
    {
        $question->update($this->validated($request));
        return redirect()->route('admin.questions.index')->with('success', 'განახლდა');
    }

    public function exportPdf(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:question_templates,id'])['ids'];

        $templates = QuestionTemplate::with('topic.grade')
            ->whereIn('id', $ids)
            ->orderBy('topic_id')->orderBy('difficulty')
            ->get();

        // Build a universal theme from all ThemeVariables so every placeholder gets a value
        $theme = new \App\Models\Theme();
        $theme->setRelation('variables', \App\Models\ThemeVariable::all());

        $questions = $templates->map(function ($tpl, $i) use ($theme) {
            // A broken template (formula never resolves) shouldn't take down the
            // whole PDF export — note it instead of silently dropping or crashing,
            // since the admin explicitly picked this exact set of templates.
            try {
                $generated = $tpl->generate($theme);
                $text   = $generated['question_text'];
                $answer = $generated['correct_answer'];
            } catch (\App\Exceptions\QuestionGenerationException $e) {
                \Illuminate\Support\Facades\Log::error('QuestionTemplateController::exportPdf: broken template', ['template_id' => $tpl->id, 'error' => $e->getMessage()]);
                $text   = "⚠ შაბლონი #{$tpl->id} ვერ დააგენერირდა — შეამოწმეთ ფორმულა";
                $answer = '—';
            }
            return [
                'num'        => $i + 1,
                'text'       => $text,
                'answer'     => $answer,
                'grade'      => $tpl->topic->grade->name,
                'topic'      => $tpl->topic->name,
                'difficulty' => $tpl->difficulty,
            ];
        });

        return view('admin.questions.pdf', compact('questions'));
    }

    public function destroy(QuestionTemplate $question)
    {
        $question->delete();
        return back()->with('success', 'წაიშალა (აღდგენა შესაძლებელია "წაშლილების" სიიდან)');
    }

    public function restore(int $id)
    {
        $question = QuestionTemplate::onlyTrashed()->findOrFail($id);
        $question->restore();
        return back()->with('success', 'აღდგენილია');
    }

    public function toggleActive(QuestionTemplate $question)
    {
        $question->update(['is_active' => ! $question->is_active]);
        return back();
    }

    /**
     * Sets (or clears) a template's priority within its topic's rotation — used
     * from the topics page's expandable template list. Called via fetch(), so it
     * responds with JSON rather than redirecting.
     */
    public function updateSequence(Request $request, QuestionTemplate $question)
    {
        $data = $request->validate([
            'sequence' => 'nullable|integer|min:1',
        ]);
        // "nullable" only skips the other rules for an empty string — it does not
        // turn it into an actual null, so a cleared input would otherwise try to
        // save '' into this integer column and fail at the DB layer.
        $sequence = $data['sequence'] ?? null;
        $question->update(['sequence' => $sequence === '' ? null : $sequence]);
        return response()->json(['success' => true, 'sequence' => $question->sequence]);
    }

    /**
     * Quick "move" action from the questions list — lets an admin re-home a
     * question to a different grade (via topic), topic, theme, or difficulty
     * without opening the full edit form. Grade itself isn't a column on this
     * model; picking a new grade just means picking a topic that belongs to it.
     */
    public function reassign(Request $request, QuestionTemplate $question)
    {
        $data = $request->validate([
            'topic_id'   => 'required|exists:topics,id',
            'theme_id'   => 'nullable|exists:themes,id',
            'difficulty' => 'required|integer|min:1|max:5',
        ]);

        $grade = Topic::find($data['topic_id'])?->grade;
        if (! $question->is_olympiad && $grade && $data['difficulty'] > $grade->max_level) {
            return response()->json(['error' => "ამ კლასს მაქსიმუმ {$grade->max_level} დონე აქვს"], 422);
        }

        // pyramid/code/crossword/scale never use a theme — keep that invariant
        // regardless of what the modal happened to send.
        if (in_array($question->question_type, ['pyramid', 'code', 'crossword', 'scale'], true)) {
            $data['theme_id'] = null;
        }

        $question->update($data);

        return response()->json(['success' => true]);
    }

    /**
     * Clones this template into a different grade (via topic)/topic/theme/
     * difficulty, leaving the original exactly where it was — for reusing a
     * question across topics/grades instead of retyping it.
     */
    public function duplicate(Request $request, QuestionTemplate $question)
    {
        $data = $request->validate([
            'topic_id'   => 'required|exists:topics,id',
            'theme_id'   => 'nullable|exists:themes,id',
            'difficulty' => 'required|integer|min:1|max:5',
        ]);

        $grade = Topic::find($data['topic_id'])?->grade;
        if (! $question->is_olympiad && $grade && $data['difficulty'] > $grade->max_level) {
            return response()->json(['error' => "ამ კლასს მაქსიმუმ {$grade->max_level} დონე აქვს"], 422);
        }

        if (in_array($question->question_type, ['pyramid', 'code', 'crossword', 'scale'], true)) {
            $data['theme_id'] = null;
        }

        $copy = $question->replicate();
        $copy->topic_id   = $data['topic_id'];
        $copy->theme_id   = $data['theme_id'];
        $copy->difficulty = $data['difficulty'];
        $copy->sequence   = null;
        $copy->save();

        return response()->json(['success' => true, 'id' => $copy->id]);
    }

    private function allThemesData(): array
    {
        $themes = Theme::with(['varGroups.variables', 'variables' => fn($q) => $q->whereNull('group_id')])->get();
        $result = [];
        foreach ($themes as $theme) {
            $groups = [];
            $varMap = [];
            foreach ($theme->varGroups as $g) {
                $slots = $g->variables->pluck('variable_name')->all();
                $groups[] = ['name' => $g->name, 'pool' => $g->values ?? [], 'slots' => $slots];
                foreach ($slots as $slot) {
                    $varMap[$slot] = $g->values ?? [];
                }
            }
            $standalone = $theme->variables->pluck('variable_name')->sort()->values()->all();
            foreach ($theme->variables as $v) {
                $varMap[$v->variable_name] = $v->values ?? [];
            }
            $result[$theme->id] = ['groups' => $groups, 'standalone' => $standalone, 'varMap' => $varMap];
        }
        return $result;
    }

    private function themeVarNames(): array
    {
        return ThemeVariable::distinct()->pluck('variable_name')->sort()->values()->all();
    }

    private function themeVarMap(): array
    {
        $map = [];
        ThemeVariable::with('group')->get()->each(function ($tv) use (&$map) {
            $values = ($tv->group_id && $tv->group) ? ($tv->group->values ?? []) : ($tv->values ?? []);
            $map[$tv->variable_name] = array_unique(array_merge($map[$tv->variable_name] ?? [], $values));
        });
        return $map;
    }

    private function themeVarGroups(): array
    {
        $byName = [];
        ThemeVarGroup::with('variables')->get()->each(function ($g) use (&$byName) {
            $name = $g->name;
            if (!isset($byName[$name])) {
                $byName[$name] = ['name' => $name, 'pool' => $g->values ?? [], 'slots' => []];
            } else {
                $byName[$name]['pool'] = array_unique(array_merge($byName[$name]['pool'], $g->values ?? []));
            }
            foreach ($g->variables as $v) {
                if (!in_array($v->variable_name, $byName[$name]['slots'])) {
                    $byName[$name]['slots'][] = $v->variable_name;
                }
            }
        });
        return array_values($byName);
    }

    private function themeVarStandalone(): array
    {
        return ThemeVariable::whereNull('group_id')->distinct()
            ->pluck('variable_name')->sort()->values()->all();
    }

    private function validated(Request $request): array
    {
        $qType       = $request->input('question_type');
        $isPyramid   = $qType === 'pyramid';
        $isCode      = $qType === 'code';
        $isCrossword = $qType === 'crossword';
        $isScale     = $qType === 'scale';
        $isSpecial   = $isPyramid || $isCode || $isCrossword || $isScale;

        $raw = $request->validate([
            'topic_id'        => 'required|exists:topics,id',
            'theme_id'        => 'nullable|exists:themes,id',
            'difficulty'      => 'required|integer|min:1|max:5',
            'is_olympiad'     => 'nullable|boolean',
            'is_active'       => 'nullable|boolean',
            'sequence'        => 'nullable|integer|min:1',
            'question_type'   => 'required|in:multiple_choice,pyramid,code,crossword,scale',
            'answer_type'     => $isScale ? 'required|in:numeric,numeric_input' : ($isSpecial ? 'nullable' : 'required|in:numeric,text,numeric_input'),
            'title'           => 'nullable|string|max:150',
            'template_text'   => $isSpecial ? 'nullable|string' : 'required|string',
            'hint_text'       => 'nullable|string',
            'correct_formula' => $isSpecial ? 'nullable|string' : 'required|string|max:200',
            'num_config'      => 'required|string',
            'distractors'     => 'nullable|string',
            'conditions'      => 'nullable|string',
        ]);

        $numConfig = json_decode($raw['num_config'], true);
        if (! is_array($numConfig)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'num_config' => 'JSON ფორმატი არასწორია',
            ]);
        }

        $isOlympiad = $request->boolean('is_olympiad');
        $isActive   = $request->boolean('is_active', true);
        $sequence   = $raw['sequence'] ?? null;

        $grade = Topic::find($raw['topic_id'])?->grade;
        if (! $isOlympiad && $grade && $raw['difficulty'] > $grade->max_level) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'difficulty' => "ამ კლასს მაქსიმუმ {$grade->max_level} დონე აქვს",
            ]);
        }

        if ($isPyramid) {
            $pyrHeight = max(3, (int) ($numConfig['height']       ?? 3));
            $pyrMax    = max(2, (int) ($numConfig['max_base']     ?? 9));
            $pyrHide   = max(1, (int) ($numConfig['hidden_count'] ?? 2));
            return [
                'topic_id'        => $raw['topic_id'],
                'theme_id'        => null,
                'difficulty'      => $raw['difficulty'],
                'is_olympiad'     => $isOlympiad,
                'is_active'       => $isActive,
                'sequence'        => $sequence,
                'question_type'   => 'pyramid',
                'answer_type'     => 'numeric',
                'title'           => $raw['title'] ?? null,
                'template_text'   => "პირამიდა {$pyrHeight}-ძირი · {$pyrHide} ცარიელი",
                'hint_text'       => $raw['hint_text'] ?? null,
                'correct_formula' => '',
                'num_config'      => ['height' => $pyrHeight, 'max_base' => $pyrMax, 'hidden_count' => $pyrHide],
                'distractors'     => null,
                'conditions'      => null,
            ];
        }

        if ($isCrossword) {
            $minVal  = max(1,           (int) ($numConfig['min_val']   ?? 1));
            $maxVal  = max($minVal + 1, (int) ($numConfig['max_val']   ?? 10));
            $ops     = (array) ($numConfig['operators'] ?? ['+']);
            if (empty($ops)) $ops = ['+'];
            $rows          = in_array((int)($numConfig['rows'] ?? 2), [2,3]) ? (int)$numConfig['rows'] : 2;
            $cols          = in_array((int)($numConfig['cols'] ?? 2), [2,3]) ? (int)$numConfig['cols'] : 2;
            $revealedCount = max(0, min($rows * $cols - 1, (int)($numConfig['revealed_count'] ?? 0)));
            $opsStr  = implode('', $ops);
            return [
                'topic_id'        => $raw['topic_id'],
                'theme_id'        => null,
                'difficulty'      => $raw['difficulty'],
                'is_olympiad'     => $isOlympiad,
                'is_active'       => $isActive,
                'sequence'        => $sequence,
                'question_type'   => 'crossword',
                'answer_type'     => 'numeric',
                'title'           => $raw['title'] ?? null,
                'template_text'   => "კროსვორდი {$rows}×{$cols} {$minVal}-{$maxVal} {$opsStr}" . ($revealedCount ? " ({$revealedCount}✓)" : ''),
                'hint_text'       => $raw['hint_text'] ?? null,
                'correct_formula' => '',
                'num_config'      => ['min_val' => $minVal, 'max_val' => $maxVal, 'operators' => $ops, 'rows' => $rows, 'cols' => $cols, 'revealed_count' => $revealedCount],
                'distractors'     => null,
                'conditions'      => null,
            ];
        }

        if ($isCode) {
            $symCount    = max(2, min(5,  (int)  ($numConfig['symbol_count']  ?? 3)));
            $minVal      = max(1,          (int)  ($numConfig['min_val']       ?? 1));
            $maxVal      = max($minVal + 1,(int)  ($numConfig['max_val']       ?? 9));
            $ops         = (array) ($numConfig['operators'] ?? ['+']);
            if (empty($ops)) $ops = ['+'];
            $varsPerEq   = in_array((int)($numConfig['vars_per_eq'] ?? 2), [2, 3]) ? (int)($numConfig['vars_per_eq'] ?? 2) : 2;
            $uniqueVals  = !empty($numConfig['unique_values']);
            $opsStr      = implode('', $ops);
            $descParts   = [
                "კოდი {$symCount} სიმ",
                "{$minVal}-{$maxVal}",
                $opsStr,
                "{$varsPerEq}-ცვ",
                $uniqueVals ? 'განსხ' : 'გამეორ',
            ];
            return [
                'topic_id'        => $raw['topic_id'],
                'theme_id'        => null,
                'difficulty'      => $raw['difficulty'],
                'is_olympiad'     => $isOlympiad,
                'is_active'       => $isActive,
                'sequence'        => $sequence,
                'question_type'   => 'code',
                'answer_type'     => 'numeric',
                'title'           => $raw['title'] ?? null,
                'template_text'   => implode(' · ', $descParts),
                'hint_text'       => $raw['hint_text'] ?? null,
                'correct_formula' => '',
                'num_config'      => [
                    'symbol_count'  => $symCount,
                    'min_val'       => $minVal,
                    'max_val'       => $maxVal,
                    'operators'     => $ops,
                    'vars_per_eq'   => $varsPerEq,
                    'unique_values' => $uniqueVals,
                ],
                'distractors'     => null,
                'conditions'      => null,
            ];
        }

        if ($isScale) {
            $symbols = is_array($numConfig['symbols'] ?? null) ? $numConfig['symbols'] : [];
            $left    = is_array($numConfig['left']    ?? null) ? $numConfig['left']    : [];
            $right   = is_array($numConfig['right']   ?? null) ? $numConfig['right']   : [];
            $countMin = max(1, (int) ($numConfig['count_min'] ?? 1));
            $countMax = max($countMin, (int) ($numConfig['count_max'] ?? 6));
            $hasCustomPrompt = trim((string) ($raw['template_text'] ?? '')) !== '';

            // The answer symbol is chosen automatically at generation time from
            // ANY defined symbol, on a pan or not (see ScaleService::build()) —
            // not admin-picked. An emoji-kind candidate is only eligible when
            // the admin has written a custom template_text (its weight is never
            // shown directly, so without a hand-written relationship there'd be
            // no way for a child to determine it).
            $eligible = array_filter(array_keys($symbols), function ($key) use ($symbols, $hasCustomPrompt) {
                $kind = $symbols[$key]['kind'] ?? 'emoji';
                return $kind === 'weight' || $hasCustomPrompt;
            });
            if (! $eligible) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'num_config' => 'საჭიროა მინიმუმ 1 წონა-ტიპის სიმბოლო, ან დაწერეთ კითხვის ტექსტი, რომ ემოჯი-ტიპის სიმბოლოც დასაშვები გახდეს პასუხად',
                ]);
            }

            // Distractors: only meaningful for a weight-kind answer in MC mode
            // (an emoji-kind MC answer builds its own "{count} {emoji}" wrong
            // options instead — see ScaleService::buildEmojiOptions()).
            $scaleDistractors = null;
            if (! empty($raw['distractors'])) {
                $scaleDistractors = json_decode($raw['distractors'], true);
                if (! is_array($scaleDistractors)) $scaleDistractors = null;
            }

            // Conditions constrain how the symbols' rolled weights relate to each
            // other (by their display letter — A, B, C... in save order), reusing
            // the same conditions engine/evaluator the plain MC templates use.
            $scaleConditions = null;
            if (! empty($raw['conditions'])) {
                $scaleConditions = json_decode($raw['conditions'], true);
                if (! is_array($scaleConditions)) $scaleConditions = null;
            }

            return [
                'topic_id'        => $raw['topic_id'],
                'theme_id'        => null,
                'difficulty'      => $raw['difficulty'],
                'is_olympiad'     => $isOlympiad,
                'is_active'       => $isActive,
                'sequence'        => $sequence,
                'question_type'   => 'scale',
                'answer_type'     => $raw['answer_type'],
                'title'           => $raw['title'] ?? null,
                'template_text'   => $raw['template_text'] ?? '',
                'hint_text'       => $raw['hint_text'] ?? null,
                'correct_formula' => '',
                'num_config'      => [
                    'symbols'   => $symbols,
                    'left'      => $left,
                    'right'     => $right,
                    'count_min' => $countMin,
                    'count_max' => $countMax,
                ],
                'distractors'     => $scaleDistractors,
                'conditions'      => $scaleConditions,
            ];
        }

        $distractors = null;
        if (! empty($raw['distractors'])) {
            $distractors = json_decode($raw['distractors'], true);
        }

        $conditions = null;
        if (! empty($raw['conditions'])) {
            $conditions = json_decode($raw['conditions'], true);
            if (! is_array($conditions)) $conditions = null;
        }

        return [
            'topic_id'        => $raw['topic_id'],
            'theme_id'        => $raw['theme_id'] ?? null,
            'difficulty'      => $raw['difficulty'],
            'is_olympiad'     => $isOlympiad,
            'is_active'       => $isActive,
            'sequence'        => $sequence,
            'question_type'   => 'multiple_choice',
            'answer_type'     => $raw['answer_type'],
            'title'           => $raw['title'] ?? null,
            'template_text'   => $raw['template_text'],
            'hint_text'       => $raw['hint_text'] ?? null,
            'correct_formula' => $raw['correct_formula'],
            'num_config'      => $numConfig,
            'distractors'     => $distractors,
            'conditions'      => $conditions,
        ];
    }
}
