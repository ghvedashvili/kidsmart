<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->user() &&
            is_null($request->user()->role) &&
            ! $request->routeIs('logout')
        ) {
            \Illuminate\Support\Facades\Auth::logout();
            return redirect('/');
        }

        return $next($request);
    }
}
