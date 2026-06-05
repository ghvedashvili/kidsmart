<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\NicknameController;
use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\RussiaIsOccupierController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\LevelUpController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PushController;

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
| Block default /login completely
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return redirect('/');
});
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');
/*
|--------------------------------------------------------------------------
| Google Auth
|--------------------------------------------------------------------------
*/

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])
    ->name('google.login');

Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

/*
|--------------------------------------------------------------------------
| Protected Routes (auth required)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/push/send', [PushController::class, 'send'])->name('push.send');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.panel');
    Route::post('/admin/users/{user}/role', [AdminController::class, 'updateRole'])->name('admin.updateRole');
    Route::post('/admin/users/{user}/level', [AdminController::class, 'updateLevel'])->name('admin.updateLevel');
    Route::post('/admin/questions', [AdminController::class, 'storeQuestion'])->name('admin.storeQuestion');
    Route::post('/admin/questions/{question}', [AdminController::class, 'updateQuestion'])->name('admin.updateQuestion');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/push/subscribe',   [PushController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushController::class, 'unsubscribe'])->name('push.unsubscribe');


    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------
    | Level 2 – CAPTCHA
    |--------------------------------------------------
    */

    Route::get('/levels/2', [CaptchaController::class, 'show'])
        ->name('level2.show');

    Route::post('/levels/2/verify', [CaptchaController::class, 'verify'])
        ->name('level2.verify');

    /*
    |--------------------------------------------------
    | Level 3
    |--------------------------------------------------
    */

    Route::get('/levels/3', [RussiaIsOccupierController::class, 'entry']);
    Route::get('/levels/3/{code}', [RussiaIsOccupierController::class, 'index'])
        ->name('level3');

    Route::post('/levels/3/complete', [RussiaIsOccupierController::class, 'complete']);

    /*
    |--------------------------------------------------
    | Level 4 Complete
    |--------------------------------------------------
    */

    Route::get('/levels/4/complete', [LevelUpController::class, 'complete']);

    /*
    |--------------------------------------------------
    | Generic Levels
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
    | Nickname Level AJAX
    |--------------------------------------------------
    */

    Route::post('/level/{level}/nickname/live', [NicknameController::class, 'live'])
        ->whereNumber('level');

    Route::post('/level/{level}/nickname/submit', [NicknameController::class, 'submit'])
        ->whereNumber('level');
Route::post('/levels/{level}/print-answer', [LevelController::class, 'printAnswer'])
    ->whereNumber('level')
    ->name('levels.print-answer')
    ->middleware('auth');
});
