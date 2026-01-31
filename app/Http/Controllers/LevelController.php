<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;

class LevelController extends Controller
{


    public function show($level)
    {
        $user = Auth::user();

        // არ მისცე მომავალ ლეველზე გადასვლა
        if ($level > $user->level) {
            abort(403, 'This level is locked');
        }
        
        $question = Question::where('level', $level)->firstOrFail();

        return view('levels.level', [
            'question' => $question,
            'level' => $level,
            'userLevel' => $user->level
        ]);
    }

    public function check(Request $request, $level)
    {
        $user = Auth::user();
        $question = Question::where('level', $level)->firstOrFail();

        if ($user->level != $level) {
            return response()->json(['status' => 'locked']);
        }

        if (strtolower(trim($request->answer)) === strtolower(trim($question->answer))) {
            $user->increment('level');

            return response()->json([
                'status' => 'correct',
                'nextLevel' => $user->level
            ]);
        }

        return response()->json(['status' => 'wrong']);
    }
}

