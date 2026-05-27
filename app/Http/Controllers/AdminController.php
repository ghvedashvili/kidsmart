<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Question;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $users     = User::orderBy('level', 'desc')->orderBy('created_at')->get();
        $questions = Question::orderBy('level')->get();
        return view('admin.panel', compact('users', 'questions'));
    }

    public function updateQuestion(Request $request, Question $question)
    {
        $request->validate([
            'level'           => 'required|integer|min:1',
            'type'            => 'required|string|max:50',
            'question'        => 'nullable|string',
            'rules'           => 'nullable|string',
            'success_message' => 'nullable|string',
            'answer'          => 'nullable|string',
            'hints'           => 'nullable|string',
        ]);

        $question->update([
            'level'           => $request->level,
            'type'            => $request->type,
            'question'        => $request->question,
            'rules'           => $request->rules,
            'success_message' => $request->success_message,
            'answer'          => json_decode($request->answer, true) ?? [],
            'hints'           => json_decode($request->hints,  true) ?? [],
        ]);

        return response()->json(['success' => true]);
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:gamer,admin']);
        $user->update(['role' => $request->role]);
        return response()->json(['success' => true, 'role' => $user->role]);
    }

    public function updateLevel(Request $request, User $user)
    {
        $request->validate(['level' => 'required|integer|min:1']);
        $user->update(['level' => $request->level]);
        return response()->json(['success' => true, 'level' => $user->level]);
    }
}
