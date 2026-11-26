<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\QuestionTemplate;
use App\Models\Theme;
use App\Services\TeacherQuestionBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * კითხვების ბანკი მასწავლებლისთვის: ყველა კითხვა, რასაც ბავშვები ხედავენ.
 * მასწავლებელს შეუძლია ნახოს როგორ დაინახავს ბავშვი (ახალი ვარიანტის გენერაციით) და
 * შეცდომიანი კითხვა გაუგზავნოს ადმინს კომენტარით — ასეთი კითხვა მაშინვე ითიშება
 * (ტესტები, ვარჯიში, ოლიმპიადა, დაუსრულებელი ტესტები) ადმინის გადაწყვეტილებამდე.
 */
class TeacherBankController extends Controller
{
    /** ამ ტიპებს ბავშვი ინტერაქტიულად ხედავს — მარტივ ბარათზე ვერ ვაჩვენებთ */
    private const SPECIAL = TeacherQuestionBuilder::INTERACTIVE;
    private const VIEWS = ['all', 'flagged', 'mine'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(in_array(auth()->user()?->role, ['teacher', 'admin'], true), 403, 'ეს გვერდი მასწავლებლებისთვისაა.');
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $view = in_array($request->query('view'), self::VIEWS, true) ? $request->query('view') : 'all';
        $f = $request->only('grade_id', 'topic_id', 'difficulty', 'search');

        $query = match ($view) {
            'flagged' => QuestionTemplate::where('review_status', QuestionTemplate::REVIEW_FLAGGED),
            'mine'    => QuestionTemplate::where('flagged_by', auth()->id()),
            default   => $this->bankScope(QuestionTemplate::query()),
        };
        $query->with(['topic.grade', 'theme', 'flagger:id,name']);

        if (! empty($f['grade_id']))  $query->whereHas('topic', fn ($q) => $q->where('grade_id', $f['grade_id']));
        if (! empty($f['topic_id']))  $query->where('question_templates.topic_id', $f['topic_id']);
        if (($f['difficulty'] ?? '') === 'olympiad') {
            $query->where('question_templates.is_olympiad', true);
        } elseif (! empty($f['difficulty'])) {
            $query->where('question_templates.difficulty', (int) $f['difficulty'])->where('question_templates.is_olympiad', false);
        }
        if (! empty($f['search'])) {
            $s = $f['search'];
            $query->where(fn ($q) => $q->where('question_templates.template_text', 'like', "%{$s}%")->orWhere('question_templates.title', 'like', "%{$s}%"));
        }

        if ($view === 'all') {
            $query->join('topics', 'topics.id', '=', 'question_templates.topic_id')
                ->orderBy('topics.grade_id')
                ->orderByRaw('topics.sequence IS NULL, topics.sequence')
                ->orderBy('question_templates.difficulty')
                ->orderBy('question_templates.id')
                ->select('question_templates.*');
        } else {
            $query->orderByDesc('flagged_at');
        }
        $questions = $query->paginate(20)->withQueryString();

        // თითო კითხვის ერთი ნიმუში — ბანკში ტექსტი ისე ჩანდეს, როგორც ბავშვს (ცვლადების ნაცვლად რიცხვები)
        $preview = [];
        foreach ($questions as $q) {
            $preview[$q->id] = $this->sampleText($q);
        }

        $counts = [
            'flagged' => QuestionTemplate::where('review_status', QuestionTemplate::REVIEW_FLAGGED)->count(),
            'mine'    => QuestionTemplate::where('flagged_by', auth()->id())->count(),
        ];
        $grades = Grade::where('is_active', true)->orderBy('number')
            ->with(['topics' => fn ($t) => $t->where('is_active', true)->orderBy('sequence')])->get();

        return view('teacher.bank.index', compact('questions', 'preview', 'counts', 'grades', 'view', 'f'));
    }

    public function show(QuestionTemplate $question, TeacherQuestionBuilder $builder)
    {
        $this->authorizeVisible($question);
        $question->load(['topic.grade', 'theme', 'flagger:id,name', 'reviewer:id,name']);

        $special = self::SPECIAL[$question->question_type ?? ''] ?? null;
        // ინტერაქტიული კითხვა (პირამიდა, კოდი…) — სრული გენერაცია და იგივე რენდერი, რაც ტესტის გადახედვაში
        $samples = $special
            ? $builder->interactiveSamples($question, 3)
            : $builder->samples($question->only($question->getFillable()), $question->theme ?? Theme::first(), 3);

        return view('teacher.bank.show', [
            'question' => $question,
            'samples'  => $samples,
            'special'  => $special,
            'td'       => (array) ($question->teacher_data ?? []),
        ]);
    }

