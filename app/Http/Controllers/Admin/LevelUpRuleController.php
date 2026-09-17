<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\LevelUpRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LevelUpRuleController extends Controller
{
    public function index()
    {
        $rows = LevelUpRule::with('grade')->orderBy('grade_id')->get();

        return view('admin.level-up-rules.index', [
            'rows'   => $rows,
            'grades' => Grade::orderBy('number')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'grade_id'        => ['required', Rule::exists('grades', 'id'), Rule::unique('level_up_rules', 'grade_id')],
            'tests_required'  => 'required|integer|min:1|max:50',
            'up_threshold'    => 'required|integer|min:1|max:100',
            'down_threshold'  => 'required|integer|min:0|max:99',
        ]);

        if ($data['down_threshold'] >= $data['up_threshold']) {
            return back()->withErrors(['down_threshold' => 'დაწევის % უნდა იყოს ასაწევ %-ზე დაბალი'])->withInput();
        }

        LevelUpRule::create($data);

        return back()->with('success', 'წესი დაემატა');
    }

    public function update(Request $request, LevelUpRule $levelUpRule)
    {
        $data = $request->validate([
            'tests_required' => 'required|integer|min:1|max:50',
            'up_threshold'   => 'required|integer|min:1|max:100',
            'down_threshold' => 'required|integer|min:0|max:99',
        ]);

        if ($data['down_threshold'] >= $data['up_threshold']) {
            return back()->withErrors(['down_threshold' => 'დაწევის % უნდა იყოს ასაწევ %-ზე დაბალი'])->withInput();
        }

        $levelUpRule->update($data);

        return back()->with('success', 'განახლდა');
    }

    public function destroy(LevelUpRule $levelUpRule)
    {
        $levelUpRule->delete();

        return back()->with('success', 'წაიშალა — ეს კლასი ნაგულისხმევ მნიშვნელობებზე გადავა');
    }
}
