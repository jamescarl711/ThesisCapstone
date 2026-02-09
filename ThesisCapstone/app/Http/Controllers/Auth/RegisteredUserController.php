<?php

namespace App\Http\Controllers\Auth;

use App\Models\PendingRegistration;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class RegisteredUserController extends Controller
{
    // ================== SHOW REGISTRATION PAGE ==================
    public function create()
    {
        return inertia('Auth/Register');
    }

    // ================== HANDLE REGISTRATION ==================
    public function store(Request $request)
    {
        // ================= OTP VERIFICATION CHECK =================
        if (session('verified_email') !== $request->email) {
            return back()->withErrors([
                'email' => 'Please verify your email first before registering.'
            ]);
        }

        $role = $request->role;

        // Base validation rules
        $rules = [
            'role' => ['required', Rule::in(['user','business'])],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ];

        // User-specific rules
        if ($role === 'user') {
            $rules = array_merge($rules, [
                'first_name' => 'required|string|max:255',
                'middle_initial' => 'nullable|string|max:1',
                'last_name' => 'required|string|max:255',
                'contact_number' => 'required|string|max:20',
            ]);
        }

        // Business-specific rules
        if ($role === 'business') {
            $rules = array_merge($rules, [
                'business_name' => 'required|string|max:255',
                'business_owner' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'contact_number' => 'required|string|max:20',
                'category' => 'required|string|max:50',
                'business_type' => ['required', Rule::in(['Individual','Small Business','Company'])],
                'bir_registration' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'dti_registration' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'mayor_permit' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'business_permit' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);
        }

        $validated = $request->validate($rules);

        // CREATE USER
        $userData = [
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_approved' => $validated['role'] === 'user' ? true : false,
            'first_name' => $validated['role'] === 'user' ? $validated['first_name'] : $validated['business_owner'],
            'middle_initial' => $validated['role'] === 'user' ? ($validated['middle_initial'] ?? null) : null,
            'last_name' => $validated['role'] === 'user' ? $validated['last_name'] : 'Business',
            'contact_number' => $validated['contact_number'],
            'latitude' => $request->latitude ?? null,
            'longitude' => $request->longitude ?? null,
        ];

        $user = User::create($userData);

        // CREATE BUSINESS RECORD IF NEEDED
        if ($user->role === 'business') {
            $bir = $request->file('bir_registration') ? $request->file('bir_registration')->store('business_docs', 'public') : null;
            $dti = $request->file('dti_registration') ? $request->file('dti_registration')->store('business_docs', 'public') : null;
            $mayor = $request->file('mayor_permit') ? $request->file('mayor_permit')->store('business_docs', 'public') : null;
            $permit = $request->file('business_permit') ? $request->file('business_permit')->store('business_docs', 'public') : null;

            Business::create([
                'user_id' => $user->id,
                'business_name' => $validated['business_name'],
                'owner_name' => $validated['business_owner'],
                'address' => $validated['address'],
                'contact_number' => $validated['contact_number'],
                'category' => $validated['category'],
                'business_type' => $validated['business_type'],
                'bir_registration' => $bir,
                'dti_registration' => $dti,
                'mayor_permit' => $mayor,
                'business_permit' => $permit,
            ]);
        }

        // CLEAR OTP SESSION
        session()->forget('verified_email');
        session()->forget('register_otp');
        session()->forget('register_email');
        session()->forget('otp_expires');

        return redirect()->route('login')
            ->with('status', 'Registration successful! Please wait for admin approval if required.');
    }

    // ================== LOGIN ==================
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (!$user->is_approved) {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is not approved yet.']);
            }

            return match ($user->role) {
                'user' => redirect()->route('user.dashboard'),
                'business' => redirect()->route('business.dashboard'),
                default => redirect()->route('dashboard')
            };
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    // ================== SEND OTP ==================
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'role'  => 'required|in:user,business',
        ]);

        $otp = rand(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        PendingRegistration::updateOrCreate(
            ['email' => $request->email],
            [
                'role'       => $request->role,  // <--- THIS FIXES THE ERROR
                'otp'        => $otp,
                'expires_at' => $expiresAt,
            ]
        );
        // Send OTP via email here
        Mail::to($request->email)->send(new OtpMail($otp));

        return response()->json(['message' => 'OTP sent']);
    }



    // ================== VERIFY OTP ==================
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        if (Session::get('otp_expires') < now()) {
            return response()->json(['message' => 'OTP expired'], 422);
        }

        if (
            Session::get('register_email') == $request->email &&
            Session::get('register_otp') == $request->otp
        ) {
            Session::put('verified_email', $request->email);

            return response()->json(['message' => 'Email verified']);
        }

        return response()->json(['message' => 'Invalid OTP'], 422);
    }

    // ================== LOGIN FOR USERS & BUSINESSES ==================
    

    // ================== ADMIN: FETCH ALL USERS ==================
    public function index()
    {
        return response()->json(
            User::with(['business', 'serviceProvider'])->latest()->get()
        );
    }

    // ================== ADMIN: SHOW SINGLE USER ==================
    public function show($id)
    {
        $user = User::with(['business', 'serviceProvider'])->findOrFail($id);
        return response()->json($user);
    }

    // ================== ADMIN: CREATE USER (FROM ADMIN PANEL) ==================
    public function storeAdminUser(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin','hr','finance','procurement','user','business','serviceprovider'])],
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_approved' => true, // Admin-created users auto-approved
        ]);

        return response()->json($user);
    }

    // ================== ADMIN: TOGGLE APPROVAL ==================
    
    public function toggleApproval($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->is_approved = !$user->is_approved; // toggles approved/pending
        $user->save();

        return response()->json(['is_approved' => $user->is_approved]);
    }


    // ================== ADMIN: DELETE USER ==================
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->business) $user->business->delete();
        if ($user->serviceProvider) $user->serviceProvider->delete();

        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }


    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $exists = \App\Models\User::where('email', $request->email)->exists();

        return response()->json(['exists' => $exists]);
    }
    
    public function reject(Request $request, $id)
    {
    $request->validate([
        'reason' => 'required|string|max:255',
    ]);

    $user = User::findOrFail($id);
    $user->is_approved = false;
    $user->status = 'rejected'; // or any status field you have
    $user->rejection_reason = $request->reason; // you may need to add this column
    $user->save();

    return response()->json([
        'message' => 'User rejected successfully'
    ]);
    }


}
