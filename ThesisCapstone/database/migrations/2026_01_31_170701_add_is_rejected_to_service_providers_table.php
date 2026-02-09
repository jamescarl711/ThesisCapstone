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
        $table->boolean('is_rejected')->default(0)->after('is_approved');
        $table->text('reject_reason')->nullable()->after('is_rejected');
    });
}

public function down()
{
    Schema::table('service_providers', function (Blueprint $table) {
        $table->dropColumn(['is_rejected', 'reject_reason']);
    });
}

};
