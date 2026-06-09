<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
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
            'theme'     => $theme->load('variables'),
        ]);
    }

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
            ['values' => $values]
        );

        return back()->with('success', 'ცვლადი შეინახა');
    }

    public function destroyVariable(ThemeVariable $variable)
    {
        $themeId = $variable->theme_id;
        $variable->delete();
        return redirect()->route('admin.themes.variables', $themeId)->with('success', 'წაიშალა');
    }
}
