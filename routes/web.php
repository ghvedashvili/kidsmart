<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\NicknameController;
use App\Http\Controllers\CaptchaController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Levels (auth required)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {





    Route::get('/levels/2', [CaptchaController::class, 'show'])
        ->name('level2.show');

    Route::post('/levels/2/verify', [CaptchaController::class, 'verify'])
        ->name('level2.verify');
    /*
    |--------------------------------------------------
    | Generic levels (1,3,4,5...)
    |--------------------------------------------------
    */
    Route::get('/levels/{level}', [LevelController::class, 'show'])
        ->whereNumber('level')
        ->name('levels.show');

    Route::post('/levels/{level}/check', [LevelController::class, 'check'])
        ->whereNumber('level')
        ->name('levels.check');

    /*
    |--------------------------------------------------
    | Nickname level AJAX
    |--------------------------------------------------
    */
    Route::post('/level/{level}/nickname/live', [NicknameController::class, 'live'])
        ->whereNumber('level');

    Route::post('/level/{level}/nickname/submit', [NicknameController::class, 'submit'])
        ->whereNumber('level');

    /*
    |--------------------------------------------------
    | Level 2 – CAPTCHA (special level)
    |--------------------------------------------------
    */
   
});

require __DIR__.'/auth.php';
