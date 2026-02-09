<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'days_present',
        'days_absent',
        'late_days',
        'transport_allowance',
        'meal_allowance',
        'salary_loan',
        'sss_loan',
        'health_insurance',
        'life_insurance',
        'created_at',
    ];
}
