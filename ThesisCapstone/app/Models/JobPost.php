<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'team',
        'status',
        'preferred_start_date',
        'notes',
        'posted_by',
        'is_published',
        'published_at',
        'linkedin_job_posting_id',
        'linkedin_task_urn',
        'linkedin_status',
        'linkedin_error',
    ];

    protected $casts = [
        'preferred_start_date' => 'date',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];
}
