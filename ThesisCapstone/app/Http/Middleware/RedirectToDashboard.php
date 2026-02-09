<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectToDashboard
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->serviceProvider && $user->serviceProvider->is_approved) {
            if (!$request->is('service-provider/dashboard')) {
                return redirect()->route('service-provider.dashboard');
            }
        }

        return $next($request);
    }
}
