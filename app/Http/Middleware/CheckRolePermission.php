<?php

namespace App\Http\Middleware;

use App\Models\RolePermission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRolePermission
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) return $next($request);

        $user = auth()->user();

        if (! $user->is_active) {
            Auth::logout();
            return redirect('/')
                ->withErrors(['child_code' => 'ანგარიში გათიშულია — მშობლის გეგმა შეიცვალა']);
        }

        if ($user->role === 'admin') return $next($request);

        $routeName = $request->route()?->getName();
        if (!$routeName) return $next($request);

        $page = RolePermission::pageForRoute($routeName);
        if (!$page) return $next($request);

        $allowed = RolePermission::allowedPages($user->role);
        if ($allowed === null) return $next($request); // no rules set yet — allow all

        if (!in_array($page, $allowed)) abort(403, 'წვდომა შეზღუდულია');

        return $next($request);
    }
}
