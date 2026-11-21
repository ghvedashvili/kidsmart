<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\QuestionTemplate;
use App\Models\Theme;
use App\Models\Topic;
use App\Services\TeacherQuestionBuilder;
use Illuminate\Http\Request;

/**
 * მასწავლებლის კითხვები: შექმნა (მარტივი ფორმა) → ადმინთან განხილვაზე → დადასტურებული ან დაბრუნებული კომენტარით.
 * მასწავლებელი ხედავს მხოლოდ საკუთარ კითხვებს; დადასტურებულს ვეღარ ცვლის.
 */
class TeacherQuestionController extends Controller
{
    private const TABS = ['rejected', 'pending', 'approved'];

    public function __construct()
    {
        // მასწავლებლის გვერდები: მასწავლებელი (და ადმინი — გადასახედად)
        $this->middleware(function ($request, $next) {
            abort_unless(in_array(auth()->user()?->role, ['teacher', 'admin'], true), 403, 'ეს გვერდი მასწავლებლებისთვისაა.');
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $uid = auth()->id();
        $counts = QuestionTemplate::where('created_by', $uid)
            ->selectRaw('review_status, count(*) c')->groupBy('review_status')->pluck('c', 'review_status');
        $tab = $request->query('tab');
        if (! in_array($tab, self::TABS, true)) $tab = ($counts['rejected'] ?? 0) > 0 ? 'rejected' : 'pending';

        $questions = QuestionTemplate::where('created_by', $uid)
            ->where('review_status', $tab)
            ->with(['topic.grade', 'reviewer:id,name'])
            ->orderByDesc($tab === 'pending' ? 'submitted_at' : 'reviewed_at')
            ->paginate(20)->withQueryString();

        return view('teacher.questions.index', compact('questions', 'counts', 'tab'));
    }

    public function create()
    {
        return view('teacher.questions.form', $this->formData(new QuestionTemplate()));
    }

    public function store(Request $request, TeacherQuestionBuilder $builder)
    {
        [$input, $built] = $this->validateAndBuild($request, $builder);
        if ($built['error']) return back()->withInput()->withErrors(['text' => $built['error']]);

        // განხილვის ველები ყოველთვის ჩვენია (attrs-ში is_active=true შეიძლება იყოს)
        QuestionTemplate::create(array_merge($built['attrs'], [
            'created_by'    => auth()->id(),
            'review_status' => QuestionTemplate::REVIEW_PENDING,
            'submitted_at'  => now(),
            'is_active'     => false,
            'teacher_data'  => $this->teacherData($input, $built),
        ]));

        return redirect()->route('teacher.questions.index', ['tab' => 'pending'])
            ->with('success', 'კითხვა გაიგზავნა ადმინთან. დადასტურების შემდეგ ბავშვებს გამოუჩნდებათ.');
    }

    public function edit(QuestionTemplate $question)
    {
        $this->authorizeOwn($question, editable: true);
        return view('teacher.questions.form', $this->formData($question));
    }

    public function update(Request $request, QuestionTemplate $question, TeacherQuestionBuilder $builder)
    {
        $this->authorizeOwn($question, editable: true);
        [$input, $built] = $this->validateAndBuild($request, $builder);
        if ($built['error']) return back()->withInput()->withErrors(['text' => $built['error']]);

        // ძველი ფორმულის/ცვლადების ნარჩენი არ დარჩეს
        $question->fill(array_merge(['num_config' => [], 'conditions' => [], 'distractors' => []], $built['attrs']));
        $question->fill([
            'review_status' => QuestionTemplate::REVIEW_PENDING,
            'submitted_at'  => now(),
            'is_active'     => false,
            'teacher_data'  => $this->teacherData($input, $built, $question),
        ])->save();

        return redirect()->route('teacher.questions.index', ['tab' => 'pending'])
            ->with('success', 'შესწორებული კითხვა ხელახლა გაიგზავნა ადმინთან.');
    }

    public function show(QuestionTemplate $question, TeacherQuestionBuilder $builder)
    {
        $this->authorizeOwn($question);
        $question->load(['topic.grade', 'theme', 'reviewer:id,name']);
        $samples = $builder->samples($question->only($question->getFillable()), $question->theme);
        return view('teacher.questions.show', compact('question', 'samples'));
    }

    public function destroy(QuestionTemplate $question)
    {
        $this->authorizeOwn($question, editable: true);
        $question->forceDelete();
        return redirect()->route('teacher.questions.index')->with('success', 'კითხვა წაიშალა.');
    }

    /** ფორმის „ნახე როგორ დაინახავს ბავშვი“ — 3 ვარიანტი (JSON) */
    public function preview(Request $request, TeacherQuestionBuilder $builder)
    {
        [, $built] = $this->validateAndBuild($request, $builder, forPreview: true);
        if ($built['error']) return response()->json(['error' => $built['error'], 'wizard' => $built['wizard'] ?? null], 422);
        return response()->json([
            'samples'   => $built['samples'],
            'auto_used' => $built['auto_used'],
            'wizard'    => $built['wizard'] ?? null,
            'type'      => $built['attrs']['answer_type'] ?? null,
            'notes'     => array_values(array_filter($built['notes'], fn ($n) => str_contains($n, 'ვერ') || str_contains($n, 'ავტომატურად შეირჩევა'))),
        ]);
    }

    // ─────────────────────────── დამხმარეები ───────────────────────────

    private function validateAndBuild(Request $request, TeacherQuestionBuilder $builder, bool $forPreview = false): array
    {
        $data = $request->validate([
            'topic_id'    => ($forPreview ? 'nullable' : 'required') . '|integer|exists:topics,id',
            'theme_id'    => 'nullable|integer|exists:themes,id',
            'difficulty'  => 'nullable|integer|min:1|max:5',
            'text'        => 'required|string|max:2000',
            'answer'      => 'required|string|max:200',
            'wrong'       => 'nullable|array|max:5',
            'wrong.*'     => 'nullable|string|max:200',
            'mode'        => 'required|in:choice,input',
            'auto'        => 'nullable|boolean',
            'hint'        => 'nullable|string|max:500',
            'is_olympiad' => 'nullable|boolean',
            'formula'     => 'nullable|string|max:300',
            'ranges'      => 'nullable|array|max:20',
            'ranges.*.min'=> 'nullable|integer|min:0|max:100000',
            'ranges.*.max'=> 'nullable|integer|min:0|max:100000',
        ], [
            'topic_id.required' => 'აირჩიე კლასი და თემა.',
            'text.required'     => 'ჩაწერე კითხვის ტექსტი.',
            'answer.required'   => 'ჩაწერე სწორი პასუხი.',
        ]);
        $input = [
            'text'        => $data['text'],
            'answer'      => $data['answer'],
            'wrong'       => array_values((array) ($data['wrong'] ?? [])),
            'mode'        => $data['mode'],
            'auto'        => $request->boolean('auto'),
            'hint'        => $data['hint'] ?? null,
            'difficulty'  => (int) ($data['difficulty'] ?? 1),
            'topic_id'    => (int) ($data['topic_id'] ?? Topic::value('id')),
            'theme_id'    => $data['theme_id'] ?? $this->defaultThemeId(),
            'is_olympiad' => $request->boolean('is_olympiad'),
            'formula'     => $request->boolean('auto') ? ($data['formula'] ?? null) : null,
            'ranges'      => $request->boolean('auto') ? (array) ($data['ranges'] ?? []) : [],
        ];
        return [$input, $builder->build($input)];
    }

    private function teacherData(array $input, array $built, ?QuestionTemplate $old = null): array
    {
        return [
            'text'      => $input['text'],
            'answer'    => $input['answer'],
            'wrong'     => array_values(array_filter($input['wrong'], fn ($w) => trim((string) $w) !== '')),
            'mode'      => $input['mode'],
            'auto'      => $input['auto'],
            'hint'      => $input['hint'],
            'auto_used' => $built['auto_used'],
            'notes'     => $built['notes'],
            // მასწავლებლის შესწორებები ოსტატის შეთავაზებაზე (რედაქტირებისას აღდგება)
            'formula'   => $input['formula'] ?: null,
            'ranges'    => $input['ranges'] ?: null,
            // წინა დაბრუნების კომენტარი — ადმინმა იცოდეს, რა სთხოვა
            'previous_comment' => $old?->review_status === QuestionTemplate::REVIEW_REJECTED ? $old->review_comment : ($old?->teacher_data['previous_comment'] ?? null),
        ];
    }

    private function authorizeOwn(QuestionTemplate $q, bool $editable = false): void
    {
        abort_unless($q->created_by === auth()->id(), 403, 'ეს კითხვა სხვა მასწავლებელს ეკუთვნის.');
        if ($editable) abort_if($q->review_status === QuestionTemplate::REVIEW_APPROVED, 403, 'დადასტურებული კითხვის შეცვლა აღარ შეიძლება.');
    }

    private function formData(QuestionTemplate $q): array
    {
        $grades = Grade::where('is_active', true)->orderBy('number')
            ->with(['topics' => fn ($t) => $t->where('is_active', true)->orderBy('sequence')])->get();
        return [
            'question' => $q,
            'td'       => (array) ($q->teacher_data ?? []),
            'grades'   => $grades,
            'themes'   => Theme::where('is_active', true)->orderBy('name')->get(),
            'defaultThemeId' => $this->defaultThemeId(),
        ];
    }

    private function defaultThemeId(): ?int
    {
        return Theme::where('name', 'სტანდარტი')->value('id') ?? Theme::where('is_active', true)->orderBy('id')->value('id');
    }
}
