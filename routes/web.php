<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\AdminController;
use App\Models\User;

Route::get('/', function () {
    if (auth()->check()) return redirect()->route('dashboard');
    return view('welcome');
});

Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Google auth (მშობელი)
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// ბავშვის login
Route::post('/child-login', function (Request $request) {
    $request->validate([
        'parent_code' => 'required|string',
        'name'        => 'required|string|max:50',
    ]);

    $parent = User::where('parent_code', strtoupper(trim($request->parent_code)))->first();

    if (! $parent) {
        return back()->withErrors(['parent_code' => 'კოდი არასწორია'])->withInput();
    }

    $child = User::firstOrCreate(
        [
            'parent_id' => $parent->id,
            'name'      => trim($request->name),
        ],
        [
            'email'    => 'child_' . $parent->id . '_' . \Illuminate\Support\Str::slug($request->name) . '@kidsmart.local',
            'password' => bcrypt(\Illuminate\Support\Str::random(16)),
            'role'     => 'child',
        ]
    );

    \Illuminate\Support\Facades\Auth::login($child, true);

    return redirect()->route('dashboard');
})->name('child-login');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/push/subscribe',   [PushController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushController::class, 'unsubscribe'])->name('push.unsubscribe');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin',                       [AdminController::class, 'index'])->name('admin.panel');
    Route::post('/push/send',                  [PushController::class, 'send'])->name('push.send');
    Route::post('/admin/users/{user}/role',    [AdminController::class, 'updateRole'])->name('admin.updateRole');
});
