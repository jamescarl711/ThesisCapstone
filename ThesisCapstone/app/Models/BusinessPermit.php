<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessPermit;

class BusinessPermitController extends Controller
{
    public function index()
    {
        return BusinessPermit::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
        ]);

        return BusinessPermit::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $permit = BusinessPermit::findOrFail($id);
        $permit->update($request->all());
        return $permit;
    }

    public function destroy($id)
    {
        BusinessPermit::destroy($id);
        return response()->json(['message'=>'Deleted successfully']);
    }
}
