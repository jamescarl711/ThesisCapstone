<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceProvider;
use App\Models\WorkHistory;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use App\Models\ServiceRequest;
use App\Models\Business;
use App\Models\ServiceRequestProof;

class ServiceProviderController extends Controller
{
    /**
     * Service Provider Dashboard
     */
   public function dashboard()
{
    $provider = ServiceProvider::where('user_id', Auth::id())
        ->with('workHistory')
        ->first();

    $user = Auth::user();

    $reviews = $provider ? Review::where('service_provider_id', $provider->id)
        ->with('user')
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($r) {
            return [
                'id' => $r->id,
                'user_name' => $r->anonymous ? 'Anonymous' : $r->user->first_name . ' ' . $r->user->last_name,
                'rating' => $r->rating,
                'review' => $r->review,
                'created_at' => $r->created_at->format('M d, Y')
            ];
        }) : [];

    $serviceRequests = $provider ? ServiceRequest::where('service_provider_id', $provider->id)
        ->where('status', 'pending')
        ->with('user')
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($r) {
            return [
                'id' => $r->id,
                'user_id' => $r->user_id,
                'user_name' => $r->user ? $r->user->first_name . ' ' . $r->user->last_name : 'N/A',
                'service_type' => $r->service_type,
                'description' => $r->description,
                'latitude' => $r->latitude,
                'longitude' => $r->longitude,
                'status' => $r->status,
                'created_at' => $r->created_at->format('M d, Y H:i'),
            ];
        }) : [];

    return response()->json([
        'provider' => $provider ? [
            'id' => $provider->id,
            'user_id' => $provider->user_id,
            'business_id' => $provider->business_id,
            'category' => $provider->category,
            'service_description' => $provider->service_description,
            'experience_years' => $provider->experience_years,
            'valid_id' => $provider->valid_id,
            'latitude' => $provider->latitude,
            'longitude' => $provider->longitude,
            'is_approved' => $provider->is_approved,
            'is_rejected' => $provider->is_rejected,
            'reject_reason' => $provider->reject_reason,
            'is_available' => $provider->is_available,
            'created_at' => $provider->created_at->toDateTimeString(),
            'updated_at' => $provider->updated_at->toDateTimeString(),
        ] : null,
        'user' => [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'middle_initial' => $user->middle_initial,
            'last_name' => $user->last_name,
            'name' => $user->first_name . ' ' . ($user->middle_initial ? $user->middle_initial . '. ' : '') . $user->last_name,
            'email' => $user->email,
            'contact_number' => $user->contact_number,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
        ],
        'work_history' => $provider ? $provider->workHistory : [],
        'reviews' => $reviews,
        'service_requests' => $serviceRequests,
    ]);
}

    /**
     * List approved businesses for selection
     */
    public function allBusinesses()
    {
        $businesses = Business::where('is_approved', true)->get();
        return response()->json($businesses);
    }

    /**
     * Check if user has applied
     */
    public function applicationStatus()
{
    $provider = ServiceProvider::with('business')->where('user_id', Auth::id())->first();

    if (!$provider) {
        return response()->json([
            'hasApplied' => false,
            'pending' => false,
            'approved' => false,
            'rejected' => false,
            'provider' => [
                'id' => null,
                'business_name' => '',
                'category' => '',
                'experience_years' => 0,
                'service_description' => '',
                'status' => 'N/A',
            ],
        ]);
    }

    return response()->json([
        'hasApplied' => true,
        'pending' => !$provider->is_approved && !$provider->is_rejected,
        'approved' => (bool)$provider->is_approved,
        'rejected' => (bool)$provider->is_rejected,
        'provider' => [
            'id' => $provider->id,
            'business_name' => $provider->business ? $provider->business->name : '',
            'category' => $provider->category ?? '',
            'experience_years' => $provider->experience_years ?? 0,
            'service_description' => $provider->service_description ?? '',
            'status' => $provider->is_approved ? 'Approved' : ($provider->is_rejected ? 'Rejected' : 'Pending'),
        ],
    ]);
}


    /**
     * Apply as service provider
     */
