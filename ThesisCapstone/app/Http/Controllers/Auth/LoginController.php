<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoginController extends Controller
{
    // Login
    public function login(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // Only business/service provider require admin approval
        if (in_array(strtolower($user->role), ['business','serviceprovider']) && !$user->is_approved) {
            Auth::logout();
            return Inertia::render('Auth/Login', [
                'error' => 'Your account is pending admin approval.'
            ]);
        }

        // Redirect based on role
        return $this->redirectBasedOnRole($user);
    }

    // Redirect users after login
    protected function redirectBasedOnRole($user)
    {
        $role = strtolower(trim($user->role));

        switch ($role) {
            case 'admin':
                return redirect()->intended(route('admin.dashboard'));
            case 'hr':
                return redirect()->intended(route('hr.dashboard'));
            case 'finance':
                return redirect()->intended(route('finance.dashboard'));
            case 'procurement':
                return redirect()->intended(route('procurement.dashboard'));
            case 'business':
                return redirect()->intended(route('business.dashboard'));
            case 'serviceprovider':
                return redirect()->intended(route('service-provider.dashboard'));
            case 'employee':
                return redirect()->intended(route('employee.dashboard'));
            default:
                return redirect()->intended(route('user.dashboard'));
        }
    }

    // Logout
    public function logout()
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return Inertia::location(route('login'));
    }
}
