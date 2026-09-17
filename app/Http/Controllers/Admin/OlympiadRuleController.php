<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\OlympiadRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OlympiadRuleController extends Controller
{
    public function index()
    {
        return view('admin.olympiad-rules.index', [
            'global' => OlympiadRule::whereNull('grade_id')->first(),
            'rows'   => OlympiadRule::whereNotNull('grade_id')->with('grade')->orderBy('grade_id')->get(),
            'grades' => Grade::orderBy('number')->get(),
        ]);
    }

    public function updateGlobal(Request $request)
    {
        $data = $request->validate([
            'olympiad_date'   => 'nullable|date',
            'tests_required'  => 'required|integer|min:1|max:50',
            'days_window'     => 'required|integer|min:1|max:90',
            'questions_count' => 'required|integer|min:1|max:100',
        ]);

        OlympiadRule::updateOrCreate(['grade_id' => null], $data);

        return back()->with('success', 'გლობალური პარამეტრები განახლდა');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'grade_id'        => ['required', Rule::exists('grades', 'id'), Rule::unique('olympiad_rules', 'grade_id')],
            'olympiad_date'   => 'nullable|date',
            'tests_required'  => 'required|integer|min:1|max:50',
            'days_window'     => 'required|integer|min:1|max:90',
            'questions_count' => 'required|integer|min:1|max:100',
        ]);

        OlympiadRule::create($data);

        return back()->with('success', 'წესი დაემატა');
    }

    public function update(Request $request, OlympiadRule $olympiadRule)
    {
        abort_if($olympiadRule->grade_id === null, 403);

        $data = $request->validate([
            'olympiad_date'   => 'nullable|date',
            'tests_required'  => 'required|integer|min:1|max:50',
            'days_window'     => 'required|integer|min:1|max:90',
            'questions_count' => 'required|integer|min:1|max:100',
        ]);

        $olympiadRule->update($data);

        return back()->with('success', 'განახლდა');
    }

    public function destroy(OlympiadRule $olympiadRule)
    {
        abort_if($olympiadRule->grade_id === null, 403);

        $olympiadRule->delete();

        return back()->with('success', 'წაიშალა — ეს კლასი გლობალურ პარამეტრებზე გადავა');
    }
}
