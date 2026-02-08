<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Question;

class CaptchaController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        if ($user->level < 2) {
            abort(403, 'This level is locked');
        }

        $standardCaptcha = $this->generateCaptcha();
        $selectionCaptcha = $this->generateCaptcha(); // moved to step 2
        $georgianCaptcha = $this->generateCaptchaSmall(); // moved to step 3
        $rotatingCaptcha = $this->generateCaptchanumber();

        session([
            'standardCaptcha' => $standardCaptcha,
            'selection_captcha' => $selectionCaptcha,
            'georgian_captcha' => $georgianCaptcha,
            'rotating_captcha' => $rotatingCaptcha,
        ]);

        $question = Question::where('level', 2)->firstOrFail();

        return view('levels.level2', [
            'question' => $question,
            'standardCaptcha' => $standardCaptcha,
            'selectionCaptcha' => $selectionCaptcha,
            'georgianCaptcha' => $georgianCaptcha,
            'rotatingCaptcha' => $rotatingCaptcha,
            'userLevel' => $user->level,
            'level' => 2
        ]);
    }

    public function verify(Request $request)
    {
        $step = (int) $request->step;
        $input = trim((string) $request->input);

        if ($step === 1) {
            $expected = trim(session('standardCaptcha'));
        } elseif ($step === 2) {
            $expected = trim((string) $request->finalCaptcha); // selectionCaptcha
        } elseif ($step === 3) {
            $expected = trim($this->convertToGeorgian(session('georgian_captcha')));
        } elseif ($step === 4) {
            $expected = trim((string) $request->finalCaptcha); // rotatingCaptcha
        } else {
            return response()->json(['success' => false]);
        }

        $success = $input === $expected;

        if (!$success) {
            return response()->json([
                'success' => false,
                'debug' => [
                    'input' => $input,
                    'expected' => $expected
                ]
            ]);
        }

        if ($step === 4) {
            $user = Auth::user();
            if ($user->level < 3) {
                $user->update(['level' => 3]);
            }

            return response()->json([
                'success' => true,
                'newLevel' => 3
            ]);
        }

        return response()->json(['success' => true]);
    }

    private function generateCaptcha($length = 8)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        return substr(str_shuffle($chars), 0, $length);
    }

    private function generateCaptchaSmall($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($chars), 0, $length);
    }

    private function generateCaptchanumber($length = 8)
    {
        $chars = '0123456789';
        return substr(str_shuffle($chars), 0, $length);
    }

    private function convertToGeorgian($text)
    {
        $map = [
            'A'=>'ა','B'=>'ბ','C'=>'ც','D'=>'დ','E'=>'ე','F'=>'ფ','G'=>'გ',
            'H'=>'ჰ','I'=>'ი','J'=>'ჯ','K'=>'კ','L'=>'ლ','M'=>'მ','N'=>'ნ',
            'O'=>'ო','P'=>'პ','Q'=>'ქ','R'=>'რ','S'=>'ს','T'=>'ტ','U'=>'უ',
            'V'=>'ვ','W'=>'წ','X'=>'ხ','Y'=>'ყ','Z'=>'ზ',
            'a'=>'ა','b'=>'ბ','c'=>'ც','d'=>'დ','e'=>'ე','f'=>'ფ','g'=>'გ',
            'h'=>'ჰ','i'=>'ი','j'=>'ჯ','k'=>'კ','l'=>'ლ','m'=>'მ','n'=>'ნ',
            'o'=>'ო','p'=>'პ','q'=>'ქ','r'=>'რ','s'=>'ს','t'=>'ტ','u'=>'უ',
            'v'=>'ვ','w'=>'წ','x'=>'ხ','y'=>'ყ','z'=>'ზ',
        ];

        return collect(str_split($text))
            ->map(fn($c) => $map[$c] ?? $c)
            ->implode('');
    }
}
