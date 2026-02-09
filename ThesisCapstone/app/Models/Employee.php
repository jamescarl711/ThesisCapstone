<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'email',
        'user_id',
        'role',
        'team',
        'status',
        'start_date',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
