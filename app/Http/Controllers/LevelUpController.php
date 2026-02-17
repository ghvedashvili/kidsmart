<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Question;
class LevelUpController extends Controller
{
   
    public function complete()
{
    $user = Auth::user();

    if ($user->level != 4) {
        return redirect()->route('levels.show', $user->level);
    }

    // level გაზარდე
    $user->update(['level' => 5]);

    return view('levels.level4', [
        'completed' => true,
        'userLevel' => $user->level,
        'level' => 5
    ]);
}

}
