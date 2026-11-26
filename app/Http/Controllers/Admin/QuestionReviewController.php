<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionTemplate;
use App\Services\TeacherQuestionBuilder;
use Illuminate\Http\Request;

/**
 * მასწავლებლების კითხვების განხილვა: დადასტურება (→ ბავშვებს ჩაერთვება) ან დაბრუნება კომენტარით.
 * მხოლოდ ადმინისთვის.
 */
class QuestionReviewController extends Controller
{
    public function index(Request $request, TeacherQuestionBuilder $builder)
    {
        $this->onlyAdmin();
        $status = match ($request->query('status')) {
            'rejected' => QuestionTemplate::REVIEW_REJECTED,
            'flagged'  => QuestionTemplate::REVIEW_FLAGGED,
            default    => QuestionTemplate::REVIEW_PENDING,
        };
        $questions = QuestionTemplate::where('review_status', $status)
            ->with(['topic.grade', 'theme', 'creator:id,name,email', 'flagger:id,name'])
            ->orderBy($status === QuestionTemplate::REVIEW_FLAGGED ? 'flagged_at' : 'submitted_at')
            ->paginate(15)->withQueryString();

        $samples = [];
        foreach ($questions as $q) {
            $samples[$q->id] = isset(TeacherQuestionBuilder::INTERACTIVE[$q->question_type ?? ''])
                ? $builder->interactiveSamples($q, 2)
                : $builder->samples($q->only($q->getFillable()), $q->theme);
        }
        $counts = [
            'pending'  => QuestionTemplate::where('review_status', QuestionTemplate::REVIEW_PENDING)->count(),
            'rejected' => QuestionTemplate::where('review_status', QuestionTemplate::REVIEW_REJECTED)->count(),
            'flagged'  => QuestionTemplate::where('review_status', QuestionTemplate::REVIEW_FLAGGED)->count(),
        ];
        return view('admin.questions.review', compact('questions', 'samples', 'status', 'counts'));
    }

    public function approve(Request $request, QuestionTemplate $question)
    {
        $this->onlyAdmin();
        $comment = $request->filled('comment') ? trim((string) $request->input('comment')) : null;
        $question->fill([
            'review_status'  => QuestionTemplate::REVIEW_APPROVED,
            'review_comment' => $comment,
            'teacher_data'   => $this->withHistory($question, 'approved', $comment),
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
            'is_active'      => true,
        ])->save();
        return back()->with('success', '„' . ($question->title ?: 'კითხვა') . '“ დადასტურდა და ბავშვებს გამოუჩნდებათ.');
    }

    public function reject(Request $request, QuestionTemplate $question)
    {
        $this->onlyAdmin();
        $data = $request->validate(['comment' => 'required|string|max:1000'], ['comment.required' => 'დაწერე კომენტარი — მასწავლებელმა უნდა იცოდეს, რა შეასწოროს.']);
        $question->fill([
            'review_status'  => QuestionTemplate::REVIEW_REJECTED,
            'review_comment' => trim($data['comment']),
            'teacher_data'   => $this->withHistory($question, 'rejected', trim($data['comment'])),
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
            'is_active'      => false,
        ])->save();
        return back()->with('success', 'კითხვა დაუბრუნდა მასწავლებელს კომენტარით.');
    }

    /**
     * მასწავლებლის შენიშვნიანი კითხვა: ადმინმა გაასწორა (ან შეცდომა არ აღმოჩნდა) → ისევ ჩაირთოს ბავშვებთან,
     * წინა სტატუსით. მასწავლებელი შედეგს „ჩემს შენიშვნებში“ ნახავს.
     */
    public function resolve(Request $request, QuestionTemplate $question)
    {
        $this->onlyAdmin();
        abort_unless($question->review_status === QuestionTemplate::REVIEW_FLAGGED, 422, 'ეს კითხვა შენიშვნიანი აღარ არის.');
        $comment = $request->filled('comment') ? trim((string) $request->input('comment')) : null;
        $td = (array) ($question->teacher_data ?? []);
        $prev = $td['flag']['prev_status'] ?? null;
        $td['flag'] = array_merge((array) ($td['flag'] ?? []), ['resolved_at' => now()->toDateTimeString(), 'resolved_by' => auth()->user()->name]);
        $question->teacher_data = $td;
        $question->fill([
            'review_status' => $prev,   // ჯერ სტატუსი — რომ მოდელმა is_active-ის ჩართვისას „approved“ არ დაუწეროს
            'teacher_data'  => $this->withHistory($question, 'resolved', $comment),
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
        ]);
        $question->is_active = true;
        $question->save();
        return back()->with('success', '„' . ($question->title ?: 'კითხვა') . '“ ისევ ჩაირთო ბავშვებთან.');
    }

    /** teacher_data.history-ში ადმინის გადაწყვეტილების ჩამატება */
    private function withHistory(QuestionTemplate $q, string $event, ?string $text): array
    {
        $td = (array) ($q->teacher_data ?? []);
        $td['history'] = array_slice(array_merge((array) ($td['history'] ?? []), [[
            'by' => 'admin', 'name' => auth()->user()->name, 'at' => now()->toDateTimeString(), 'event' => $event, 'text' => $text,
        ]]), -30);
        return $td;
    }

    private function onlyAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'კითხვების დადასტურება მხოლოდ ადმინს შეუძლია.');
    }
}
