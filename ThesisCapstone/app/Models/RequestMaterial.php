<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'material_name',
        'quantity',
        'unit'
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }
}
