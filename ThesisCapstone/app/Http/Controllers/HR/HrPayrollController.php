<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HrPayrollController extends Controller
{
    public function attendanceSummary(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer'],
            'pay_date' => ['nullable', 'date'],
        ]);

        $date = isset($data['pay_date']) ? Carbon::parse($data['pay_date']) : now();
        $from = $date->copy()->startOfMonth();
        $to = $date->copy()->endOfMonth();

        $query = Attendance::where('employee_id', $data['employee_id'])
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to);

        $daysPresent = (clone $query)->where('status', 'present')->count();
        $daysAbsent = (clone $query)->where('status', 'absent')->count();
        $lateDays = (clone $query)->where('status', 'late')->count();
        $overtimeMinutes = (clone $query)->sum('overtime_minutes');

        return response()->json([
            'days_present' => $daysPresent,
            'days_absent' => $daysAbsent,
            'late_days' => $lateDays,
            'overtime_minutes' => (int) $overtimeMinutes,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'integer'],
            'days_present' => ['required', 'integer', 'min:0'],
            'days_absent' => ['required', 'integer', 'min:0'],
            'late_days' => ['required', 'integer', 'min:0'],
            'transport_allowance' => ['nullable', 'numeric', 'min:0'],
            'meal_allowance' => ['nullable', 'numeric', 'min:0'],
            'salary_loan' => ['nullable', 'numeric', 'min:0'],
            'sss_loan' => ['nullable', 'numeric', 'min:0'],
            'health_insurance' => ['nullable', 'numeric', 'min:0'],
            'life_insurance' => ['nullable', 'numeric', 'min:0'],
            'pay_date' => ['nullable', 'date'],
        ]);

        $payroll = Payroll::create([
            'employee_id' => $data['employee_id'],
            'days_present' => $data['days_present'],
            'days_absent' => $data['days_absent'],
            'late_days' => $data['late_days'],
            'transport_allowance' => $data['transport_allowance'] ?? 0,
            'meal_allowance' => $data['meal_allowance'] ?? 0,
            'salary_loan' => $data['salary_loan'] ?? 0,
            'sss_loan' => $data['sss_loan'] ?? 0,
            'health_insurance' => $data['health_insurance'] ?? 0,
            'life_insurance' => $data['life_insurance'] ?? 0,
            'created_at' => $data['pay_date'] ?? now(),
        ]);

        return response()->json(['id' => $payroll->id]);
    }
}
