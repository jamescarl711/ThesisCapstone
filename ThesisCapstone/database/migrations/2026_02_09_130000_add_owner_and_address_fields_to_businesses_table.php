<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('owner_first_name')->nullable()->after('owner_name');
            $table->string('owner_middle_initial')->nullable()->after('owner_first_name');
            $table->string('owner_last_name')->nullable()->after('owner_middle_initial');

            $table->string('address_unit')->nullable()->after('address');
            $table->string('address_street')->nullable()->after('address_unit');
            $table->string('address_barangay')->nullable()->after('address_street');
            $table->string('address_city')->nullable()->after('address_barangay');
            $table->string('address_province')->nullable()->after('address_city');
            $table->string('address_postal')->nullable()->after('address_province');

            $table->string('business_ownership')->nullable()->after('business_type');
            $table->string('years_in_operation')->nullable()->after('business_ownership');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'owner_first_name',
                'owner_middle_initial',
                'owner_last_name',
                'address_unit',
                'address_street',
                'address_barangay',
                'address_city',
                'address_province',
                'address_postal',
                'business_ownership',
                'years_in_operation',
            ]);
        });
    }
};
