<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class HrAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('employee')
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        return response()->json([
            'data' => $query->paginate(20),
        ]);
    }

    public function employees()
    {
        return response()->json(
            Employee::orderBy('name')->get(['id', 'name', 'email', 'role', 'team', 'status'])
        );
    }

    public function qr(Request $request)
    {
        $token = Cache::get('attendance_qr_token');
        $expiresAt = Cache::get('attendance_qr_expires_at');

        $expired = !$expiresAt || now()->greaterThan($expiresAt);
        if ($request->boolean('force') || !$token || $expired) {
            $expiresAt = now()->addHour()->startOfMinute();
            $token = Str::random(32);
            Cache::put('attendance_qr_token', $token, $expiresAt);
            Cache::put('attendance_qr_expires_at', $expiresAt, $expiresAt);
        }

        return response()->json([
            'token' => $token,
            'expires_at' => $expiresAt ? $expiresAt->toDateTimeString() : null,
        ]);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Attendance is read-only.'], 403);
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'status' => ['required', Rule::in(['present', 'absent', 'late', 'leave'])],
            'time_in' => ['nullable', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i'],
            'work_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $attendance = Attendance::updateOrCreate(
            [
                'employee_id' => $validated['employee_id'],
                'date' => $validated['date'],
            ],
            [
                'status' => $validated['status'],
                'time_in' => $validated['time_in'] ?? null,
                'time_out' => $validated['time_out'] ?? null,
                'work_hours' => $validated['work_hours'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => Auth::id(),
            ]
        );

        return response()->json(['data' => $attendance->load('employee')], 201);
    }

    public function update(Request $request, Attendance $attendance)
    {
        if (empty($attendance->time_out)) {
            return response()->json(['message' => 'Overtime can only be set after time-out.'], 422);
        }

        $validated = $request->validate([
            'overtime_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
        ]);

        if (!is_null($validated['overtime_minutes'])) {
            $minutes = (int) $validated['overtime_minutes'];
            if ($minutes > 0 && $minutes < 10) {
                return response()->json(['message' => 'Overtime must be at least 10 minutes.'], 422);
            }
            if ($minutes % 10 !== 0) {
                return response()->json(['message' => 'Overtime minutes must be in 10-minute increments.'], 422);
            }
        }

        $attendance->update([
            'overtime_minutes' => $validated['overtime_minutes'] ?? null,
            'recorded_by' => Auth::id(),
        ]);

        return response()->json(['data' => $attendance->load('employee')]);
    }

    public function destroy(Attendance $attendance)
    {
        return response()->json(['message' => 'Attendance is read-only.'], 403);
        $attendance->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
