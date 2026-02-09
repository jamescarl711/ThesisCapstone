<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class HrRequestStatusController extends Controller
{
    public function accepted()
    {
        return ServiceRequest::query()
            ->with(['user', 'business'])
            ->where('status', 'accepted')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($req) => [
                'id' => $req->id,
                'user' => [
                    'first_name' => $req->user?->first_name,
                    'middle_initial' => $req->user?->middle_initial,
                    'last_name' => $req->user?->last_name,
                ],
                'business' => [
                    'business_name' => $req->business?->business_name,
                ],
                'service_type' => $req->service_type,
                'preferred_date' => optional($req->preferred_date)->format('Y-m-d'),
                'address_text' => $req->address_text,
                'status' => $req->status,
            ]);
    }

    public function rejected()
    {
        return ServiceRequest::query()
            ->with(['user', 'business'])
            ->where('status', 'rejected')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($req) => [
                'id' => $req->id,
                'user' => [
                    'first_name' => $req->user?->first_name,
                    'middle_initial' => $req->user?->middle_initial,
                    'last_name' => $req->user?->last_name,
                ],
                'business' => [
                    'business_name' => $req->business?->business_name,
                ],
                'service_type' => $req->service_type,
                'preferred_date' => optional($req->preferred_date)->format('Y-m-d'),
                'address_text' => $req->address_text,
                'status' => $req->status,
            ]);
    }
}
