<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/** გიდის (მელა) მდგომარეობის შენახვა — users.guide_state */
class GuideController extends Controller
{
    private const RULES = [
        'welcome'          => ['done', 'later'],
        'step'             => ['add', 'share', 'tour', 'market', null],
        'tour'             => ['done', null],
        'shared'           => 'bool',
        'checklist_hidden' => 'bool',
    ];

    public function save(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (! Schema::hasColumn('users', 'guide_state')) return response()->json(['ok' => false]);

        $state = (array) ($user->guide_state ?? []);
        foreach ((array) $request->input('state', []) as $key => $value) {
            $rule = self::RULES[$key] ?? false;
            if ($rule === false) continue;
            if ($rule === 'bool') { $state[$key] = (bool) $value; continue; }
            if (! in_array($value, $rule, true)) continue;
            if ($value === null) unset($state[$key]); else $state[$key] = $value;
        }
        $user->guide_state = $state;
        $user->save();

        return response()->json(['ok' => true, 'state' => $state]);
    }
}