    /** „შეცდომაა — გაუგზავნე ადმინს“: კითხვა ითიშება ყველგან და ადმინის განსახილველში ხვდება */
    public function flag(Request $request, QuestionTemplate $question)
    {
        $this->authorizeVisible($question);
        $comment = $this->commentInput($request, 'დაწერე, რა არის შესასწორებელი — ადმინმა უნდა იცოდეს, რა გაასწოროს.');

        if ($question->review_status === QuestionTemplate::REVIEW_FLAGGED) {
            return $this->addComment($question, $comment, 'flagged');
        }
        abort_unless($question->is_active, 422, 'ეს კითხვა ბავშვებს ისედაც არ უჩანთ.');

        $u  = auth()->user();
        $td = (array) ($question->teacher_data ?? []);
        $td['flag'] = [
            'prev_status' => $question->review_status,
            'by' => $u->id, 'name' => $u->name, 'at' => now()->toDateTimeString(), 'comment' => $comment,
        ];
        $td['history'] = $this->pushHistory($td, 'flagged', $comment);

        $question->fill([
            'review_status' => QuestionTemplate::REVIEW_FLAGGED,
            'is_active'     => false,
            'flagged_by'    => $u->id,
            'flagged_at'    => now(),
            'teacher_data'  => $td,
        ])->save();

        $tests = $question->withdrawFromOpenTests();

        return redirect()->route('teacher.bank.show', $question)->with('success',
            'კითხვა გამოირთო და გაიგზავნა ადმინთან.' . ($tests ? " ამოღებულია {$tests} დაუსრულებელი ტესტიდანაც." : ''));
    }

    /** დამატებითი კომენტარი უკვე გაგზავნილ კითხვაზე */
    public function comment(Request $request, QuestionTemplate $question)
    {
        $this->authorizeVisible($question);
        abort_unless($question->review_status === QuestionTemplate::REVIEW_FLAGGED || $question->flagged_by === auth()->id(), 403);
        return $this->addComment($question, $this->commentInput($request), 'comment');
    }

    // ─────────────────────────── დამხმარეები ───────────────────────────

    /** ბანკი: ბავშვებთან ჩართული კითხვები + შენიშვნიანი (გამორთული) კითხვები; სხვა მასწავლებლის განსახილველი — არა */
    private function bankScope(Builder $q): Builder
    {
        return $q->where(function ($w) {
            $w->where(fn ($a) => $a->where('question_templates.is_active', true)
                    ->where(fn ($s) => $s->whereNull('question_templates.review_status')->orWhere('question_templates.review_status', QuestionTemplate::REVIEW_APPROVED)))
              ->orWhere('question_templates.review_status', QuestionTemplate::REVIEW_FLAGGED);
        });
    }

    private function authorizeVisible(QuestionTemplate $q): void
    {
        $ok = $q->review_status === QuestionTemplate::REVIEW_FLAGGED
            || $q->flagged_by === auth()->id()
            || ($q->is_active && in_array($q->review_status, [null, QuestionTemplate::REVIEW_APPROVED], true));
        abort_unless($ok, 404);
    }

    private function commentInput(Request $request, string $msg = 'ჩაწერე კომენტარი.'): string
    {
        return trim($request->validate(['comment' => 'required|string|max:1000'], ['comment.required' => $msg])['comment']);
    }

    private function addComment(QuestionTemplate $q, string $text, string $event)
    {
        $td = (array) ($q->teacher_data ?? []);
        $td['history'] = $this->pushHistory($td, $event === 'flagged' ? 'comment' : $event, $text);
        // შენახვა is_active-ს არ ეხება (მასწავლებლის შენახვისას მოდელი მაინც არ ჩართავს)
        $q->forceFill(['teacher_data' => $td])->saveQuietly();
        return redirect()->route('teacher.bank.show', $q)->with('success', 'კომენტარი დაემატა.');
    }

    private function pushHistory(array $td, string $event, ?string $text): array
    {
        return array_slice(array_merge((array) ($td['history'] ?? []), [[
            'by' => 'teacher', 'name' => auth()->user()->name, 'at' => now()->toDateTimeString(), 'event' => $event, 'text' => $text,
        ]]), -30);
    }

    private function sampleText(QuestionTemplate $q): array
    {
        if ($label = self::SPECIAL[$q->question_type ?? ''] ?? null) {
            return ['text' => $q->title ?: $label, 'answer' => null, 'special' => $label];
        }
        try {
            $g = $q->generate($q->theme ?? Theme::first());
            return ['text' => (string) ($g['question_text'] ?? ''), 'answer' => (string) ($g['correct_answer'] ?? ''), 'special' => null];
        } catch (\Throwable $e) {
            return ['text' => $q->template_text, 'answer' => null, 'special' => null, 'broken' => true];
        }
    }
}
