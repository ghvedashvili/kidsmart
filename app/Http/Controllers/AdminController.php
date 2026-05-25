<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::orderBy('level', 'desc')->orderBy('created_at')->get();
        return view('admin.panel', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:gamer,admin']);
        $user->update(['role' => $request->role]);
        return response()->json(['success' => true, 'role' => $user->role]);
    }
}
