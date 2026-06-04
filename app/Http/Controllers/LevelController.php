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

    $maxLevel = Question::max('level') ?? 1;

    if ($level > $maxLevel) {
        return view('errors.level-not-found', [
            'level'    => $level,
            'maxLevel' => $maxLevel,
        ]);
    }

    if ($level > $user->level) {
        abort(403, 'This level is locked');
    }

    $question = Question::where('level', $level)->firstOrFail();

    // პასუხი encode-ით გადაეცემა — inspect-ში გაუგებარი ჩანს
    $encodedAnswer = base64_encode(
        is_array($question->answer)
            ? implode(' / ', $question->answer)
            : $question->answer
    );

    return view('levels.level', [
        'question'      => $question,
        'level'         => $level,
        'nickname'      => $user->nickname,
        'userLevel'     => $user->level,
        'completed'     => false,
        'encodedAnswer' => $encodedAnswer, // ← encoded პასუხი
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

    // ✅ თუ კითხვა არის ACTION ტიპის
    if ($question->type === 'action') {
        $user->increment('level');

        return response()->json([
            'status' => 'correct',
            'nextLevel' => $user->level,
            'hints' => $question->hints ?? []
        ]);
    }

    // normalize ფუნქცია
    $normalize = function ($text) {
        $text = strtolower(trim($text));
        $text = preg_replace('/\s+/', ' ', $text);
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
            'hints' => $question->hints ?? []
        ]);
    }

    return response()->json([
        'status' => 'wrong',
        'hints' => $question->hints ?? []
    ]);
}


}

