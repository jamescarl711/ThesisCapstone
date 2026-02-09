<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceRequest; 
use App\Models\Business;      
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserServiceRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $requests = ServiceRequest::with('business')
            ->where('user_id', $user->id)
            ->get();

        return response()->json($requests);
    }

    public function store(Request $request)
{
    $user = Auth::user();

    $validator = Validator::make($request->all(), [
        'business_id' => 'required|exists:businesses,id',
        'service_type' => 'required|string',
        'address_text' => 'required|string',
        'notes' => 'nullable|string',
        'preferred_date' => 'required|date', // ⭐ ADD THIS
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => $validator->errors()->first()
        ], 422);
    }

    $serviceRequest = ServiceRequest::create([
        'user_id' => $user->id,
        'business_id' => $request->business_id,
        'service_provider_id' => null,
        'service_type' => $request->service_type,
        'address_text' => $request->address_text,
        'notes' => $request->notes,
        'preferred_date' => $request->preferred_date, // ⭐ ADD THIS
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
        'status' => 'pending',
    ]);

    return response()->json($serviceRequest);
}
public function assignedRequests()
{
    $user = Auth::user();

    $requests = ServiceRequest::with(['user', 'business'])
        ->where('service_provider_id', $user->id)
        ->get()
        ->map(function($r) {
            return [
                'id' => $r->id,
                'user_id' => $r->user_id,
                'business_id' => $r->business_id,
                'service_provider_id' => $r->service_provider_id,
                'service_type' => $r->service_type,
                'latitude' => $r->latitude,
                'longitude' => $r->longitude,
                'address_text' => $r->address_text,
                'preferred_date' => $r->preferred_date,
                'notes' => $r->notes,
                'status' => $r->status,
                'created_at' => $r->created_at->format('Y-m-d H:i'),
                'updated_at' => $r->updated_at->format('Y-m-d H:i'),
                'customer_name' => $r->user ? ($r->user->first_name . ' ' . ($r->user->middle_initial ? $r->user->middle_initial . '. ' : '') . $r->user->last_name) : 'N/A',
            ];
        });

    return response()->json($requests);
}

}
