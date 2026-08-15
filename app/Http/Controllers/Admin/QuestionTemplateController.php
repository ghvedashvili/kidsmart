<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\QuestionTemplate;
use App\Models\Theme;
use App\Models\ThemeVariable;
use App\Models\Topic;
use Illuminate\Http\Request;

class QuestionTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = QuestionTemplate::with('topic.grade');

        if ($request->filled('grade_id')) {
            $query->whereHas('topic', fn($q) => $q->where('grade_id', $request->grade_id));
        }
        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->topic_id);
        }
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        return view('admin.questions.index', [
            'templates'   => $query->latest()->paginate(20),
            'grades'      => Grade::orderBy('number')->get(),
            'topics'      => Topic::with('grade')->orderBy('grade_id')->get(),
            'filters'     => $request->only('grade_id', 'topic_id', 'difficulty'),
            'themeVarMap' => $this->themeVarMap(),
        ]);
    }

    public function create()
    {
        return view('admin.questions.form', [
            'template'      => null,
            'grades'        => Grade::orderBy('number')->get(),
            'topics'        => Topic::with('grade')->orderBy('grade_id')->get(),
            'themes'        => Theme::orderBy('name')->get(),
            'themeVarNames' => $this->themeVarNames(),
            'themeVarMap'   => $this->themeVarMap(),
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
            'template'      => $question->load('topic'),
            'grades'        => Grade::orderBy('number')->get(),
            'topics'        => Topic::with('grade')->orderBy('grade_id')->get(),
            'themes'        => Theme::orderBy('name')->get(),
            'themeVarNames' => $this->themeVarNames(),
            'themeVarMap'   => $this->themeVarMap(),
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
            $generated = $tpl->generate($theme);
            return [
                'num'        => $i + 1,
                'text'       => $generated['question_text'],
                'answer'     => $generated['correct_answer'],
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
        return back()->with('success', 'წაიშალა');
    }

    private function themeVarNames(): array
    {
        return ThemeVariable::distinct()->pluck('variable_name')->sort()->values()->all();
    }

    private function themeVarMap(): array
    {
        $map = [];
        ThemeVariable::all()->each(function ($tv) use (&$map) {
            $map[$tv->variable_name] = array_merge(
                $map[$tv->variable_name] ?? [],
                $tv->values ?? []
            );
        });
        return $map;
    }

    private function validated(Request $request): array
    {
        $raw = $request->validate([
            'topic_id'        => 'required|exists:topics,id',
            'theme_id'        => 'nullable|exists:themes,id',
            'difficulty'      => 'required|integer|min:1|max:5',
            'template_text'   => 'required|string',
            'hint_text'       => 'nullable|string',
            'correct_formula' => 'required|string|max:200',
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
            'template_text'   => $raw['template_text'],
            'hint_text'       => $raw['hint_text'] ?? null,
            'correct_formula' => $raw['correct_formula'],
            'num_config'      => $numConfig,
            'distractors'     => $distractors,
            'conditions'      => $conditions,
        ];
    }
}