public function apply(Request $request)
{
    $request->validate([
        'business_id' => 'required|exists:businesses,id',
        'category' => 'required|string|max:255',
        'service_description' => 'required|string|max:1000',
        'experience_years' => 'required|integer|min:0',
        'valid_id' => 'nullable|file|mimes:jpg,png,pdf|max:2048',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
    ]);

    $data = [
        'user_id' => Auth::id(),
        'business_id' => $request->business_id,
        'category' => $request->category,
        'service_description' => $request->service_description,
        'experience_years' => $request->experience_years,
        'valid_id' => $request->file('valid_id')?->store('valid_ids', 'public'),
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
        'is_approved' => 0,
        'is_rejected' => 0,
        'reject_reason' => null,
    ];

    $existing = ServiceProvider::where('user_id', Auth::id())->first();
    if ($existing) {
        $existing->update($data); // re-apply after rejection
    } else {
        ServiceProvider::create($data);
    }

    return response()->json(['success' => true, 'message' => 'Application submitted successfully']);
}


    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name' => 'required|string|max:50',
            'middle_initial' => 'nullable|string|max:5',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email',
            'contact_number' => 'required|string|max:20'
        ]);

        $user->update($request->only(['first_name', 'middle_initial', 'last_name', 'email', 'contact_number']));

        return response()->json(['success' => true, 'user' => $user]);
    }

    /**
     * List all service providers (for user)
     */
    public function listForUser()
    {
        $providers = ServiceProvider::with('user')
            ->where('is_approved', 1)  // only approved providers
            ->where('is_rejected', 0)  // ignore rejected
            ->orderBy('latitude', 'asc')
            ->get();

        return response()->json($providers->map(function ($sp) {
            return [
                'id' => $sp->id,
                'user_id' => $sp->user_id,
                'user_name' => $sp->user ? $sp->user->first_name . ' ' . $sp->user->last_name : 'N/A',
                'full_name' => $sp->user ? trim($sp->user->first_name . ' ' . ($sp->user->middle_initial ? $sp->user->middle_initial . '. ' : '') . $sp->user->last_name) : 'N/A',
                'category' => $sp->category,
                'service_description' => $sp->service_description,
                'experience_years' => $sp->experience_years,
                'valid_id' => $sp->valid_id,
                'latitude' => $sp->latitude,
                'longitude' => $sp->longitude,
                'is_approved' => (bool)$sp->is_approved,
                'is_rejected' => (bool)$sp->is_rejected,
                'is_available' => (bool)$sp->is_available,
            ];
        }));
    }


    public function toggleAvailability(Request $request)
    {
        $sp = ServiceProvider::where('user_id', auth()->id())->firstOrFail();
        $sp->is_available = !$sp->is_available; // toggle
        $sp->save();

        return response()->json([
            'is_available' => $sp->is_available
        ]);
    }

    public function dashboardData()
    {
        $user = auth()->user();
        $provider = ServiceProvider::where('user_id', $user->id)->first();

        $businesses = $provider ? $provider->businesses : [];

        return response()->json([
            'user' => $user,
            'provider' => $provider,
            'businesses' => $businesses
        ]);
    }

    /**
     * Add work history
     */
    public function addWorkHistory(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'year' => 'nullable|digits:4',
            'description' => 'nullable|string|max:500',
        ]);

        $provider = ServiceProvider::where('user_id', Auth::id())->firstOrFail();

        $history = WorkHistory::create([
            'service_provider_id' => $provider->id,
            'title' => $request->title,
            'location' => $request->location,
            'year' => $request->year,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'history' => $history]);
    }

    /**
     * Update service provider info
     */
    public function update(Request $request)
    {
        $provider = ServiceProvider::where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'category' => 'required|in:plumbing,siphoning,both',
            'service_description' => 'required|string|max:500',
            'experience_years' => 'required|integer|min:0',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'valid_id' => 'nullable|file|mimes:jpg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('valid_id')) {
            $provider->valid_id = $request->file('valid_id')->store('valid_ids', 'public');
        }

        $provider->fill([
            'category' => $request->category,
            'service_description' => $request->service_description,
            'experience_years' => $request->experience_years,
            'latitude' => $request->latitude ?? $provider->latitude,
            'longitude' => $request->longitude ?? $provider->longitude,
        ])->save();

        return response()->json(['success' => true, 'provider' => $provider]);
    }

    /**
     * Service Provider Details
     */
    public function details()
    {
        $user = Auth::user();
        $sp = ServiceProvider::where('user_id', $user->id)->first();

        if (!$sp) {
            return response()->json([
                'category' => '',
                'experience_years' => '',
                'service_description' => '',
            ]);
        }

        return response()->json([
            'category' => $sp->category,
            'experience_years' => $sp->experience_years,
            'service_description' => $sp->service_description,
        ]);
    }
    // ServiceProviderController.php
    public function index()
    {
        $sp = ServiceProvider::with('user')->where('user_id', auth()->id())->first();

        if (request()->wantsJson()) {
            return response()->json([
                'provider' => $sp,
                'user' => $sp->user,
                'work_history' => $sp->workHistory,
                'reviews' => $sp->reviews,
            ]);
        }

        return Inertia::render('ServiceProviderDashboard');
    }
    
