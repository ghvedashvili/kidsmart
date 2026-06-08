<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PushSubscription;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $users         = User::orderBy('created_at', 'desc')->get();
        $subscriptions = PushSubscription::with('user')->get();
        return view('admin.panel', compact('users', 'subscriptions'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:child,parent,admin']);
        $user->update(['role' => $request->role]);
        return response()->json(['success' => true, 'role' => $user->role]);
    }
}
