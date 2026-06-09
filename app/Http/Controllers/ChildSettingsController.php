<?php

namespace App\Http\Controllers;

use App\Models\ChildSetting;
use App\Models\Grade;
use App\Models\Theme;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;

class ChildSettingsController extends Controller
{
    private function authorizeChild(User $child): void
    {
        abort_if(
            $child->parent_id !== auth()->id() || $child->role !== 'child',
            403
        );
    }

    public function edit(User $child)
    {
        $this->authorizeChild($child);

        $setting  = $child->childSetting ?? new ChildSetting(['difficulty' => 1, 'tests_per_week' => 3]);
        $grades   = Grade::orderBy('number')->get();
        $themes   = Theme::all();
        $topics   = Topic::with('grade')->orderBy('grade_id')->get();

        $selectedThemes = $child->themes->pluck('id')->toArray();
        $selectedTopics = $child->topics->pluck('id')->toArray();

        return view('parent.child-settings', compact(
            'child', 'setting', 'grades', 'themes', 'topics',
            'selectedThemes', 'selectedTopics'
        ));
    }

    public function update(Request $request, User $child)
    {
        $this->authorizeChild($child);

        $data = $request->validate([
            'grade_id'      => 'nullable|exists:grades,id',
            'difficulty'    => 'required|integer|min:1|max:5',
            'tests_per_week'=> 'required|integer|min:1|max:7',
            'theme_ids'     => 'nullable|array',
            'theme_ids.*'   => 'exists:themes,id',
            'topic_ids'     => 'nullable|array',
            'topic_ids.*'   => 'exists:topics,id',
        ]);

        ChildSetting::updateOrCreate(
            ['user_id' => $child->id],
            [
                'grade_id'       => $data['grade_id'] ?? null,
                'difficulty'     => $data['difficulty'],
                'tests_per_week' => $data['tests_per_week'],
            ]
        );

        $child->themes()->sync($data['theme_ids'] ?? []);
        $child->topics()->sync($data['topic_ids'] ?? []);

        return redirect()->route('dashboard')->with('success', $child->name . '-ის პარამეტრები შეინახა');
    }
}
