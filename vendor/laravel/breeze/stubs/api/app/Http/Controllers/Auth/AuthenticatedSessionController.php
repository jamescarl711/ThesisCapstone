<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // ❌ Block unapproved users
        if (!$user->is_approved) {
            Auth::logout();

            return Inertia::render('Auth/Login', [
                'errors' => ['email' => 'Your account is pending admin approval.']
            ]);
        }

        // ✅ Role-based redirect
        $redirectRoute = match ($user->role) {
            'admin' => route('admin.dashboard'),
            'finance' => route('finance.dashboard'),
            'hr' => route('hr.dashboard'),
            'procurement' => route('procurement.dashboard'),
            'business' => route('business.dashboard'),
            'serviceprovider' => route('serviceprovider.dashboard'),
            default => route('user.dashboard'),
        };

        // Full page redirect for SPA
        return Inertia::location($redirectRoute);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location(route('login'));
    }
}
