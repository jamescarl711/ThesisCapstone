<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $employee = Employee::query()->where('user_id', $user->id)->first();

        $payrolls = [];
        if ($employee) {
            $payrolls = Payroll::query()
                ->where('employee_id', $employee->id)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (Payroll $payroll) => [
                    'id' => $payroll->id,
                    'days_present' => $payroll->days_present,
                    'days_absent' => $payroll->days_absent,
                    'late_days' => $payroll->late_days,
                    'transport_allowance' => $payroll->transport_allowance,
                    'meal_allowance' => $payroll->meal_allowance,
                    'salary_loan' => $payroll->salary_loan,
                    'sss_loan' => $payroll->sss_loan,
                    'health_insurance' => $payroll->health_insurance,
                    'life_insurance' => $payroll->life_insurance,
                    'created_at' => $payroll->created_at
                        ? Carbon::parse($payroll->created_at)->format('Y-m-d')
                        : null,
                ]);
        }

        $notifications = $user->notifications()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'data' => $notification->data,
                'read_at' => $notification->read_at,
                'created_at' => optional($notification->created_at)->format('Y-m-d H:i'),
            ]);

        $assignedRequests = [];
        if ($employee) {
            $assignedRequests = ServiceRequest::query()
                ->where('employee_id', $employee->id)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($req) => [
                    'id' => $req->id,
                    'service_type' => $req->service_type,
                    'status' => $req->status,
                    'preferred_date' => optional($req->preferred_date)->format('Y-m-d'),
                    'address_text' => $req->address_text,
                ]);
        }

        return response()->json([
            'profile' => [
                'first_name' => $user->first_name,
                'middle_initial' => $user->middle_initial,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'contact_number' => $user->contact_number,
            ],
            'employee' => $employee ? [
                'id' => $employee->id,
                'role' => $employee->role,
                'team' => $employee->team,
                'status' => $employee->status,
                'start_date' => optional($employee->start_date)->format('Y-m-d'),
                'notes' => $employee->notes,
            ] : null,
            'notifications' => $notifications,
            'payrolls' => $payrolls,
            'assigned_requests' => $assignedRequests,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string', 'max:1'],
            'last_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:20'],
        ]);

        $user = $request->user();
        $user->update($data);

        return response()->json([
            'first_name' => $user->first_name,
            'middle_initial' => $user->middle_initial,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'contact_number' => $user->contact_number,
        ]);
    }

    public function updateAssignedRequest(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate([
            'status' => ['required', 'in:accepted,rejected'],
        ]);

        $user = $request->user();
        $employee = Employee::query()->where('user_id', $user->id)->first();

        if (!$employee || $serviceRequest->employee_id !== $employee->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($serviceRequest->status !== 'assigned') {
            return response()->json(['message' => 'Request not available'], 400);
        }

        $serviceRequest->status = $data['status'];
        $serviceRequest->save();

        return response()->json([
            'id' => $serviceRequest->id,
            'status' => $serviceRequest->status,
        ]);
    }
}
