<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PermitController extends Controller
{
    public function index()
    {
        $permits = Permit::orderBy('created_at', 'desc')->get();
        return response()->json($permits);
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'permit_number' => 'required|string|max:255|unique:permits,permit_number',
            'business_type' => 'nullable|string|max:255',
            'status' => 'required|in:Pending,Approved,Rejected,Suspended,Expired',
            'file' => 'nullable|file|max:2048'
        ]);

        $filePath = $request->hasFile('file') ? $request->file('file')->store('permits') : null;

        $permit = Permit::create([
            'business_name' => $request->business_name,
            'owner_name' => $request->owner_name,
            'permit_number' => $request->permit_number,
            'business_type' => $request->business_type,
            'status' => $request->status,
            'file' => $filePath
        ]);

        return response()->json($permit, 201);
    }

    public function update(Request $request, Permit $permit)
    {
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected,Suspended,Expired'
        ]);

        $permit->update(['status' => $request->status]);

        return response()->json($permit);
    }

    public function destroy(Permit $permit)
    {
        if ($permit->file) {
            Storage::delete($permit->file);
        }
        $permit->delete();

        return response()->json(['message' => 'Permit deleted']);
    }
}
