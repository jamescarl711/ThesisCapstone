<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use App\Models\ServiceProvider;

class HRServiceRequestController extends Controller
{
    /**
     * GET: /hr/service-requests
     * Show all service requests approved by business and not yet assigned
     */
    public function index()
    {
        try {
            $requests = ServiceRequest::with([
                    'user:id,first_name,middle_initial,last_name',
                    'business:id,business_name',
                    'employee:id,name'
                ])
                ->whereIn('status', ['approved', 'assigned'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($requests);
        } catch (\Exception $e) {
            \Log::error('HRServiceRequestController@index error: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch service requests',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET: /hr/available-providers
     * List all service providers (for dropdown assignment)
     */
    public function availableProviders()
    {
        try {
            $providers = ServiceProvider::with('user:id,first_name,last_name')
                ->where(function ($q) {
                    $q->where('status', 'approved')   // enum status
                      ->orWhere('is_approved', 1);    // legacy flag
                })
                ->where('is_available', 1)
                ->get();

            return response()->json($providers);
        } catch (\Exception $e) {
            \Log::error('HRServiceRequestController@availableProviders error: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch providers',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST: /hr/service-requests/{id}/assign
     * Assign a provider to a service request
     */
    public function assignProvider(Request $request, $id)
{
    try {
        $request->validate([
            'service_provider_id' => 'required|exists:service_providers,id'
        ]);

        \Log::info('AssignProvider called', [
            'request_id' => $id,
            'provider_id' => $request->service_provider_id
        ]);

        $provider = ServiceProvider::where('id', $request->service_provider_id)
            ->where(function ($q) {
                $q->where('status', 'approved')
                  ->orWhere('is_approved', 1);
            })
            ->where('is_available', 1)
            ->first();

        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not found or not available'
            ], 404);
        }

        $sr = ServiceRequest::find($id);
        if (!$sr) {
            return response()->json([
                'success' => false,
                'message' => 'Service request not found'
            ], 404);
        }

        $sr->service_provider_id = $provider->id;
        $sr->status = 'assigned';
        $sr->save();

        $provider->is_available = 0;
        $provider->save();

        return response()->json([
            'success' => true,
            'message' => 'Service provider assigned successfully',
            'data'    => $sr->only(['id','status','service_provider_id'])
        ]);
    } catch (\Exception $e) {
        \Log::error('AssignProvider error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to assign provider',
            'error'   => $e->getMessage()
        ], 500);
    }
}

    /**
     * POST: /hr/service-requests/{id}/assign-employee
     * Assign an employee to a service request
     */
    public function assignEmployee(Request $request, $id)
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:employees,id'
            ]);

            $sr = ServiceRequest::find($id);
            if (!$sr) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service request not found'
                ], 404);
            }

            if (!in_array($sr->status, ['approved', 'assigned'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service request is not approved'
                ], 400);
            }

            $sr->employee_id = $request->employee_id;
            $sr->status = 'assigned';
            $sr->save();

            return response()->json([
                'success' => true,
                'message' => 'Employee assigned successfully',
                'data'    => $sr->only(['id', 'status', 'employee_id'])
            ]);
        } catch (\Exception $e) {
            \Log::error('AssignEmployee error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign employee',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
