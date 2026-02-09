<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleAndApprovalToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user'); // user, admin, hr, finance, procurement, business, serviceprovider
            $table->boolean('is_approved')->default(false); // auto-approve 'user' later in registration
            $table->unsignedBigInteger('company_id')->nullable(); // for business
            $table->unsignedBigInteger('service_provider_id')->nullable(); // for serviceprovider
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_approved', 'company_id', 'service_provider_id']);
        });
    }
}