public function assignedRequests()
{
    // Kunin ang service provider record ng logged-in user
    $sp = ServiceProvider::where('user_id', auth()->id())->first();

    if (!$sp) {
        return response()->json([], 200);
    }

    $requests = ServiceRequest::with(['user', 'business'])
        ->where('service_provider_id', $sp->id)
        ->orderByDesc('created_at')
        ->get()
        ->map(function($r) {
            $user = $r->user;
            $business = $r->business;

            return [
                'id' => $r->id,
                'user_id' => $r->user_id,
                'first_name' => $user->first_name ?? 'N/A',
                'middle_initial' => $user->middle_initial ?? '',
                'last_name' => $user->last_name ?? 'N/A',
                'service_type' => $r->service_type ?? 'N/A',
                'status' => $r->status ?? 'N/A',
                'notes' => $r->notes ?? 'N/A',
                'address_text' => $r->address_text ?? 'N/A',
                'preferred_date' => $r->preferred_date ? $r->preferred_date->format('Y-m-d') : 'N/A',
                'business_name' => $business->business_name ?? 'N/A',
                'category' => $business->category ?? 'N/A',
            ];
        });

    return response()->json($requests);
}



public function acceptRequest($id)
{
    $sr = ServiceRequest::findOrFail($id);

    if ($sr->status !== 'assigned') {
        return response()->json([
            'success' => false,
            'message' => 'Request not available for acceptance'
        ], 400);
    }

    $sr->status = 'accepted';
    $sr->save();

    return response()->json([
        'success' => true,
        'message' => 'Request accepted successfully',
        'data'    => $sr->only(['id','status','service_provider_id'])
    ]);
}

public function updateRequest(Request $request, $id)
{
    $req = ServiceRequest::findOrFail($id);

    $status = $request->status;

    // Define allowed transitions
    $allowedStatuses = ['awaiting_material', 'job_ready', 'in_progress', 'completed', 'rejected'];

    if (!in_array($status, $allowedStatuses)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid status'
        ], 400);
    }

    switch ($status) {
        case 'awaiting_material':
            if ($req->status !== 'assigned') {
                return response()->json(['success'=>false,'message'=>'Cannot set awaiting_material'], 400);
            }
            $req->status = 'awaiting_material';
            $req->rejection_reason = null;
            break;

        case 'rejected':
            if ($req->status !== 'assigned') {
                return response()->json(['success'=>false,'message'=>'Cannot reject'], 400);
            }
            $req->status = 'rejected';
            $req->rejection_reason = $request->reason ?? null;
            break;

        case 'job_ready':
            if ($req->status !== 'awaiting_material') {
                return response()->json(['success'=>false,'message'=>'Cannot mark job_ready'], 400);
            }
            $req->status = 'job_ready';
            break;

        case 'in_progress':
            if ($req->status !== 'job_ready') {
                return response()->json(['success'=>false,'message'=>'Cannot start job'], 400);
            }
            $req->status = 'in_progress';
            break;

        case 'completed':
            if ($req->status !== 'in_progress') {
                return response()->json(['success'=>false,'message'=>'Cannot complete job'], 400);
            }
            $req->status = 'completed';
            break;
    }

    $req->save();

    return response()->json([
        'success' => true,
        'status' => $req->status
    ]);
}


public function completeJob(Request $request, $id)
{
    $request->validate([
        'photos' => 'required|array|min:1',
        'photos.*' => 'image|max:5120',
    ]);

    $serviceRequest = ServiceRequest::findOrFail($id);

    $sp = ServiceProvider::where('user_id', auth()->id())->first();
    if ($serviceRequest->service_provider_id !== $sp->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $uploadedPaths = [];
    foreach ($request->file('photos') as $file) {
        $path = $file->store('public/service_proofs');
        $uploadedPaths[] = $path;

        ServiceRequestProof::create([
            'service_request_id' => $serviceRequest->id,
            'file_path' => $path
        ]);
    }

    $serviceRequest->status = 'completed';
    $serviceRequest->save();

    return response()->json([
        'message' => 'Job completed successfully',
        'files' => $uploadedPaths
    ]);
}




}

