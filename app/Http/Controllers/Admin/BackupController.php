<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\BackupDatabase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/** სარეზერვო ასლების სია, „ახლავე შექმნა“, ჩამოტვირთვა და წაშლა — მხოლოდ ადმინი */
class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->isAdmin(), 403, 'სარეზერვო ასლები მხოლოდ ადმინს უჩანს.');
            return $next($request);
        });
    }

    public function index()
    {
        $files = collect(glob($this->dir() . '/kidsmart-*.gz') ?: [])
            ->map(fn ($p) => ['name' => basename($p), 'size' => filesize($p), 'at' => \Illuminate\Support\Carbon::createFromTimestamp(filemtime($p))])
            ->sortByDesc('name')->values();

        return view('admin.backups.index', [
            'files'    => $files,
            'lastAt'   => $files->first()['at'] ?? null,
        ]);
    }

    public function store()
    {
        @set_time_limit(300);
        $code = Artisan::call('ks:backup');
        $out  = trim(Artisan::output());
        return back()->with($code === 0 ? 'success' : 'error', $out ?: ($code === 0 ? 'შეიქმნა.' : 'ვერ შეიქმნა.'));
    }

    public function download(string $file)
    {
        return response()->download($this->path($file));
    }

    public function destroy(string $file)
    {
        @unlink($this->path($file));
        return back()->with('success', "„{$file}“ წაიშალა.");
    }

    private function dir(): string
    {
        return storage_path('app/' . BackupDatabase::DIR);
    }

    private function path(string $file): string
    {
        abort_unless(preg_match('/^kidsmart-[0-9_-]+\.(sql|sqlite)\.gz$/', $file), 404);
        $p = $this->dir() . '/' . $file;
        abort_unless(is_file($p), 404);
        return $p;
    }
}
