<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\ThemeController;
use App\Models\ThemeVarGroup;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\Admin\TopicVideoController;
use App\Http\Controllers\Admin\QuestionTemplateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\ChildSettingsController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TestPreviewController;
use App\Models\Grade;
use App\Models\Theme;
use App\Models\Topic;
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
        'child_code' => 'required|string',
    ]);

    $child = User::where('child_code', strtoupper(trim($request->child_code)))
                 ->where('role', 'child')
                 ->first();

    if (! $child) {
        return back()->withErrors(['child_code' => 'კოდი არასწორია'])->withInput();
    }

    if (! $child->is_active) {
        return back()->withErrors(['child_code' => 'ანგარიში გათიშულია — მშობლის გეგმა არ მოიცავს ამ ბავშვს'])->withInput();
    }

    \Illuminate\Support\Facades\Auth::login($child, true);

    return redirect()->route('dashboard');
})->name('child-login');

Route::middleware(['auth'])->post('/children', [ChildController::class, 'store'])->name('child.store');
Route::middleware(['auth'])->post('/children/link', [ChildController::class, 'link'])->name('child.link');

Route::middleware(['auth', 'role.permission'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', [
            'grades'         => Grade::where('is_active', true)->orderBy('number')->get(),
            'themes'         => Theme::all(),
            'topics'         => Topic::with('grade')->orderBy('grade_id')->get(),
            'defaultThemeId' => Theme::where('name', 'სტანდარტი')->value('id'),
        ]);
    })->name('dashboard');

    Route::post('/push/subscribe',        [PushController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe',      [PushController::class, 'unsubscribe'])->name('push.unsubscribe');
    Route::post('/push/remind/{child}',   [PushController::class, 'remind'])->name('push.remind');

    // შვილის პარამეტრები (მხოლოდ მშობელი)
    Route::get('/children/{child}/stats',                [ChildSettingsController::class, 'stats'])->name('child.stats');
    Route::get('/children/{child}/tests/{test}',         [ChildSettingsController::class, 'showTest'])->name('child.test.show');
    Route::get('/children/{child}/settings',    [ChildSettingsController::class, 'edit'])->name('child.settings.edit');
    Route::put('/children/{child}/settings',    [ChildSettingsController::class, 'update'])->name('child.settings.update');
    Route::put('/children/{child}/avatar',      [ChildSettingsController::class, 'updateAvatar'])->name('child.avatar.update');
    Route::delete('/children/{child}',          [ChildSettingsController::class, 'destroy'])->name('child.destroy');

    // მიღწევები
    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements');

    // სავარჯიშოები (ბავშვი)
    Route::get('/practice',                              [PracticeController::class, 'topics'])->name('practice.topics');
    Route::get('/practice/{slug}',                       [PracticeController::class, 'show'])->name('practice.show');
    Route::get('/practice/{slug}/question',              [PracticeController::class, 'question'])->name('practice.question');
    Route::post('/practice/{slug}/answer',               [PracticeController::class, 'answer'])->name('practice.answer');

    // ვიდეოთეკა + ისტორია (ბავშვი)
    Route::get('/videos', [VideoController::class, 'library'])->name('videos.library');
    Route::get('/my/tests', [VideoController::class, 'myHistory'])->name('my.tests');
    Route::get('/my/tests/{test}', [VideoController::class, 'myTest'])->name('my.test.show');

    // თამაშები (ბავშვი)
    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::get('/games/rock-paper-scissors', [GameController::class, 'rps'])->name('games.rps');
    Route::post('/games/rock-paper-scissors/round', [GameController::class, 'round'])->name('games.rps.round');

    Route::get('/games/wolves-hare', [GameController::class, 'wolvesHare'])->name('games.wolves-hare');
    Route::post('/games/wolves-hare/state', [GameController::class, 'wolvesHareState'])->name('games.wolves-hare.state');
    Route::post('/games/wolves-hare/finish', [GameController::class, 'wolvesHareFinish'])->name('games.wolves-hare.finish');

    // მარკეტი — parent manages
    Route::get('/children/{child}/market',              [MarketController::class, 'index'])->name('market.index');
    Route::post('/children/{child}/market/items',       [MarketController::class, 'store'])->name('market.store');
    Route::delete('/market/items/{item}',               [MarketController::class, 'destroy'])->name('market.item.destroy');
    Route::patch('/market/items/{item}/toggle',         [MarketController::class, 'toggleItem'])->name('market.item.toggle');
    // მარკეტი — parent approves/cancels
    Route::patch('/market/purchases/{purchase}/approve', [MarketController::class, 'approve'])->name('market.approve');
    Route::patch('/market/purchases/{purchase}/cancel',  [MarketController::class, 'cancel'])->name('market.cancel');
    // მარკეტი — child views
    Route::get('/market',                                [MarketController::class, 'childIndex'])->name('market.child');
    // მარკეტი — child buys
    Route::post('/market/items/{item}/buy',              [MarketController::class, 'buy'])->name('market.buy');

    // ტესტი
    Route::get('/test/start',           [TestController::class, 'start'])->name('test.start');
    Route::get('/test/{test}',          [TestController::class, 'show'])->name('test.show');
    Route::post('/test/{test}/submit',  [TestController::class, 'submit'])->name('test.submit');
    Route::get('/test/{test}/result',   [TestController::class, 'result'])->name('test.result');

    Route::get('/test-preview', [TestPreviewController::class, 'show'])->name('test.preview');
});

