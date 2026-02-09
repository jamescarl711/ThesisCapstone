<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        return response()->json(
            User::with(['business', 'serviceProvider'])->latest()->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            User::with(['business', 'serviceProvider'])->findOrFail($id)
        );
    }

    public function toggleApproval($id)
    {
        $user = User::findOrFail($id);
        $user->is_approved = ! $user->is_approved;
        $user->save();

        return response()->json([
            'status' => 'success',
            'approved' => $user->is_approved
        ]);
    }

    // ❌ REJECT
    public function reject($id)
    {
        $user = User::findOrFail($id);
        $user->is_approved = false;
        $user->save();

        return response()->json([
            'status' => 'rejected'
        ]);
    }

    // 👀 MARK AS VIEWED (optional)
    public function markViewed($id)
    {
        $user = User::findOrFail($id);
        $user->is_viewed = true;
        $user->save();

        return response()->json([
            'status' => 'viewed'
        ]);
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'deleted']);
    }
}
