<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    const ALL_ROLES = ['admin', 'parent', 'child', 'teacher', 'staff'];

    public function index(Request $request)
    {
        $query = User::orderBy('created_at', 'desc');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($s) => $s->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%"));
        }

        return view('admin.users.index', [
            'users'    => $query->paginate(25)->withQueryString(),
            'roles'    => self::ALL_ROLES,
            'packages' => Package::orderBy('sort_order')->get(),
            'filters'  => $request->only('role', 'search'),
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:admin,parent,child,teacher,staff']);
        $user->update(['role' => $request->role]);
        return response()->json(['ok' => true, 'role' => $user->role]);
    }
}
