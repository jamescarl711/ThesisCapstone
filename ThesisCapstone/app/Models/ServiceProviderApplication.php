<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProviderApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'category',
        'experience_years',
        'service_description',
        'valid_id',
        'status',
        'rejection_reason'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
