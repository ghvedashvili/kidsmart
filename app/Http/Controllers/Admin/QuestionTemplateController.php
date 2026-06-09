<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\QuestionTemplate;
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
            'templates'  => $query->latest()->paginate(20),
            'grades'     => Grade::orderBy('number')->get(),
            'topics'     => Topic::with('grade')->orderBy('grade_id')->get(),
            'filters'    => $request->only('grade_id', 'topic_id', 'difficulty'),
        ]);
    }

    public function create()
    {
        return view('admin.questions.form', [
            'template' => null,
            'grades'   => Grade::orderBy('number')->get(),
            'topics'   => Topic::with('grade')->orderBy('grade_id')->get(),
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
            'template' => $question,
            'grades'   => Grade::orderBy('number')->get(),
            'topics'   => Topic::with('grade')->orderBy('grade_id')->get(),
        ]);
    }

    public function update(Request $request, QuestionTemplate $question)
    {
        $question->update($this->validated($request));
        return redirect()->route('admin.questions.index')->with('success', 'განახლდა');
    }

    public function destroy(QuestionTemplate $question)
    {
        $question->delete();
        return back()->with('success', 'წაიშალა');
    }

    private function validated(Request $request): array
    {
        $raw = $request->validate([
            'topic_id'        => 'required|exists:topics,id',
            'difficulty'      => 'required|integer|min:1|max:5',
            'template_text'   => 'required|string',
            'correct_formula' => 'required|string|max:200',
            'num_config'      => 'required|string',
            'distractors'     => 'nullable|string',
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
            if (! is_array($distractors)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'distractors' => 'JSON ფორმატი არასწორია',
                ]);
            }
        }

        return [
            'topic_id'        => $raw['topic_id'],
            'difficulty'      => $raw['difficulty'],
            'template_text'   => $raw['template_text'],
            'correct_formula' => $raw['correct_formula'],
            'num_config'      => $numConfig,
            'distractors'     => $distractors,
        ];
    }
}
