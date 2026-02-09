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
        Schema::create('service_material_templates', function (Blueprint $table) {
            $table->id();
            $table->string('service_type'); // plumbing / siphoning
            $table->string('material_name');
            $table->integer('default_qty');
            $table->string('unit');
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
        Schema::dropIfExists('service_material_templates');
    }
};
