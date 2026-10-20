<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChildSetting;
use App\Models\Grade;
use App\Models\Package;
use App\Models\RolePermission;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    const ALL_ROLES = ['admin', 'parent', 'child', 'teacher', 'staff'];

    public function index(Request $request)
    {
        $query = User::withCount(['parents', 'children'])->orderBy('created_at', 'desc');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($s) => $s->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%"));
        }

        // A child can have several parents and a parent can have several children
        // (child_parent is many-to-many) — clicking a row filters the list to just
        // the other side of that relationship instead of searching by name/email.
        // Queried directly against the pivot table rather than whereHas(), since
        // whereHas() on a self-referential belongsToMany (User -> User) aliases
        // the joined table internally and an unqualified 'users.id' inside the
        // closure silently resolves to the wrong side of that join.
        $filterParentsOf = null;
        $filterChildrenOf = null;
        if ($request->filled('parents_of')) {
            $filterParentsOf = User::find($request->parents_of);
            if ($filterParentsOf) {
                $parentIds = \Illuminate\Support\Facades\DB::table('child_parent')
                    ->where('child_id', $filterParentsOf->id)->pluck('parent_id');
                $query->whereIn('id', $parentIds);
            }
        } elseif ($request->filled('children_of')) {
            $filterChildrenOf = User::find($request->children_of);
            if ($filterChildrenOf) {
                $childIds = \Illuminate\Support\Facades\DB::table('child_parent')
                    ->where('parent_id', $filterChildrenOf->id)->pluck('child_id');
                $query->whereIn('id', $childIds);
            }
        }

        $users = $query->paginate(25)->withQueryString();
        $users->load(['childSetting.grade', 'themes:id', 'parents:id,name,email', 'children:id,name,email,child_code']);

        return view('admin.users.index', [
            'users'            => $users,
            'roles'            => self::ALL_ROLES,
            'packages'         => Package::orderBy('sort_order')->get(),
            'grades'           => Grade::orderBy('number')->get(),
            'themes'           => Theme::orderBy('name')->get(),
            'filters'          => $request->only('role', 'search'),
            'filterParentsOf'  => $filterParentsOf,
            'filterChildrenOf' => $filterChildrenOf,
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:admin,parent,child,teacher,staff']);
        $user->update(['role' => $request->role]);
        return response()->json(['ok' => true, 'role' => $user->role]);
    }

    /**
     * Admin-only child management — lets an admin repurpose any child account
     * for testing: change grade/difficulty/themes without being that child's
     * parent, and optionally exempt it from the daily test-count limit.
     */
    public function updateChildSettings(Request $request, User $child)
    {
        abort_unless($child->role === 'child', 404);

        $data = $request->validate([
            'grade_id'       => 'nullable|exists:grades,id',
            'difficulty'     => 'required|integer|min:1|max:5',
            'tests_per_week' => 'required|integer|min:1|max:7',
            'theme_ids'      => 'nullable|array',
            'theme_ids.*'    => 'exists:themes,id',
        ]);

        $grade = ! empty($data['grade_id']) ? Grade::find($data['grade_id']) : null;
        if ($grade && $data['difficulty'] > $grade->max_level) {
            return response()->json(['error' => "ამ კლასს მაქსიმუმ {$grade->max_level} დონე აქვს"], 422);
        }

        ChildSetting::updateOrCreate(
            ['user_id' => $child->id],
            [
                'grade_id'        => $data['grade_id'] ?? null,
                'difficulty'      => $data['difficulty'],
                'tests_per_week'  => $data['tests_per_week'],
                'unlimited_tests' => $request->boolean('unlimited_tests'),
            ]
        );

        $child->themes()->sync($data['theme_ids'] ?? []);

        return response()->json(['ok' => true]);
    }

    /**
     * Resets a child's level/streak/coin progress back to a fresh start — for
     * reusing one account to repeatedly test level-up/achievement flows without
     * creating a new child every time. Does not delete test/practice history.
     */
    public function resetChildProgress(User $child)
    {
        abort_unless($child->role === 'child', 404);

        $setting = $child->childSetting;
        if ($setting) {
            $setting->update([
                'difficulty'               => 1,
                'difficulty_streak'        => 0,
                'tests_since_level_review' => 0,
                'coins'                    => 0,
                'topic_rotation'           => null,
                'template_rotation'        => null,
                'practice_topic_rotation'  => null,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Lightweight search for the parent/child relationship picker — role-scoped
     * so a child row's "add parent" search only offers parent-role users, and
     * vice versa.
     */
    public function searchUsers(Request $request)
    {
        $role = $request->input('role');
        $q    = trim((string) $request->input('q', ''));

        $query = User::query();
        if (in_array($role, ['parent', 'child'], true)) {
            $query->where('role', $role);
        }
        if ($q !== '') {
            $query->where(fn ($s) => $s->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('child_code', 'like', "%{$q}%"));
        }

        return response()->json(
            $query->orderBy('name')->limit(20)->get(['id', 'name', 'email', 'child_code'])
        );
    }

    /**
     * Attaches/detaches a parent<->child link from either side — a child can
     * have several parents and a parent can have several children, so this is
     * always additive/subtractive on the child_parent pivot, never a full sync
     * that would silently drop other existing links.
     */
    public function manageRelation(Request $request, User $user)
    {
        $data = $request->validate([
            'action'    => 'required|in:attach,detach',
            'direction' => 'required|in:parent,child',
            'target_id' => 'required|exists:users,id',
        ]);

        $target = User::find($data['target_id']);

        if ($data['direction'] === 'parent') {
            // $user is the CHILD; $target must be a parent-role user.
            abort_unless($user->role === 'child', 404);
            if ($data['action'] === 'attach') {
                abort_unless($target->role === 'parent', 422);
                $user->parents()->syncWithoutDetaching([$target->id]);
            } else {
                $user->parents()->detach($target->id);
            }
            $target->syncChildActivation();
        } else {
            // $user is the PARENT; $target must be a child-role user.
            abort_unless($user->role === 'parent', 404);
            if ($data['action'] === 'attach') {
                abort_unless($target->role === 'child', 422);
                $user->children()->syncWithoutDetaching([$target->id]);
            } else {
                $user->children()->detach($target->id);
            }
            $user->syncChildActivation();
        }

        return response()->json(['ok' => true]);
    }
}
