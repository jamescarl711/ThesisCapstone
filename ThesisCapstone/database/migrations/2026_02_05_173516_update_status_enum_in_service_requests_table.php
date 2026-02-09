<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Para sa MySQL, kailangan palitan ang enum values
        DB::statement("
            ALTER TABLE service_requests MODIFY status ENUM(
                'pending',
                'approved',
                'assigned',
                'accepted',
                'rejected',
                'awaiting_material',
                'job_ready'
            ) DEFAULT 'pending';
        ");
    }

    public function down(): void
    {
        // I-revert sa original enum
        DB::statement("
            ALTER TABLE service_requests MODIFY status ENUM(
                'pending',
                'approved',
                'assigned',
                'accepted',
                'rejected'
            ) DEFAULT 'pending';
        ");
    }
};

