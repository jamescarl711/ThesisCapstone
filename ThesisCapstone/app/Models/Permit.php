<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permit extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name', 
        'owner_name', 
        'permit_number', 
        'business_type', 
        'status', 
        'file'
    ];
}
