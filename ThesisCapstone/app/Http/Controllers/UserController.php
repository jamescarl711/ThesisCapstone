<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\Models\ServiceRequest;

class UserController extends Controller
{
    // ================== FETCH USER PROFILE ==================
    public function profile()
    {
        $user = Auth::user();
        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'middle_initial' => $user->middle_initial,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'contact_number' => $user->contact_number,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
            'role' => $user->role,
            'is_approved' => $user->is_approved,
        ]);
    }

    public function allBusinesses()
    {
        $businesses = Business::select(
            'id','user_id','business_name','owner_name','address','contact_number',
            'category','business_type','latitude','longitude'
        )
        ->where('status','Approved') // or whatever your approved column is
        ->get();

        return response()->json($businesses);
    }

    // Submit a service request
    public function submitServiceRequest(Request $request)
    {
        $request->validate([
            'business_id'     => 'required|exists:businesses,id',
            'service_type'    => 'required|string|max:255',
            'address_text'    => 'required|string|max:255',
            'notes'           => 'nullable|string|max:500',
            'preferred_date'  => 'required|date',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
        ]);

        $sr = ServiceRequest::create([
            'user_id'        => Auth::id(),
            'business_id'    => $request->business_id,
            'service_type'   => $request->service_type,
            'address_text'   => $request->address_text,
            'notes'          => $request->notes,
            'preferred_date' => $request->preferred_date, // ✅ FIXED
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'status'         => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'service_request_id' => $sr->id
        ]);
    }

    // ================== GET SERVICE PROVIDER DETAILS ==================
    public function serviceProviderDetails()
    {
        $user = Auth::user();

        $sp = ServiceProvider::where('user_id', $user->id)->first();

        if (!$sp) {
            return response()->json([
                'category' => null,
                'experience_years' => null,
                'service_description' => null,
                'is_approved' => false
            ]);
        }

        return response()->json([
            'category' => $sp->category,
            'experience_years' => $sp->experience_years,
            'service_description' => $sp->service_description,
            'is_approved' => (bool)$sp->is_approved
        ]);
    }

    // ================== UPDATE USER PROFILE ==================
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:1',
            'last_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->first_name = $request->first_name;
        $user->middle_initial = $request->middle_initial ?? null;
        $user->last_name = $request->last_name;
        $user->contact_number = $request->contact_number;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Profile updated successfully']);
    }

    // ================== UPDATE LATITUDE & LONGITUDE ==================
    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $user->latitude = $request->latitude;
        $user->longitude = $request->longitude;
        $user->save();

        return response()->json([
            'success' => true,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
        ]);
    }

    // ================== GET APPROVED BUSINESSES ==================
    public function approvedBusinesses()
    {
        // All businesses available for the user to browse
        $businesses = Business::where('status', 'Approved')
            ->get(['id', 'business_name', 'owner_name', 'category', 'status']);

        return response()->json($businesses);
    }

    // ================== GET APPROVED SERVICE PROVIDERS ==================
    public function approvedServiceProviders()
    {
        $providers = ServiceProvider::where('approved', true)
            ->with('user:id,first_name,middle_initial,last_name,email')
            ->get(['id', 'category', 'experience_years', 'user_id', 'latitude', 'longitude']);

        $providers = $providers->map(function ($p) {
            return [
                'id' => $p->id,
                'full_name' => $p->user->first_name . ' ' . ($p->user->middle_initial ?? '') . ' ' . $p->user->last_name,
                'email' => $p->user->email,
                'category' => $p->category,
                'experience_years' => $p->experience_years,
                'latitude' => $p->latitude,
                'longitude' => $p->longitude,
                'is_available' => (bool) $p->is_available,
            ];
        });

        return response()->json($providers);
    }

    // ================== CHECK SERVICE PROVIDER APPLICATION STATUS ==================
    public function applicationStatus()
{
    $user = Auth::user();
    $application = ServiceProvider::where('user_id', $user->id)->first();

    if (!$application) {
        return response()->json([
            'hasApplied' => false,
            'status' => 'none',
            'reject_reason' => null,
        ]);
    }

    $status = 'pending';
    if ($application->is_approved) {
        $status = 'approved';
    } elseif ($application->is_rejected) {
        $status = 'rejected';
    }


    return response()->json([
        'hasApplied' => true,
        'status' => $status,
        'reject_reason' => $application->reject_reason,
        'provider' => [
            'id' => $application->id,
            'category' => $application->category,
            'experience_years' => $application->experience_years,
            'service_description' => $application->service_description,
        ]
    ]);
}
public function applyServiceProvider(Request $request)
{
    $userId = Auth::id();

    // Check latest application
    $latestApp = ServiceProvider::where('user_id', $userId)->latest()->first();

        if ($latestApp) {
            if ($latestApp->status === 'pending') {
                return response()->json(['error' => 'You already have a pending application'], 400);
            }
            if ($latestApp->status === 'approved') {
                return response()->json(['error' => 'You are already an approved service provider'], 400);
            }
            // If status === 'rejected', allow new application
        }



    // Validate request
    $request->validate([
        'business_id' => 'required|exists:businesses,id',
        'category' => 'required|string',
        'experience_years' => 'required|integer|min:0',
        'service_description' => 'required|string',
        'valid_id' => 'required|file|mimes:jpg,png,pdf|max:2048',
    ]);

    // Store valid ID
    $validIdPath = $request->file('valid_id')->store('valid_ids', 'public');

    // Create new service provider application
    $sp = ServiceProvider::create([
        'user_id' => $userId,
        'business_id' => $request->business_id,
        'category' => $request->category,
        'experience_years' => $request->experience_years,
        'service_description' => $request->service_description,
        'valid_id' => $validIdPath,
        'status' => 'pending',
        'reject_reason' => null,
    ]);

    return response()->json([
        'message' => 'Application submitted successfully',
        'provider' => $sp
    ]);
}

public function apply(Request $request)
{
    $userId = auth()->id();

    // Check if user already has a pending or approved application
    $existing = ServiceProvider::where('user_id', $userId)
        ->whereIn('status', ['pending', 'approved']) // only block these
        ->first();

    if ($existing) {
        return response()->json(['error' => 'You already have a pending or approved application'], 400);
    }

    // Validate request
    $request->validate([
        'business_id' => 'required|exists:businesses,id',
        'category' => 'required|string',
        'experience_years' => 'required|integer',
        'service_description' => 'required|string',
        'valid_id' => 'required|file|mimes:jpg,png,pdf',
    ]);

    // Save valid ID
    $filePath = $request->file('valid_id')->store('valid_ids', 'public');

    // Create new application
    $sp = ServiceProvider::create([
        'user_id' => $userId,
        'business_id' => $request->business_id,
        'category' => $request->category,
        'experience_years' => $request->experience_years,
        'service_description' => $request->service_description,
        'valid_id' => $filePath,
        'status' => 'pending',
        'reject_reason' => null,
    ]);

    return response()->json(['message' => 'Application submitted successfully', 'provider' => $sp]);
}


}
