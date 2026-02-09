<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\JobPost;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HrDashboardController extends Controller
{
    public function index()
    {
        $totalEmployees = Employee::count();
        $onLeave = Employee::where('status', 'On Leave')->count();
        $activeEmployees = Employee::where('status', 'Active')->count();

        $openPositions = JobPost::where('is_published', true)
            ->where('status', 'Active')
            ->count();

        $priorityRoles = min(4, $openPositions);
        $complianceRate = $totalEmployees > 0
            ? (int) round(($activeEmployees / $totalEmployees) * 100)
            : 0;

        $teamSummary = Employee::query()
            ->selectRaw('team, COUNT(*) as count')
            ->whereNotNull('team')
            ->groupBy('team')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->team,
                'count' => (int) $row->count,
                'coverage' => $totalEmployees > 0 ? (int) round(($row->count / $totalEmployees) * 100) : 0,
            ])
            ->values();

        $operationalTasks = ServiceRequest::query()
            ->with(['user', 'business'])
            ->whereIn('status', ['pending', 'accepted', 'assigned'])
            ->orderBy('preferred_date')
            ->limit(3)
            ->get()
            ->map(function ($req) {
                $priority = match ($req->status) {
                    'pending' => 'High',
                    'accepted' => 'Medium',
                    default => 'Low',
                };
                $owner = $req->business?->business_name ?: $req->user?->name ?: 'System';
                return [
                    'id' => $req->id,
                    'title' => $req->service_type ?: 'Service Request',
                    'owner' => $owner,
                    'due' => $req->preferred_date ? Carbon::parse($req->preferred_date)->format('M d') : 'TBD',
                    'priority' => $priority,
                ];
            })
            ->values();

        $recentActivities = ServiceRequest::query()
            ->with(['user', 'business'])
            ->orderByDesc('updated_at')
            ->limit(4)
            ->get()
            ->map(function ($req) {
                $name = $req->user?->name ?: 'User';
                $initials = collect(explode(' ', trim($name)))
                    ->filter()
                    ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
                    ->take(2)
                    ->implode('');

                $business = $req->business?->business_name ?: 'Business';
                $description = trim(($req->service_type ?: 'Service') . ' · ' . $business);
                return [
                    'id' => $req->id,
                    'initials' => $initials ?: 'SR',
                    'title' => 'Request ' . ucfirst($req->status ?? 'updated'),
                    'description' => $description,
                    'timestamp' => $req->updated_at ? Carbon::parse($req->updated_at)->format('M d, g:i A') : '',
                ];
            })
            ->values();

        $personnelRequests = ServiceRequest::query()
            ->with('business')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->map(function ($req) {
                $department = $req->business?->category ?: 'General';
                return [
                    'id' => $req->id,
                    'title' => $req->service_type ?: 'Service Request',
                    'department' => ucfirst($department),
                    'status' => 'Pending',
                ];
            })
            ->values();

        return response()->json([
            'stats' => [
                'totalEmployees' => $totalEmployees,
                'monthlyGrowth' => 0,
                'openPositions' => $openPositions,
                'priorityRoles' => $priorityRoles,
                'onLeave' => $onLeave,
                'leaveType' => 'On Leave',
                'complianceRate' => $complianceRate,
                'auditWindow' => 'Safety Audit',
            ],
            'teamSummary' => $teamSummary,
            'operationalTasks' => $operationalTasks,
            'recentActivities' => $recentActivities,
            'personnelRequests' => $personnelRequests,
        ]);
    }

    public function updateRequestStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:255'],
        ]);

        $serviceRequest->update([
            'status' => $data['status'],
        ]);

        return response()->json(['ok' => true]);
    }
}
