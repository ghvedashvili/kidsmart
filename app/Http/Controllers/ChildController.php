<?php

namespace App\Http\Controllers;

use App\Models\ChildSetting;
use App\Models\Grade;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Rule;
use Illuminate\Support\Str;

class ChildController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:50',
            'grade_id'       => ['required', Rule::exists('grades', 'id')->where('is_active', true)],
            'difficulty'     => 'nullable|integer|min:1|max:5',
            'tests_per_week' => 'nullable|integer|min:1|max:5',
            'theme_ids'      => 'nullable|array',
            'theme_ids.*'    => 'exists:themes,id',
            'topic_ids'      => 'nullable|array',
            'topic_ids.*'    => 'exists:topics,id',
        ]);

        $parent  = auth()->user();
        $pkg     = $parent->currentPackage();
        $current = $parent->children()->count();

        if ($pkg->max_children > 0 && $current >= $pkg->max_children) {
            return back()->withErrors(['name' => 'თქვენი პლანი მხოლოდ ' . $pkg->max_children . ' ბავშვს იძლევა. გეგმის შესაცვლელად მიმართეთ ადმინს.']);
        }

        $child_code = $this->uniqueCode();

        $child = User::create([
            'name'       => trim($data['name']),
            'email'      => 'child_' . $parent->id . '_' . $child_code . '@kidsmart.local',
            'password'   => bcrypt(Str::random(16)),
            'role'       => 'child',
            'parent_id'  => $parent->id,
            'child_code' => $child_code,
        ]);

        $parent->children()->attach($child->id);

        ChildSetting::create([
            'user_id'        => $child->id,
            'grade_id'       => $data['grade_id'],
            'difficulty'     => $data['difficulty'] ?? 1,
            'tests_per_week' => $data['tests_per_week'] ?? 3,
        ]);

        $themeIds = $data['theme_ids'] ?? [];
        if (empty($themeIds)) {
            $defaultId = Theme::where('name', 'სტანდარტი')->value('id');
            if ($defaultId) $themeIds = [$defaultId];
        }
        if (! empty($themeIds)) {
            $child->themes()->sync($themeIds);
        }
        if (! empty($data['topic_ids'])) {
            $child->topics()->sync($data['topic_ids']);
        }

        return back()->with('child_added', trim($data['name']) . ' დაემატა — კოდი: ' . $child_code);
    }

    public function link(Request $request)
    {
        $data = $request->validate([
            'child_code' => 'required|string|max:8',
        ]);

        $code  = strtoupper(trim($data['child_code']));
        $child = User::where('child_code', $code)->where('role', 'child')->first();

        if (! $child) {
            return back()->withErrors(['child_code_link' => 'კოდი არასწორია — ბავშვი ვერ მოიძებნა'])->withInput();
        }

        $parent = auth()->user();

        if ($parent->children()->where('users.id', $child->id)->exists()) {
            return back()->with('child_added', $child->name . ' უკვე დამატებულია');
        }

        $parent->children()->attach($child->id);

        return back()->with('child_added', $child->name . ' დაემატა კოდის საშუალებით!');
    }

    private function uniqueCode(): string
    {
        $chars = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (User::where('child_code', $code)->exists());

        return $code;
    }
}
