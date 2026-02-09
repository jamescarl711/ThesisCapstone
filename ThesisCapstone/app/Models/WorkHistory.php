<?php

// app/Models/WorkHistory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkHistory extends Model
{
    protected $fillable = [
        'service_provider_id',
        'title',
        'location',
        'year',
        'description',
    ];

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
