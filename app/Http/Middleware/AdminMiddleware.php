<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403);
        }
        $user = auth()->user();
        if ($user->isAdmin() || $user->role === 'staff') {
            return $next($request);
        }
        // მასწავლებელს ადმინ რეჟიმი არ აქვს — მისი გვერდი კითხვების გვერდია
        if ($user->role === 'teacher') {
            return redirect()->route('teacher.questions.index');
        }
        abort(403);
    }
}
