<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EmployeeAttendanceController extends Controller
{
    private function getEmployee()
    {
        return Employee::where('user_id', auth()->id())->first();
    }

    public function status()
    {
        $employee = $this->getEmployee();
        if (!$employee) {
            return response()->json(['message' => 'Employee record not found.'], 404);
        }

        $today = Carbon::today();
        $todayRecord = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        return response()->json([
            'employee' => $employee,
            'today' => $todayRecord,
        ]);
    }

    public function records(Request $request)
    {
        $employee = $this->getEmployee();
        if (!$employee) {
            return response()->json(['message' => 'Employee record not found.'], 404);
        }

        $query = Attendance::where('employee_id', $employee->id)
            ->orderByDesc('date')
            ->orderByDesc('id');

        return response()->json([
            'data' => $query->paginate(15),
        ]);
    }

    public function qr(Request $request)
    {
        [$token, $expiresAt] = $this->getOrCreateQrToken($request->boolean('force'));

        return response()->json([
            'token' => $token,
            'expires_at' => $expiresAt ? $expiresAt->toDateTimeString() : null,
            'scan_url' => url('/employee/attendance/scan?token=' . $token),
        ]);
    }

    public function scan(Request $request)
    {
        $employee = $this->getEmployee();
        if (!$employee) {
            return $this->scanView('Error', 'Employee record not found.');
        }

        $token = (string) $request->query('token', $request->query('qr_token', ''));
        $request->merge(['qr_token' => $token]);
        $qrError = $this->assertValidQrToken($request);
        if ($qrError) {
            return $this->scanView('Error', $qrError->getData()->message ?? 'Invalid QR token.');
        }

        $now = Carbon::now();

        $openAttendance = Attendance::where('employee_id', $employee->id)
            ->whereNull('time_out')
            ->whereDate('date', '>=', Carbon::today()->subDay())
            ->orderByDesc('date')
            ->first();

        if (!$openAttendance || !$openAttendance->time_in) {
            [$shift, $attendanceDate] = $this->resolveShiftAndDate($now);
            $startAt = $this->shiftStart($shift, $attendanceDate);
            $graceMinutes = 15;
            $isLate = $now->greaterThan($startAt->copy()->addMinutes($graceMinutes));

            $attendance = Attendance::firstOrNew([
                'employee_id' => $employee->id,
                'date' => $attendanceDate->toDateString(),
            ]);

            if ($attendance->time_in) {
                return $this->scanView('Info', 'Already checked in.');
            }

            $attendance->status = $isLate ? 'late' : 'present';
            $attendance->time_in = $now->format('H:i');
            $attendance->recorded_by = auth()->id();
            $attendance->save();

            $this->regenerateQrToken();
            return $this->scanView('Success', 'Check-in recorded.');
        }

        $attendance = $openAttendance;
        if ($attendance->time_out) {
            return $this->scanView('Info', 'Attendance already completed for today.');
        }

        $attendance->time_out = $now->format('H:i');
        $timeInRaw = (string) $attendance->time_in;
        if (preg_match('/\d{4}-\d{2}-\d{2}/', $timeInRaw)) {
            $timeInDate = Carbon::parse($timeInRaw);
        } else {
            $timeInDate = Carbon::parse($attendance->date->toDateString() . ' ' . $timeInRaw);
        }
        $effectiveNow = $now->copy();
        if ($effectiveNow->lessThan($timeInDate)) {
            $effectiveNow->addDay();
        }
        $minutes = $timeInDate->diffInMinutes($effectiveNow);
        $attendance->work_hours = round($minutes / 60, 2);
        $attendance->recorded_by = auth()->id();
        $attendance->save();

        $this->regenerateQrToken();
        return $this->scanView('Success', 'Check-out recorded.');
    }

    public function checkIn()
    {
        $employee = $this->getEmployee();
        if (!$employee) {
            return response()->json(['message' => 'Employee record not found.'], 404);
        }
        $qrError = $this->assertValidQrToken(request());
        if ($qrError) {
            return $qrError;
        }

        $now = Carbon::now();
        [$shift, $attendanceDate] = $this->resolveShiftAndDate($now);
        $startAt = $this->shiftStart($shift, $attendanceDate);
        $graceMinutes = 15;
        $isLate = $now->greaterThan($startAt->copy()->addMinutes($graceMinutes));

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $attendanceDate->toDateString(),
        ]);

        if ($attendance->time_in) {
            return response()->json(['message' => 'Already checked in.'], 422);
        }

        $attendance->status = $isLate ? 'late' : 'present';
        $attendance->time_in = $now->format('H:i');
        $attendance->recorded_by = auth()->id();
        $attendance->save();

        return response()->json(['data' => $attendance], 201);
    }

    public function checkOut()
    {
        $employee = $this->getEmployee();
        if (!$employee) {
            return response()->json(['message' => 'Employee record not found.'], 404);
        }
        $qrError = $this->assertValidQrToken(request());
        if ($qrError) {
            return $qrError;
        }

        $now = Carbon::now();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereNull('time_out')
            ->whereDate('date', '>=', Carbon::today()->subDay())
            ->orderByDesc('date')
            ->first();

        if (!$attendance || !$attendance->time_in) {
            return response()->json(['message' => 'No check-in record for today.'], 422);
        }

        if ($attendance->time_out) {
            return response()->json(['message' => 'Already checked out.'], 422);
        }

        $attendance->time_out = $now->format('H:i');
        $timeInRaw = (string) $attendance->time_in;
        if (preg_match('/\d{4}-\d{2}-\d{2}/', $timeInRaw)) {
            // already a full datetime string
            $timeInDate = Carbon::parse($timeInRaw);
        } else {
            $timeInDate = Carbon::parse($attendance->date->toDateString() . ' ' . $timeInRaw);
        }
        $effectiveNow = $now->copy();
        if ($effectiveNow->lessThan($timeInDate)) {
            $effectiveNow->addDay();
        }
        $minutes = $timeInDate->diffInMinutes($effectiveNow);
        $attendance->work_hours = round($minutes / 60, 2);
        $attendance->recorded_by = auth()->id();
        $attendance->save();

        return response()->json(['data' => $attendance]);
    }

    private function assertValidQrToken(Request $request)
    {
        $request->validate([
            'qr_token' => ['required', 'string']
        ]);

        $token = Cache::get('attendance_qr_token');
        $expiresAt = Cache::get('attendance_qr_expires_at');
        $qrToken = trim((string) $request->qr_token);

        if ($qrToken === '') {
            return response()->json(['message' => 'QR token is required.'], 422);
        }

        if (!$token || !$expiresAt) {
            return response()->json(['message' => 'QR token is not available.'], 422);
        }

        if (now()->greaterThan($expiresAt)) {
            return response()->json(['message' => 'QR token has expired.'], 422);
        }

        if (!hash_equals($token, $qrToken)) {
            return response()->json(['message' => 'Invalid QR token.'], 422);
        }

        return null;
    }

    private function resolveShiftAndDate(Carbon $now): array
    {
        $time = $now->format('H:i');
        // Night shift: 20:00 - 05:59 (crosses midnight)
        if ($time >= '20:00' || $time <= '05:59') {
            $date = $time <= '05:59' ? $now->copy()->subDay() : $now->copy();
            return ['night', $date->startOfDay()];
        }

        return ['morning', $now->copy()->startOfDay()];
    }

    private function shiftStart(string $shift, Carbon $date): Carbon
    {
        if ($shift === 'night') {
            return Carbon::parse($date->toDateString() . ' 20:00');
        }
        return Carbon::parse($date->toDateString() . ' 08:00');
    }

    private function scanView(string $title, string $message)
    {
        return response()->view('attendance.scan', [
            'title' => $title,
            'message' => $message,
        ]);
    }

    private function getOrCreateQrToken(bool $force = false): array
    {
        $token = Cache::get('attendance_qr_token');
        $expiresAt = Cache::get('attendance_qr_expires_at');

        $expired = !$expiresAt || now()->greaterThan($expiresAt);
        if ($force || !$token || $expired) {
            $expiresAt = now()->addHour()->startOfMinute();
            $token = Str::random(32);
            Cache::put('attendance_qr_token', $token, $expiresAt);
            Cache::put('attendance_qr_expires_at', $expiresAt, $expiresAt);
        }

        if (now()->greaterThan($expiresAt)) {
            abort(422, 'QR token has expired.');
        }

        return [$token, $expiresAt];
    }

    private function regenerateQrToken(): void
    {
        $this->getOrCreateQrToken(true);
    }
}
