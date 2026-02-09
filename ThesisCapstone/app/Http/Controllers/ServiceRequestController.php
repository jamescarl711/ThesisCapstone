<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\ServiceProvider;

class ServiceRequestController extends Controller
{
    // GET /business/service-requests
    public function index()
    {
        // Get service provider for current user
        $sp = ServiceProvider::where('user_id', auth()->id())->first();

        if (!$sp) {
            return response()->json([], 200);
        }

        // Get all requests assigned to this provider
        $requests = ServiceRequest::with('user', 'business')
            ->where('service_provider_id', $sp->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'user_name' => $r->user->first_name 
                                   . ($r->user->middle_initial ? ' '.$r->user->middle_initial.'.' : '') 
                                   . ' '.$r->user->last_name,
                    'service_name' => $r->service_type, // the exact service user requested
                    'status' => $r->status,
                    'details' => $r->notes,
                    'address' => $r->address_text,
                    'preferred_date' => $r->preferred_date,
                    'business_name' => $r->business->business_name ?? null,
                    'category' => $r->business->category ?? null,
                ];
            });

        return response()->json($requests);
    }

 public function store(Request $request)
{
    $request->validate([
        'business_id' => 'required|exists:businesses,id',
        'service_type' => 'required|string|max:50',
        'address_text' => 'required|string|max:255',
        'notes' => 'nullable|string|max:500',
        'preferred_date' => 'nullable|date', // ✅ allow null just in case
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
    ]);

    // Get current user
    $user = auth()->user();

    // Assign default values if null
    $latitude = $request->latitude ?? 0;
    $longitude = $request->longitude ?? 0;
    $preferred_date = $request->preferred_date ?: null;

    $serviceRequest = ServiceRequest::create([
        'user_id' => $user->id,
        'business_id' => $request->business_id,
        'service_provider_id' => null, // initially no provider assigned
        'service_type' => $request->service_type,
        'address_text' => $request->address_text,
        'notes' => $request->notes,
        'preferred_date' => $preferred_date,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'status' => 'pending',
    ]);

    return response()->json([
        'success' => true,
        'request' => $serviceRequest
    ]);
}


    // POST /business/service-requests/{id}/review
    public function review(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string',
        ]);

        $sr = ServiceRequest::findOrFail($id);

        if ($sr->service_provider_id !== auth()->user()->serviceProvider->id) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        if ($request->action === 'approve') {
            $sr->status = 'approved';
        } elseif ($request->action === 'reject') {
            $sr->status = 'rejected';
            $sr->notes .= $request->reason ? " | Rejection reason: ".$request->reason : '';
        }

        $sr->save();

        return response()->json(['success' => true]);
    }
    public function assignedForProvider()
    {
        $provider = auth()->user()->serviceProvider;
        if(!$provider) {
            return response()->json(['error' => 'No service provider assigned'], 404);
        }

        $requests = ServiceRequest::where('service_provider_id', $provider->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($requests);
    }
   /**
 * Get assigned requests for the logged-in service provider
 */
public function assignedRequests()
{
    // Kunin service provider record ng current logged-in user
    $provider = ServiceProvider::where('user_id', auth()->id())->first();

    if (!$provider) {
        return response()->json([]);
    }

    // Fetch requests assigned to THIS provider
    $requests = ServiceRequest::where('service_provider_id', $provider->id)
        ->where('status', 'assigned') // optional: only assigned
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($r) {
            return [
                'id' => $r->id,
                'user_id' => $r->user_id,
                'customer_name' => $r->user ? $r->user->first_name . ' ' . $r->user->last_name : 'N/A',
                'business_id' => $r->business_id,
                'service_type' => $r->service_type,
                'address' => $r->address_text,
                'preferred_date' => $r->preferred_date,
                'notes' => $r->notes,
                'status' => $r->status,
                'created_at' => $r->created_at->format('M d, Y H:i'),
            ];
        });

    return response()->json($requests);
}



}
