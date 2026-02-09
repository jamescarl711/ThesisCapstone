<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $userRole = strtolower(trim($request->user()->role));
        $roles = array_map(fn($r) => strtolower(trim($r)), $roles);

        if (!in_array($userRole, $roles)) {
            return redirect()->route('login')->with('error', 'You do not have access to this page.');
        }

        return $next($request);
    }

}
