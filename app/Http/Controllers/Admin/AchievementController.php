<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AchievementController extends Controller
{
    public function index()
    {
        return view('admin.achievements.index', [
            'achievements' => Achievement::with(['tiers', 'theme'])->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.achievements.form', [
            'achievement' => null,
            'conditionTypes' => Achievement::CONDITION_TYPES,
            'binaryTypes' => Achievement::BINARY_TYPES,
            'themes' => Theme::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $tiers = $data['tiers'];
        unset($data['tiers']);

        $achievement = Achievement::create($data);
        $this->syncTiers($achievement, $tiers, $request);

        return redirect()->route('admin.achievements.index')->with('success', 'მიღწევა დაემატა');
    }

    public function edit(Achievement $achievement)
    {
        $achievement->load('tiers');

        return view('admin.achievements.form', [
            'achievement' => $achievement,
            'conditionTypes' => Achievement::CONDITION_TYPES,
            'binaryTypes' => Achievement::BINARY_TYPES,
            'themes' => Theme::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Achievement $achievement)
    {
        $data = $this->validated($request, $achievement);
        $tiers = $data['tiers'];
        unset($data['tiers']);

        $achievement->update($data);
        $this->syncTiers($achievement, $tiers, $request);

        return redirect()->route('admin.achievements.index')->with('success', 'მიღწევა განახლდა');
    }

    public function destroy(Achievement $achievement)
    {
        foreach ($achievement->tiers as $tier) {
            if ($tier->image_path) {
                Storage::disk('public')->delete($tier->image_path);
            }
        }
        $achievement->delete();

        return back()->with('success', 'წაიშალა');
    }

    public function toggleActive(Achievement $achievement)
    {
        $achievement->update(['is_active' => ! $achievement->is_active]);

        return back()->with('success', $achievement->name . ($achievement->is_active ? ' გააქტიურდა' : ' გაითიშა'));
    }

    private function validated(Request $request, ?Achievement $achievement = null): array
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'slug'             => [
                'required', 'string', 'max:60', 'alpha_dash',
                Rule::unique('achievements', 'slug')->ignore($achievement?->id),
            ],
            'theme_id'         => ['nullable', Rule::exists('themes', 'id')],
            'description'      => 'nullable|string|max:200',
            'condition_type'   => ['required', Rule::in(array_keys(Achievement::CONDITION_TYPES))],
            'condition_before' => 'nullable|date_format:H:i',
            'condition_after'  => 'nullable|date_format:H:i',
            'daily_limit'      => 'nullable|boolean',
            'is_active'        => 'nullable|boolean',
            'tiers'            => 'required|array|min:1',
            'tiers.*.label'    => 'required|string|max:80',
            'tiers.*.threshold' => 'nullable|integer|min:1',
            'tiers.*.image'    => 'nullable|image|max:2048',
        ]);

        $conditionConfig = null;
        if ($data['condition_type'] === 'time_of_day') {
            $conditionConfig = array_filter([
                'before' => $data['condition_before'] ?? null,
                'after'  => $data['condition_after'] ?? null,
            ]);
        }

        return [
            'name'             => $data['name'],
            'slug'             => Str::slug($data['slug'], '_'),
            'theme_id'         => $data['theme_id'] ?? null,
            'description'      => $data['description'] ?? null,
            'condition_type'   => $data['condition_type'],
            'condition_config' => $conditionConfig,
            'daily_limit'      => $request->boolean('daily_limit'),
            'is_active'        => $request->boolean('is_active', true),
            'tiers'            => array_values($data['tiers']),
        ];
    }

    private function syncTiers(Achievement $achievement, array $tiers, Request $request): void
    {
        $keptLevels = [];

        foreach ($tiers as $i => $tierData) {
            $level = $i + 1;
            $keptLevels[] = $level;

            $tier = $achievement->tiers()->firstOrNew(['level' => $level]);
            $tier->label     = $tierData['label'];
            $tier->threshold = in_array($achievement->condition_type, Achievement::BINARY_TYPES)
                ? null
                : ($tierData['threshold'] ?? null);

            $file = $request->file("tiers.$i.image");
            if ($file) {
                if ($tier->image_path) {
                    Storage::disk('public')->delete($tier->image_path);
                }
                $tier->image_path = $file->store('achievements', 'public');
            }

            $tier->save();
        }

        // remove tiers the admin deleted from the form
        $achievement->tiers()->whereNotIn('level', $keptLevels)->get()->each(function ($tier) {
            if ($tier->image_path) {
                Storage::disk('public')->delete($tier->image_path);
            }
            $tier->delete();
        });
    }
}