// /admin entry-point — ნებისმიერი admin-permissioned user შედის (per-page check არ არის)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.panel');
});

// დანარჩენი ადმინ-route-ები — role.permission ამოწმებს კონკრეტულ გვერდს
Route::middleware(['auth', 'admin', 'role.permission'])->group(function () {
    Route::post('/push/send',                  [PushController::class, 'send'])->name('push.send');

    Route::get('/admin/users',                    [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users/{user}/role',       [UserController::class, 'updateRole'])->name('admin.updateRole');
    Route::get('/admin/permissions',              [RolePermissionController::class, 'index'])->name('admin.permissions.index');
    Route::post('/admin/permissions',             [RolePermissionController::class, 'update'])->name('admin.permissions.update');

    // პაკეტები
    Route::get('/admin/packages',                 [PackageController::class, 'index'])->name('admin.packages.index');
    Route::post('/admin/packages',                [PackageController::class, 'store'])->name('admin.packages.store');
    Route::put('/admin/packages/{package}',       [PackageController::class, 'update'])->name('admin.packages.update');
    Route::delete('/admin/packages/{package}',    [PackageController::class, 'destroy'])->name('admin.packages.destroy');

    // გამოწერები
    Route::post('/admin/users/{user}/subscribe',  [SubscriptionController::class, 'assign'])->name('admin.subscriptions.assign');
    Route::get('/admin/users/{user}/subscriptions', [SubscriptionController::class, 'history'])->name('admin.subscriptions.history');

    // კლასები
    Route::get('/admin/grades',                    [GradeController::class, 'index'])->name('admin.grades.index');
    Route::post('/admin/grades',                   [GradeController::class, 'store'])->name('admin.grades.store');
    Route::put('/admin/grades/{grade}',             [GradeController::class, 'update'])->name('admin.grades.update');
    Route::patch('/admin/grades/{grade}/toggle',   [GradeController::class, 'toggleActive'])->name('admin.grades.toggle');
    Route::delete('/admin/grades/{grade}',         [GradeController::class, 'destroy'])->name('admin.grades.destroy');

    // თემები
    Route::get('/admin/themes',                      [ThemeController::class, 'index'])->name('admin.themes.index');
    Route::post('/admin/themes',                     [ThemeController::class, 'store'])->name('admin.themes.store');
    Route::patch('/admin/themes/{theme}/toggle',     [ThemeController::class, 'toggleActive'])->name('admin.themes.toggle');
    Route::delete('/admin/themes/{theme}',           [ThemeController::class, 'destroy'])->name('admin.themes.destroy');
    Route::get('/admin/themes/{theme}/vars',        [ThemeController::class, 'showVariables'])->name('admin.themes.variables');
    Route::post('/admin/themes/{theme}/vars',       [ThemeController::class, 'storeVariable'])->name('admin.themes.variables.store');
    Route::post('/admin/themes/{theme}/groups',     [ThemeController::class, 'storeGroup'])->name('admin.themes.groups.store');
    Route::put('/admin/var-groups/{group}',         [ThemeController::class, 'updateGroup'])->name('admin.themes.groups.update');
    Route::post('/admin/var-groups/{group}/slots',  [ThemeController::class, 'addSlot'])->name('admin.themes.groups.addSlot');
    Route::put('/admin/vars/{variable}',            [ThemeController::class, 'updateVariable'])->name('admin.themes.variables.update');
    Route::delete('/admin/vars/{variable}',         [ThemeController::class, 'destroyVariable'])->name('admin.themes.variables.destroy');
    Route::delete('/admin/var-groups/{group}',      [ThemeController::class, 'destroyGroup'])->name('admin.themes.groups.destroy');

    // თოპიქ-ვიდეოები
    Route::post('/admin/topics/{topic}/videos',   [TopicVideoController::class, 'store'])->name('admin.topic.videos.store');
    Route::delete('/admin/topic-videos/{video}',  [TopicVideoController::class, 'destroy'])->name('admin.topic.videos.destroy');

    // თოპიქები
    Route::get('/admin/topics',               [TopicController::class, 'index'])->name('admin.topics.index');
    Route::post('/admin/topics',              [TopicController::class, 'store'])->name('admin.topics.store');
    Route::post('/admin/topics/move',         [TopicController::class, 'moveToGrade'])->name('admin.topics.move');
    Route::put('/admin/topics/{topic}',       [TopicController::class, 'update'])->name('admin.topics.update');
    Route::delete('/admin/topics/{topic}',    [TopicController::class, 'destroy'])->name('admin.topics.destroy');

    // კითხვების შაბლონები
    Route::get('/admin/questions',                     [QuestionTemplateController::class, 'index'])->name('admin.questions.index');
    Route::get('/admin/questions/create',              [QuestionTemplateController::class, 'create'])->name('admin.questions.create');
    Route::post('/admin/questions',                    [QuestionTemplateController::class, 'store'])->name('admin.questions.store');
    Route::post('/admin/questions/pdf',                [QuestionTemplateController::class, 'exportPdf'])->name('admin.questions.pdf');
    Route::get('/admin/questions/{question}/edit',     [QuestionTemplateController::class, 'edit'])->name('admin.questions.edit');
    Route::put('/admin/questions/{question}',          [QuestionTemplateController::class, 'update'])->name('admin.questions.update');
    Route::delete('/admin/questions/{question}',       [QuestionTemplateController::class, 'destroy'])->name('admin.questions.destroy');
});
