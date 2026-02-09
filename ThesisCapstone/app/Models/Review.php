<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_provider_id',
        'rating',
        'review',
        'anonymous'
    ];

    // The user who made the review
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // The service provider being reviewed
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
