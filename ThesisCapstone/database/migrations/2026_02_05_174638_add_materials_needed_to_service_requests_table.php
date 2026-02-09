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
        Schema::table('service_requests', function ($table) {
            $table->text('materials_needed')->nullable()->after('notes');
        });
    }

    public function down()
    {
        Schema::table('service_requests', function ($table) {
            $table->dropColumn('materials_needed');
        });
    }

};
