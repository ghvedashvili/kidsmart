<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\Admin\QuestionTemplateController;
use App\Http\Controllers\ChildSettingsController;
use App\Http\Controllers\TestController;
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

    // შვილის პარამეტრები (მხოლოდ მშობელი)
    Route::get('/children/{child}/settings',  [ChildSettingsController::class, 'edit'])->name('child.settings.edit');
    Route::put('/children/{child}/settings',  [ChildSettingsController::class, 'update'])->name('child.settings.update');

    // ტესტი
    Route::get('/test/start',           [TestController::class, 'start'])->name('test.start');
    Route::get('/test/{test}',          [TestController::class, 'show'])->name('test.show');
    Route::post('/test/{test}/submit',  [TestController::class, 'submit'])->name('test.submit');
    Route::get('/test/{test}/result',   [TestController::class, 'result'])->name('test.result');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin',                       [AdminController::class, 'index'])->name('admin.panel');
    Route::post('/push/send',                  [PushController::class, 'send'])->name('push.send');
    Route::post('/admin/users/{user}/role',    [AdminController::class, 'updateRole'])->name('admin.updateRole');

    // კლასები
    Route::get('/admin/grades',               [GradeController::class, 'index'])->name('admin.grades.index');
    Route::post('/admin/grades',              [GradeController::class, 'store'])->name('admin.grades.store');
    Route::delete('/admin/grades/{grade}',    [GradeController::class, 'destroy'])->name('admin.grades.destroy');

    // თემები
    Route::get('/admin/themes',               [ThemeController::class, 'index'])->name('admin.themes.index');
    Route::post('/admin/themes',              [ThemeController::class, 'store'])->name('admin.themes.store');
    Route::delete('/admin/themes/{theme}',    [ThemeController::class, 'destroy'])->name('admin.themes.destroy');
    Route::get('/admin/themes/{theme}/vars',  [ThemeController::class, 'showVariables'])->name('admin.themes.variables');
    Route::post('/admin/themes/{theme}/vars', [ThemeController::class, 'storeVariable'])->name('admin.themes.variables.store');
    Route::delete('/admin/vars/{variable}',   [ThemeController::class, 'destroyVariable'])->name('admin.themes.variables.destroy');

    // თოპიქები
    Route::get('/admin/topics',               [TopicController::class, 'index'])->name('admin.topics.index');
    Route::post('/admin/topics',              [TopicController::class, 'store'])->name('admin.topics.store');
    Route::delete('/admin/topics/{topic}',    [TopicController::class, 'destroy'])->name('admin.topics.destroy');

    // კითხვების შაბლონები
    Route::get('/admin/questions',                     [QuestionTemplateController::class, 'index'])->name('admin.questions.index');
    Route::get('/admin/questions/create',              [QuestionTemplateController::class, 'create'])->name('admin.questions.create');
    Route::post('/admin/questions',                    [QuestionTemplateController::class, 'store'])->name('admin.questions.store');
    Route::get('/admin/questions/{question}/edit',     [QuestionTemplateController::class, 'edit'])->name('admin.questions.edit');
    Route::put('/admin/questions/{question}',          [QuestionTemplateController::class, 'update'])->name('admin.questions.update');
    Route::delete('/admin/questions/{question}',       [QuestionTemplateController::class, 'destroy'])->name('admin.questions.destroy');
});
