<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('status');
            $table->enum('status', [
                'pending','approved','assigned','accepted','rejected','awaiting_material','job_ready','in_progress','completed'
            ])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
            $table->enum('status', [
                'pending','approved','assigned','accepted','rejected','awaiting_material','job_ready'
            ])->default('pending')->change();
        });
    }
};
