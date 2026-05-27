<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LevelUpController extends Controller
{
    public function complete(Request $request)
    {
        $user  = Auth::user();
        $token = $request->query('t');

        // token არ არის ან არასწორია
        if (!$token || $token !== env('LEVEL4_TOKEN')) {
            return redirect()->route('levels.show', 4)
                ->with('error', 'ბმული არასწორია. ინსტაგრამზე სწორი ბმული იპოვე.');
        }

        // მხოლოდ 4-ე ლეველზე მყოფ მოთამაშეს შეუძლია გადასვლა
        if ($user->level != 4) {
            return redirect()->route('levels.show', $user->level);
        }

        $user->update(['level' => 5]);

        return view('levels.level4', [
            'completed' => true,
            'userLevel' => $user->level,
            'level'     => 5,
        ]);
    }
}
