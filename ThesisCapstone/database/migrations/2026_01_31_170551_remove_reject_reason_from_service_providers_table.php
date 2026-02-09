<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            if (Schema::hasColumn('service_providers', 'reject_reason')) {
                $table->dropColumn('reject_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->text('reject_reason')->nullable()->after('is_rejected');
        });
    }
};
