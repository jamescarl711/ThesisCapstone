<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'user_id','owner_name','owner_first_name','owner_middle_initial','owner_last_name',
        'business_name','address','address_unit','address_street','address_barangay','address_city',
        'address_province','address_postal','contact_number','category','business_type',
        'business_ownership','years_in_operation','bir_registration','dti_registration','mayor_permit','business_permit'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(ServiceProviderApplication::class);
    }

    public function serviceProviders()
    {
        return $this->hasMany(ServiceProvider::class);
    }
    public function providers() {
        return $this->hasMany(ServiceProvider::class);
    }

}
