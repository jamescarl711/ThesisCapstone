<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
    'user_id',
    'business_id',
    'service_provider_id',
    'service_type',
    'address_text',
    'notes',
    'preferred_date',  // ✅ must be here
    'status',
    'latitude',
    'longitude'
];

protected $casts = [
    'preferred_date' => 'date:Y-m-d', // ✅ ensures proper casting
];


    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }
    public function materials()
    {
        return $this->hasMany(RequestMaterial::class);
    }
    public function proofs() {
        return $this->hasMany(ServiceRequestProof::class);
    }


}
