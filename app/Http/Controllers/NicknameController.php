<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NicknameController extends Controller
{
    private function getRules(string $nickname): array
    {
        return [
            [
                'id' => 1,
                'text' => 'Nickname უნდა შეიცავდეს მინიმუმ 5 სიმბოლოს',
                'passed' => strlen($nickname) >= 5
            ],
            [
                'id' => 2,
                'text' => 'Nickname უნდა შეიცავდეს ციფრს',
                'passed' => preg_match('/\d/', $nickname)
            ],
            [
                'id' => 3,
                'text' => 'Nickname უნდა შეიცავდეს დიდ ასოს',
                'passed' => preg_match('/[A-Z]/', $nickname)
            ],
            [
                'id' => 4,
                'text' => 'Nickname უნდა შეიცავდეს სპეციალურ სიმბოლოს',
                'passed' => preg_match('/[!@#$%^&*()_\-+=\[\]{};:"\\|,.<>\/?]/', $nickname)
            ],
            [
                'id' => 5,
                'text' => 'Nickname-ში ციფრების ჯამი უნდა იყოს 15',
                'passed' => function() use($nickname){
                    preg_match_all('/\d/', $nickname, $nums);
                    return array_sum($nums[0]) === 15;
                }
            ],
            [
                'id' => 6,
                'text' => 'RU აკრძალულია',
                'passed' => !str_contains(strtoupper($nickname), 'RU')
            ],
        ];
    }

    private function normalizeRules(array $rules): array
    {
        return array_map(function($rule){
            return [
                'id' => $rule['id'],
                'text' => $rule['text'],
                'passed' => is_callable($rule['passed']) ? (bool)$rule['passed']() : (bool)$rule['passed']
            ];
        }, $rules);
    }

    public function live(Request $request, $level)
    {
        $user = Auth::user();
        if($user->level != $level){
            return response()->json(['locked' => true]);
        }

        $nickname = $request->nickname ?? '';
        $rules = $this->normalizeRules($this->getRules($nickname));

        return response()->json([
            'rules' => $rules,
            'locked' => false
        ]);
    }

    // Submit nickname
public function submit(Request $request, $level)
{
    $user = Auth::user();

    if ($user->level != $level) {
        return response()->json(['status' => 'locked']);
    }

    $nickname = $request->nickname ?? '';

    $rules = $this->normalizeRules($this->getRules($nickname));

    if (!collect($rules)->every(fn($r) => $r['passed'])) {
        return response()->json([
            'status' => 'error',
            'rules' => $rules
        ]);
    }

    $user->nickname = $nickname;

    // ✅ პირდაპირ update მეთოდით, DB-ში ინკრემენტი და სეიშენში სინქრონიზაცია
    $user->level += 1;
    $user->save();

    // ახალ level-ს ვაჩვენებთ response-ში
    return response()->json([
        'status' => 'success',
        'nickname' => $nickname,
        'newLevel' => $user->level
    ]);
}


}
