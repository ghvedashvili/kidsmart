<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\NicknameController;
use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\RussiaIsOccupierController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\LevelUpController;


Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);


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

    Route::get('/levels/3', [RussiaIsOccupierController::class, 'entry']);
    Route::get('/levels/3/{code}', [RussiaIsOccupierController::class, 'index'])
        ->name('level3');
    Route::post('/levels/3/complete', [RussiaIsOccupierController::class, 'complete'])
    ->middleware('auth');

    Route::get('/levels/4/complete', [LevelUpController::class, 'complete'])
    ->middleware('auth');
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
