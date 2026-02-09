<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\RequestMaterial;
use App\Models\ServiceMaterialTemplate;

class ProcurementController extends Controller
{
    // List requests that are awaiting materials
    public function awaitingMaterial()
    {
        $requests = ServiceRequest::with('user', 'business')
            ->where('status', 'awaiting_material')
            ->orderByDesc('created_at')
            ->get()
            ->map(function($r) {
                return [
                    'id' => $r->id,
                    'first_name' => $r->user->first_name ?? 'N/A',
                    'middle_initial' => $r->user->middle_initial ?? '',
                    'last_name' => $r->user->last_name ?? '',
                    'service_type' => $r->service_type,
                    'preferred_date' => $r->preferred_date,
                    'address_text' => $r->address_text,
                    'business_name' => $r->business->business_name ?? 'N/A',
                    'notes' => $r->notes ?? '',
                    'status' => $r->status,
                ];
            });

        return response()->json($requests);
    }

    // Mark a service request as Job Ready
    // Mark job ready
    public function markJobReady($id)
{
    // Kunin ang request
    $serviceRequest = ServiceRequest::find($id);

    if (!$serviceRequest) {
        return response()->json(['message' => 'Service request not found'], 404);
    }

    // Siguraduhing current status ay awaiting_material
    if ($serviceRequest->status !== 'awaiting_material') {
        return response()->json([
            'message' => 'Cannot mark job ready. Current status: ' . $serviceRequest->status
        ], 400);
    }

    // Update status to job_ready
    $serviceRequest->status = 'job_ready';
    $serviceRequest->save();

    return response()->json([
        'success' => true,
        'status' => $serviceRequest->status
    ]);
}

    // Get suggested materials based on service type
    public function getSuggested($requestId)
    {
        $request = ServiceRequest::findOrFail($requestId);

        $materials = ServiceMaterialTemplate::where('service_type', $request->service_type)
            ->get()
            ->map(function($m){
                return [
                    'material_name' => $m->material_name,
                    'quantity' => $m->default_qty,
                    'unit' => $m->unit
                ];
            });

        return response()->json($materials);
    }


}
