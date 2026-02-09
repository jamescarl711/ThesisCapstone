<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\ServiceProvider;
use Inertia\Inertia;

class DashboardController extends Controller
{
    // Business dashboard
    public function business()
    {
        $user = Auth::user();

        $business = Business::with('user')->where('user_id', $user->id)->first();

        if (!$business) {
            return redirect()->route('register')->with('error', 'Please complete your business profile.');
        }

        // Add status field based on user approval
        $business->status = $user->is_approved ? 'Approved' : 'Pending';

        return Inertia::render('BusinessDashboard', [
            'business' => $business
        ]);
    }

    // Service Provider dashboard
    public function serviceProvider()
    {
        $user = Auth::user();

        $provider = ServiceProvider::firstOrCreate(
            ['user_id' => $user->id],
            [
                'category' => 'plumbing',
                'service_description' => 'Please update your description',
                'experience_years' => 0,
                'valid_id' => null,
            ]
        );

        $provider = ServiceProvider::where('user_id', $user->id)->first();

        // Add status field based on user approval
        $provider->status = $user->is_approved ? 'Approved' : 'Not Approved';

        return Inertia::render('ServiceProviderDashboard', [
            'provider' => $provider
        ]);
    }
}
