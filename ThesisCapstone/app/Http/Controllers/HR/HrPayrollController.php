<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Http\Request;

class HrPayrollController extends Controller
{
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
