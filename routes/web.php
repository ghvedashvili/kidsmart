<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\NicknameController; // 👈 ეს აკლდა

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('/levels/{level}', [LevelController::class, 'show'])
        ->name('levels.show');

    Route::post('/levels/{level}/check', [LevelController::class, 'check'])
        ->name('levels.check');

    // 👇 nickname AJAX routes
    Route::post('/level/{level}/nickname/live', [NicknameController::class, 'live']);
    Route::post('/level/{level}/nickname/submit', [NicknameController::class, 'submit']);
});

require __DIR__.'/auth.php';
