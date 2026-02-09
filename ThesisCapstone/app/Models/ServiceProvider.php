<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    protected $fillable = [
        'user_id',
        'business_id',
        'category',
        'experience_years',
        'service_description',
        'valid_id',
        'latitude',
        'longitude',
        'is_approved',
        'is_rejected',
        'is_available',
        'reject_reason',
    ];

    // Relations

    // Link to user account
    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Link to the business
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    // Optional work history
    public function workHistory()
    {
        return $this->hasMany(WorkHistory::class);
    }

    // Service requests assigned to this service provider
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'service_provider_id');
    }

    // Proof photos uploaded for jobs
    public function proofs()
    {
        return $this->hasManyThrough(
            ServiceRequestProof::class,
            ServiceRequest::class,
            'service_provider_id', // Foreign key on ServiceRequest
            'service_request_id',  // Foreign key on ServiceRequestProof
            'id',                  // Local key on ServiceProvider
            'id'                   // Local key on ServiceRequest
        );
    }
}
