<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Get all users
    public function index()
    {
        return response()->json(User::all());
    }

    // Create new user
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:5',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'role'       => 'required|string|max:50',
            'contact_number' => 'nullable|string|max:20',
            'latitude'   => 'nullable|string',
            'longitude'  => 'nullable|string',
        ]);

        $user = User::create([
            'first_name'      => $request->first_name,
            'middle_initial'  => $request->middle_initial,
            'last_name'       => $request->last_name,
            'email'           => $request->email,
            'role'            => $request->role,
            'contact_number'  => $request->contact_number,
            'latitude'        => $request->latitude,
            'longitude'       => $request->longitude,
            'password'        => bcrypt('default123'), // default password
            'is_approved'     => false,
        ]);

        return response()->json($user, 201);
    }
}
