<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'status',
        'time_in',
        'time_out',
        'work_hours',
        'overtime_minutes',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime:H:i',
        'time_out' => 'datetime:H:i',
        'work_hours' => 'decimal:2',
        'overtime_minutes' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
