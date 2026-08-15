<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PushSubscription;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $isAdmin       = auth()->user()->isAdmin();
        $users         = $isAdmin ? User::orderBy('created_at', 'desc')->get() : collect();
        $subscriptions = $isAdmin ? PushSubscription::with('user')->get() : collect();
        return view('admin.panel', compact('users', 'subscriptions', 'isAdmin'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:child,parent,admin']);
        $user->update(['role' => $request->role]);
        return response()->json(['success' => true, 'role' => $user->role]);
    }
}
