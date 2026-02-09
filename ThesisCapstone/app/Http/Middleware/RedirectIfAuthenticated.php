<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
        {
            $guards = empty($guards) ? [null] : $guards;

            foreach ($guards as $guard) {
                if (Auth::guard($guard)->check()) {
                    // Only redirect logged-in users trying to access guest pages
                    if ($request->routeIs('login') || $request->routeIs('register')) {
                        $user = Auth::user();
                        $role = strtolower(trim($user->role));

                        return match ($role) {
                            'admin' => redirect()->route('admin.dashboard'),
                            'hr' => redirect()->route('hr.dashboard'),
                            'finance' => redirect()->route('finance.dashboard'),
                            'procurement' => redirect()->route('procurement.dashboard'),
                            'business' => redirect()->route('business.dashboard'),
                            'serviceprovider' => redirect()->route('service-provider.dashboard'),
                            default => redirect()->route('user.dashboard'),
                        };
                    }

                    // Let logged-in users access all other pages
                    return $next($request);
                }
            }

            // Guests can access guest pages freely
            return $next($request);
        }

}