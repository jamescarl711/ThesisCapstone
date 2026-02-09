<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class BusinessController extends Controller
{
    /* ===============================
     | REGISTER BUSINESS
     =============================== */
    public function registerBusiness(Request $request)
    {
        $request->validate([
            'business_owner' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'business_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'category' => 'required|string',
            'business_type' => 'required|in:Individual,Small Business,Company',
            'password' => 'required|string|confirmed|min:6',
            'bir_registration' => 'nullable|file|mimes:jpg,png,pdf',
            'dti_registration' => 'nullable|file|mimes:jpg,png,pdf',
            'mayor_permit' => 'nullable|file|mimes:jpg,png,pdf',
            'business_permit' => 'nullable|file|mimes:jpg,png,pdf',
        ]);

        $user = User::create([
            'first_name' => $request->business_owner,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'business',
            'is_approved' => 0,
        ]);

        $business = Business::create([
            'user_id' => $user->id,
            'owner_name' => $request->business_owner,
            'business_name' => $request->business_name,
            'address' => $request->address,
            'contact_number' => $request->contact_number,
            'category' => $request->category,
            'business_type' => $request->business_type,
            'bir_registration' => $request->file('bir_registration')?->store('business_docs', 'public'),
            'dti_registration' => $request->file('dti_registration')?->store('business_docs', 'public'),
            'mayor_permit' => $request->file('mayor_permit')?->store('business_docs', 'public'),
            'business_permit' => $request->file('business_permit')?->store('business_docs', 'public'),
        ]);

        return response()->json(['success' => true, 'business' => $business]);
    }

    /* ===============================
     | PENDING PROVIDER APPLICATIONS
     | is_approved = 0
     =============================== */
public function pendingApplications()
    {
        $business = Business::where('user_id', Auth::id())->firstOrFail();

        return ServiceProvider::where('business_id', $business->id)
            ->where('is_approved', 0)
            ->where('is_rejected', 0)
            ->with('user')
            ->get();
    }

/* APPROVE / REJECT */
public function reviewApplication(Request $request, $id)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string|max:500',
        ]);

        $sp = ServiceProvider::findOrFail($id);

        if ($validated['action'] === 'approve') {
            $sp->update([
                'is_approved' => 1,
                'is_rejected' => 0,
                'reject_reason' => null,
            ]);

            $sp->user->update(['role' => 'serviceprovider']);
        } else {
            if (empty($validated['reason'])) {
                return response()->json(['message' => 'Rejection reason is required'], 422);
            }

            $sp->update([
                'is_approved' => 0,
                'is_rejected' => 1,
                'reject_reason' => $validated['reason'],
            ]);
        }

        return response()->json(['success' => true]);
    }


    /* ===============================
     | ACTIVE PROVIDERS
     | is_approved = 1
     =============================== */
public function activeProviders()
    {
        $business = Business::where('user_id', Auth::id())->firstOrFail();

        return ServiceProvider::where('business_id', $business->id)
            ->where('is_approved', 1)
            ->with('user')
            ->get();
    }


    /* ===============================
     | BUSINESS SERVICE REQUESTS
     =============================== */
    public function serviceRequests()
    {
        $business = Business::where('user_id', Auth::id())->firstOrFail();

        return ServiceRequest::where('business_id', $business->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();
    }

    public function reviewServiceRequest(Request $request, $id)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string|max:500',
        ]);

        $requestItem = ServiceRequest::findOrFail($id);

        if ($validated['action'] === 'approve') {
            $requestItem->update([
                'status' => 'approved',
                'reject_reason' => null,
            ]);
        } else {
            if (empty($validated['reason'])) {
                return response()->json(['message' => 'Rejection reason is required'], 422);
            }

            $requestItem->update([
                'status' => 'rejected',
                'reject_reason' => $validated['reason'],
            ]);
        }

        return response()->json(['success' => true]);
    }



    /* ===============================
     | LIST APPROVED BUSINESSES (USER)
     =============================== */
    public function index()
    {
        return Business::with('user')
            ->whereHas('user', fn ($q) => $q->where('is_approved', 1))
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'business_name' => $b->business_name,
                'owner_name' => $b->owner_name,
                'address' => $b->address,
                'contact_number' => $b->contact_number,
                'category' => $b->category,
                'business_type' => $b->business_type,
            ]);
    }
}
