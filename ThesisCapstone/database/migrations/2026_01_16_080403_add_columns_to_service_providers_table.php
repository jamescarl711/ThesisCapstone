<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_providers', function (Blueprint $table) {
            if (!Schema::hasColumn('service_providers', 'category')) {
                $table->string('category')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('service_providers', 'service_description')) {
                $table->text('service_description')->nullable()->after('category');
            }
            if (!Schema::hasColumn('service_providers', 'experience_years')) {
                $table->integer('experience_years')->default(0)->after('service_description');
            }
            if (!Schema::hasColumn('service_providers', 'valid_id')) {
                $table->string('valid_id')->nullable()->after('experience_years');
            }
        });
    }


      public function down()
    {
        Schema::table('service_providers', function (Blueprint $table) {
            if (Schema::hasColumn('service_providers', 'valid_id')) {
                $table->dropColumn('valid_id');
            }
            if (Schema::hasColumn('service_providers', 'experience_years')) {
                $table->dropColumn('experience_years');
            }
            if (Schema::hasColumn('service_providers', 'service_description')) {
                $table->dropColumn('service_description');
            }
            if (Schema::hasColumn('service_providers', 'category')) {
                $table->dropColumn('category');
            }
        });
    }

};
