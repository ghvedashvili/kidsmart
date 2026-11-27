<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ბავშვის შემოსვლის დღის აღნიშვნა („დაბრუნების“ მაჩვენებლისთვის).
 * დღეში მაქსიმუმ ერთი ჩაწერა — სესიაში ვიმახსოვრებთ, რომ დღევანდელი უკვე ჩაიწერა.
 */
class TrackChildActivity
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && $user->role === 'child' && $request->hasSession()) {
            $today = now()->toDateString();
            if ($request->session()->get('ks_active_day') !== $today) {
                try {
                    DB::table('child_activity_days')->insertOrIgnore(['child_id' => $user->id, 'day' => $today]);
                    $request->session()->put('ks_active_day', $today);
                } catch (\Throwable $e) {
                    // ცხრილი ჯერ არ არსებობს (migrate არ გაშვებულა) — გვერდს არ ვაფუჭებთ
                }
            }
        }
        return $next($request);
    }
}
