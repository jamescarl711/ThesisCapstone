<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
    'first_name','middle_initial','last_name','email','password','role','latitude','longitude','is_approved','contact_number'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_approved' => 'boolean'
    ];

    /* =======================
       ACCESSORS
       ======================= */

    // Para gumana pa rin lahat ng old code na gumagamit ng $user->name
    public function getNameAttribute()
    {
        return trim(
            $this->first_name . ' ' .
            ($this->middle_initial ? $this->middle_initial . '. ' : '') .
            $this->last_name
        );
    }

    /* =======================
       ROLE HELPERS
       ======================= */

    public function isAdmin() { return $this->role === 'admin'; }
    public function isBusiness() { return $this->role === 'business'; }
    public function isServiceProvider() { return $this->role === 'serviceprovider'; }

    /* =======================
       RELATIONSHIPS
       ======================= */

    public function business()
    {
        return $this->hasOne(Business::class);
    }

    public function serviceProvider()
    {
        return $this->hasOne(ServiceProvider::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
