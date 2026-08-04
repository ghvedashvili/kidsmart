<?php

namespace App\Http\Controllers;

use App\Models\ChildSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChildController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:50',
            'grade_id'       => 'required|exists:grades,id',
            'difficulty'     => 'nullable|integer|min:1|max:5',
            'tests_per_week' => 'nullable|integer|min:1|max:7',
            'theme_ids'      => 'nullable|array',
            'theme_ids.*'    => 'exists:themes,id',
            'topic_ids'      => 'nullable|array',
            'topic_ids.*'    => 'exists:topics,id',
        ]);

        $parent     = auth()->user();
        $child_code = $this->uniqueCode();

        $child = User::create([
            'name'       => trim($data['name']),
            'email'      => 'child_' . $parent->id . '_' . $child_code . '@kidsmart.local',
            'password'   => bcrypt(Str::random(16)),
            'role'       => 'child',
            'parent_id'  => $parent->id,
            'child_code' => $child_code,
        ]);

        ChildSetting::create([
            'user_id'        => $child->id,
            'grade_id'       => $data['grade_id'],
            'difficulty'     => $data['difficulty'] ?? 1,
            'tests_per_week' => $data['tests_per_week'] ?? 1,
        ]);

        if (! empty($data['theme_ids'])) {
            $child->themes()->sync($data['theme_ids']);
        }
        if (! empty($data['topic_ids'])) {
            $child->topics()->sync($data['topic_ids']);
        }

        return back()->with('child_added', trim($data['name']) . ' დაემატა — კოდი: ' . $child_code);
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
