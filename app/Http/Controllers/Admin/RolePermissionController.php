<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function index()
    {
        $perms = RolePermission::all()
            ->groupBy('role')
            ->map(fn($rows) => $rows->pluck('page')->all());

        return view('admin.permissions.index', [
            'perms' => $perms,
            'roles' => RolePermission::ROLES,
            'pages' => RolePermission::PAGES,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->input('perms', []);

        RolePermission::whereIn('role', RolePermission::ROLES)->delete();

        foreach (RolePermission::ROLES as $role) {
            foreach ($data[$role] ?? [] as $page) {
                if (isset(RolePermission::PAGES[$page])) {
                    RolePermission::create(['role' => $role, 'page' => $page]);
                }
            }
        }

        return back()->with('success', 'ნებართვები შენახულია');
    }
}
