<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GameController;
use App\Models\GameSession;
use App\Models\GameSetting;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** თამაშების მართვა: ჩართვა/გათიშვა და რომელ კლასებს უჩანს */
class GameSettingController extends Controller
{
    public function index()
    {
        $settings = GameSetting::map();
        $plays = GameSession::selectRaw('game, count(*) c')->groupBy('game')->pluck('c', 'game');
        $games = collect(GameController::GAMES)->map(fn ($g, $slug) => $g + [
            'slug'      => $slug,
            'is_active' => $settings[$slug]->is_active ?? true,
            'grade_ids' => array_map('intval', (array) ($settings[$slug]->grade_ids ?? [])),
            'players'   => (int) ($plays[$slug] ?? 0),
        ]);
        return view('admin.games.index', [
            'games'  => $games,
            'grades' => Grade::orderBy('number')->get(),
        ]);
    }

    public function update(Request $request, string $slug)
    {
        abort_unless(array_key_exists($slug, GameController::GAMES), 404);
        $data = $request->validate([
            'is_active'   => 'nullable|boolean',
            'grade_ids'   => 'nullable|array',
            'grade_ids.*' => ['integer', Rule::exists('grades', 'id')],
        ]);
        $ids = array_values(array_unique(array_map('intval', (array) ($data['grade_ids'] ?? []))));
        GameSetting::updateOrCreate(['slug' => $slug], [
            'is_active' => $request->boolean('is_active'),
            'grade_ids' => $ids ?: null,
        ]);
        return back()->with('success', '„' . GameController::GAMES[$slug]['name'] . '“ — შენახულია.');
    }
}
