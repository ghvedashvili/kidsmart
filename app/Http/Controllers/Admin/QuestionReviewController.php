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
        $status = $request->query('status') === 'rejected' ? QuestionTemplate::REVIEW_REJECTED : QuestionTemplate::REVIEW_PENDING;
        $questions = QuestionTemplate::where('review_status', $status)
            ->with(['topic.grade', 'theme', 'creator:id,name,email'])
            ->orderBy('submitted_at')
            ->paginate(15)->withQueryString();

        $samples = [];
        foreach ($questions as $q) {
            $samples[$q->id] = $builder->samples($q->only($q->getFillable()), $q->theme);
        }
        $counts = [
            'pending'  => QuestionTemplate::where('review_status', QuestionTemplate::REVIEW_PENDING)->count(),
            'rejected' => QuestionTemplate::where('review_status', QuestionTemplate::REVIEW_REJECTED)->count(),
        ];
        return view('admin.questions.review', compact('questions', 'samples', 'status', 'counts'));
    }

    public function approve(Request $request, QuestionTemplate $question)
    {
        $this->onlyAdmin();
        $question->fill([
            'review_status'  => QuestionTemplate::REVIEW_APPROVED,
            'review_comment' => $request->filled('comment') ? trim((string) $request->input('comment')) : null,
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
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
            'is_active'      => false,
        ])->save();
        return back()->with('success', 'კითხვა დაუბრუნდა მასწავლებელს კომენტარით.');
    }

    private function onlyAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'კითხვების დადასტურება მხოლოდ ადმინს შეუძლია.');
    }
}
