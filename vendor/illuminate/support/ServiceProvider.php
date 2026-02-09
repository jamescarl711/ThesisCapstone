<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'service_description',
        'experience_years',
        'valid_id',
        'latitude',
        'longitude',
        'is_available',
        'is_approved',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); // siguraduhing user_id ang FK
    }

    public function workHistory()
    {
        return $this->hasMany(WorkHistory::class, 'service_provider_id');
    }
}
