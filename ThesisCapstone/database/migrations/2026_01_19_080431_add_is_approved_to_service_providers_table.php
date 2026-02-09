<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            if (!Schema::hasColumn('service_providers', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            if (Schema::hasColumn('service_providers', 'is_approved')) {
                $table->dropColumn('is_approved');
            }
        });
    }
};
