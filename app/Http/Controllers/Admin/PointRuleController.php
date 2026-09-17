<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\PointRule;
use Illuminate\Http\Request;

class PointRuleController extends Controller
{
    public function index()
    {
        $rows = PointRule::with('grade')
            ->orderByRaw('grade_id IS NULL, grade_id')
            ->orderByRaw('difficulty IS NULL, difficulty')
            ->orderBy('context')
            ->get();

        return view('admin.point-rules.index', [
            'rows'   => $rows,
            'grades' => Grade::orderBy('number')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'grade_id'            => 'nullable|exists:grades,id',
            'difficulty'          => 'nullable|integer|min:1|max:5',
            'context'             => 'required|in:' . implode(',', PointRule::CONTEXTS),
            'points_per_correct'  => 'required|integer|min:0|max:100',
        ]);

        $exists = PointRule::where('grade_id', $data['grade_id'] ?? null)
            ->where('difficulty', $data['difficulty'] ?? null)
            ->where('context', $data['context'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['context' => 'ეს კომბინაცია უკვე არსებობს'])->withInput();
        }

        PointRule::create($data);

        return back()->with('success', 'წესი დაემატა');
    }

    public function update(Request $request, PointRule $pointRule)
    {
        $data = $request->validate([
            'points_per_correct' => 'required|integer|min:0|max:100',
        ]);

        $pointRule->update($data);

        return back()->with('success', 'განახლდა');
    }

    public function destroy(PointRule $pointRule)
    {
        $pointRule->delete();

        return back()->with('success', 'წაიშალა');
    }
}
