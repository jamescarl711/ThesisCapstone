<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessPermit;

class BusinessPermitController extends Controller
{
    public function index()
    {
        $permits = BusinessPermit::all();
        return response()->json($permits);
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
        ]);

        $permit = BusinessPermit::create($request->all());
        return response()->json($permit);
    }

    public function update(Request $request, $id)
    {
        $permit = BusinessPermit::findOrFail($id);
        $permit->update($request->all());
        return response()->json($permit);
    }

    public function destroy($id)
    {
        BusinessPermit::destroy($id);
        return response()->json(['message'=>'Deleted successfully']);
    }
}
