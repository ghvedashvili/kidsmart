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
            'nickname' => $user->nickname,
            'userLevel' => $user->level,
             'completed' => false,
        ]);
    }

    public function check(Request $request, $level)
{
    $user = Auth::user();
    $question = Question::where('level', $level)->firstOrFail();

    // თუ მომხმარებელი არ არის ამ დონეზე
    if ($user->level != $level) {
        return response()->json(['status' => 'locked']);
    }

    // normalize ფუნქცია: lowercase + trim + ყველა ზედმეტი space–ის მოხსნა
    $normalize = function ($text) {
        $text = strtolower(trim($text));
        $text = preg_replace('/\s+/', ' ', $text); // ყველა ზედმეტი space–ი single space–ად
        return $text;
    };

    $userAnswer = $normalize($request->answer);

    // ყველა სწორი პასუხის normalize
    $correctAnswers = array_map($normalize, $question->answer);

    if (in_array($userAnswer, $correctAnswers)) {
        $user->increment('level');

        return response()->json([
            'status' => 'correct',
            'nextLevel' => $user->level,
            // optional: show hints after correct answer
            'hints' => $question->hints ?? []
        ]);
    }

    return response()->json([
        'status' => 'wrong',
        'hints' => $question->hints ?? []
    ]);
}

}

