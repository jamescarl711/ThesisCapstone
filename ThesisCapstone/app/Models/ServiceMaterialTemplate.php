<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMaterialTemplate extends Model
{
    protected $fillable = [
        'service_type',
        'material_name',
        'default_qty',
        'unit'
    ];
}
