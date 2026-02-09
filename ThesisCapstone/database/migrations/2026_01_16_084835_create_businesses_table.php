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
       Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->string('owner_name');
            $table->string('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('category')->nullable();
            $table->string('bir_registration')->nullable();
            $table->string('dti_registration')->nullable();
            $table->string('mayor_permit')->nullable();
            $table->string('business_permit')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('businesses');
    }
};
