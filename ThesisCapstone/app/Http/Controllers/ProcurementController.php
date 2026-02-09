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
    public function markJobReady(Request $request, $id)
    {
        $r = ServiceRequest::findOrFail($id);

        if(!$r->materials_needed){
            return response()->json(['error'=>'No materials added yet'], 400);
        }

        $r->status = 'job_ready';
        $r->save();

        return response()->json(['success'=>true]);
    }

    // Add prepared materials to a service request
    public function addMaterials(Request $request, $id)
    {
        $service = ServiceRequest::findOrFail($id);

        foreach ($request->materials as $mat) {
            RequestMaterial::create([
                'service_request_id' => $service->id,
                'material_name' => $mat['name'],
                'quantity' => $mat['qty'],
                'unit' => $mat['unit'],
            ]);
        }

        $service->status = 'job_ready';
        $service->save();

        return response()->json(['message' => 'Materials prepared. Job ready.']);
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
