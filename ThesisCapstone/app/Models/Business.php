<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'user_id','owner_name','business_name','address','contact_number','category','business_type',
        'bir_registration','dti_registration','mayor_permit','business_permit'
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
