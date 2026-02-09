<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PendingRegistration;
use App\Notifications\OTPNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class RegisterOtpController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|string|in:user,business', // <-- validate role
        ]);

        $email = strtolower(trim($request->email));

        // check existing account
        if (User::where('email', $email)->exists()) {
            return response()->json(['message' => 'Email already registered'], 422);
        }

        $otp = rand(100000, 999999);

        PendingRegistration::updateOrCreate(
            ['email' => $email],
            [
                'role' => $request->role,        // <-- add role here
                'otp' => $otp,
                'expires_at' => now()->addMinutes(5),
            ]
        );

        try {
            Notification::route('mail', $email)
                ->notify(new OTPNotification($otp));
        } catch (\Throwable $e) {
            Log::error('OTP Mail Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }

        return response()->json(['sent' => true]);
    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6'
        ]);

        $email = strtolower(trim($request->email));
        $otp = trim((string)$request->otp);

        $record = PendingRegistration::where('email', $email)->first();

        if(!$record)
            return response()->json(['message'=>'No request found'],404);

        if(now()->gt($record->expires_at))
            return response()->json(['message'=>'OTP expired'],422);

        if (!hash_equals((string)$record->otp, $otp))
            return response()->json(['message'=>'Invalid OTP'],422);

        session(['verified_email'=>$email]);

        return response()->json(['verified'=>true]);
    }
}
