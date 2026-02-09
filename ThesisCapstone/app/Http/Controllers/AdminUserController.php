<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // List all users
    // List all users (for admin user management page)
public function index()
{
    $users = User::all();
    return Inertia::render('Admin/UserManagement', ['users' => $users]);
}

// Add user (admin, hr, finance, procurement only)
public function store(Request $request)
{
     $request->validate([
        'first_name' => 'required|string|max:255',
        'middle_initial' => 'nullable|string|max:1',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
        'role' => ['required', Rule::in(['admin','hr','finance','procurement'])],
    ]);

    User::create([
        'first_name' => $request->first_name,
        'middle_initial' => $request->middle_initial,
        'last_name' => $request->last_name,
        'email' => $request->email,
        'contact_number' => null, // ✅ allowed now
        'password' => Hash::make($request->password),
        'role' => $request->role,
        'status' => 'active',
        'is_approved' => true,
    ]);

        return response()->json(['message' => 'User created successfully'], 201);
    }

    // Approve business/serviceprovider users
    public function approve($id)
    {
        $user = User::findOrFail($id);
        if (in_array($user->role, ['business', 'serviceprovider'])) {
            $user->is_approved = true;
            $user->save();
            return response()->json(['message' => 'User approved']);
        }
        return response()->json(['message' => 'Cannot approve this user'], 403);
    }

    // Delete user
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'User deleted']);
    }

}
