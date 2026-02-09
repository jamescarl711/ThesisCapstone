<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle($request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (strtolower(trim(Auth::user()->role)) !== strtolower(trim($role))) {
            // ❌ HUWAG mag logout
            abort(403, 'Unauthorized role');
        }

        return $next($request);
    }
}
