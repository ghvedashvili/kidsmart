<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\ThemeVarGroup;
use App\Models\ThemeVariable;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function index()
    {
        return view('admin.themes.index', ['themes' => Theme::withCount('variables')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'required|string|max:10',
        ]);
        Theme::create($data);
        return back()->with('success', 'თემა დაემატა');
    }

    public function destroy(Theme $theme)
    {
        $theme->delete();
        return back()->with('success', 'წაიშალა');
    }

    public function showVariables(Theme $theme)
    {
        return view('admin.themes.variables', [
            'theme' => $theme->load(['varGroups.variables', 'variables' => fn($q) => $q->whereNull('group_id')]),
        ]);
    }

    // ── Group (pool + slots)
    public function storeGroup(Request $request, Theme $theme)
    {
        $data = $request->validate([
            'group_name' => ['required','string','max:50','regex:/^[A-Z0-9_]+$/'],
            'pool'       => 'required|string',
            'slots'      => 'required|string',
        ]);

        $poolValues = array_values(array_filter(array_map('trim', explode(',', $data['pool']))));
        if (empty($poolValues)) {
            return back()->withErrors(['pool' => 'მინიმუმ ერთი მნიშვნელობა'])->withInput();
        }

        $slotNames = array_values(array_unique(array_filter(
            array_map(fn($s) => strtoupper(preg_replace('/[^A-Z0-9_]/i', '', trim($s))), explode(',', $data['slots']))
        )));
        if (empty($slotNames)) {
            return back()->withErrors(['slots' => 'მინიმუმ ერთი სლოტი'])->withInput();
        }

        $group = ThemeVarGroup::updateOrCreate(
            ['theme_id' => $theme->id, 'name' => strtoupper($data['group_name'])],
            ['values' => $poolValues]
        );

        foreach ($slotNames as $slot) {
            ThemeVariable::firstOrCreate(
                ['theme_id' => $theme->id, 'variable_name' => $slot],
                ['group_id' => $group->id, 'values' => null]
            );
        }

        return back()->with('success', 'ჯგუფი შეინახა');
    }

    public function updateGroup(Request $request, ThemeVarGroup $group)
    {
        $data = $request->validate([
            'group_name' => ['required','string','max:50','regex:/^[A-Z0-9_]+$/'],
            'pool'       => 'required|string',
        ]);

        $poolValues = array_values(array_filter(array_map('trim', explode(',', $data['pool']))));
        if (empty($poolValues)) {
            return back()->withErrors(['pool' => 'მინიმუმ ერთი მნიშვნელობა']);
        }

        $group->update([
            'name'   => strtoupper($data['group_name']),
            'values' => $poolValues,
        ]);

        return redirect()->route('admin.themes.variables', $group->theme_id)->with('success', 'ჯგუფი განახლდა');
    }

    public function addSlot(Request $request, ThemeVarGroup $group)
    {
        $data = $request->validate([
            'slot_name' => ['required','string','max:50','regex:/^[A-Z0-9_]+$/'],
        ]);

        $slot = strtoupper($data['slot_name']);

        ThemeVariable::firstOrCreate(
            ['theme_id' => $group->theme_id, 'variable_name' => $slot],
            ['group_id' => $group->id, 'values' => null]
        );

        return redirect()->route('admin.themes.variables', $group->theme_id)->with('success', $slot . ' დაემატა');
    }

    public function destroyGroup(ThemeVarGroup $group)
    {
        $themeId = $group->theme_id;
        $group->variables()->delete();
        $group->delete();
        return redirect()->route('admin.themes.variables', $themeId)->with('success', 'ჯგუფი წაიშალა');
    }

    // ── Standalone variable
    public function storeVariable(Request $request, Theme $theme)
    {
        $data = $request->validate([
            'variable_name' => 'required|string|max:50|alpha_num',
            'values'        => 'required|string',
        ]);

        $values = array_values(array_filter(array_map('trim', explode(',', $data['values']))));
        if (empty($values)) {
            return back()->withErrors(['values' => 'მინიმუმ ერთი მნიშვნელობა']);
        }

        ThemeVariable::updateOrCreate(
            ['theme_id' => $theme->id, 'variable_name' => strtoupper($data['variable_name'])],
            ['values' => $values, 'group_id' => null]
        );

        return back()->with('success', 'ცვლადი შეინახა');
    }

    public function updateVariable(Request $request, ThemeVariable $variable)
    {
        $data = $request->validate([
            'variable_name' => ['required','string','max:50','regex:/^[A-Z0-9_]+$/'],
        ]);
        $variable->update(['variable_name' => strtoupper($data['variable_name'])]);
        return redirect()->route('admin.themes.variables', $variable->theme_id)->with('success', 'სახელი განახლდა');
    }

    public function destroyVariable(ThemeVariable $variable)
    {
        $themeId = $variable->theme_id;
        $variable->delete();
        return redirect()->route('admin.themes.variables', $themeId)->with('success', 'წაიშალა');
    }
}
