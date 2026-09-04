<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\TestQuestionCount;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TestQuestionCountController extends Controller
{
    public function index()
    {
        $rows = TestQuestionCount::with(['grade', 'theme'])
            ->orderBy('grade_id')->orderBy('difficulty')->orderBy('theme_id')
            ->get();

        return view('admin.test-question-counts.index', [
            'rows'   => $rows,
            'grades' => Grade::orderBy('number')->get(),
            'themes' => Theme::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'grade_id'        => ['required', Rule::exists('grades', 'id')],
            'difficulty'      => 'required|integer|min:1|max:3',
            'theme_id'        => ['nullable', Rule::exists('themes', 'id')],
            'questions_count' => 'required|integer|min:1|max:100',
        ]);

        $exists = TestQuestionCount::where('grade_id', $data['grade_id'])
            ->where('difficulty', $data['difficulty'])
            ->where('theme_id', $data['theme_id'] ?? null)
            ->exists();

        if ($exists) {
            return back()->withErrors(['grade_id' => 'ეს კომბინაცია უკვე დამატებულია — შეასწორე არსებული ჩანაწერი'])->withInput();
        }

        TestQuestionCount::create($data);

        return back()->with('success', 'წესი დაემატა');
    }

    public function update(Request $request, TestQuestionCount $testQuestionCount)
    {
        $data = $request->validate([
            'questions_count' => 'required|integer|min:1|max:100',
        ]);

        $testQuestionCount->update($data);

        return back()->with('success', 'განახლდა');
    }

    public function destroy(TestQuestionCount $testQuestionCount)
    {
        $testQuestionCount->delete();

        return back()->with('success', 'წაიშალა');
    }
}
