<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            // 1️⃣ Add business_id as nullable first (safe for existing rows)
            $table->unsignedBigInteger('business_id')->nullable()->after('user_id');
        });

        // 2️⃣ Populate existing rows with a valid business_id
        // You can run raw SQL here, adjust logic as needed
        DB::table('service_requests')
            ->whereNull('business_id')
            ->update([
                // Example: if service_provider_id exists, use their business_id
                'business_id' => DB::raw('(SELECT business_id FROM service_providers WHERE id = service_requests.service_provider_id LIMIT 1)')
            ]);

        Schema::table('service_requests', function (Blueprint $table) {
            // 3️⃣ Make column NOT NULL and add foreign key constraint
            $table->unsignedBigInteger('business_id')->nullable(false)->change();

            $table->foreign('business_id')
                  ->references('id')
                  ->on('businesses')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn('business_id');
        });
    }
};
