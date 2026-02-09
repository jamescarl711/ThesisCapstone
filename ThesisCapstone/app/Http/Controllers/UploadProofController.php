<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceRequestProof;

class UploadProofController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'service_request_id' => 'required|exists:service_requests,id',
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf'
        ]);

        // Store file
        $path = $request->file('proof')->store('proofs', 'public');

        // Save to DB (IMPORTANT: file_path must match DB column)
        ServiceRequestProof::create([
            'service_request_id' => $request->service_request_id,
            'file_path' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Proof uploaded successfully'
        ]);
    }
}
