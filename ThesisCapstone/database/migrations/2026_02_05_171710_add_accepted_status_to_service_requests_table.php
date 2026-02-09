<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If your status column is ENUM, we need to modify it
        Schema::table('service_requests', function (Blueprint $table) {
            // Change ENUM to include 'accepted'
            DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pending', 'approved', 'assigned', 'accepted', 'rejected') NOT NULL DEFAULT 'pending'");
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            // Remove 'accepted' from ENUM if rolling back
            DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pending', 'approved', 'assigned', 'rejected') NOT NULL DEFAULT 'pending'");
        });
    }
};

