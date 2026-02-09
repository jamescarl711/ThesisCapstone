<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('role')->nullable()->after('name');
            $table->string('team')->nullable()->after('role');
            $table->string('status')->default('Active')->after('team');
            $table->date('start_date')->nullable()->after('status');
            $table->text('notes')->nullable()->after('start_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['role', 'team', 'status', 'start_date', 'notes', 'created_at', 'updated_at']);
        });
    }
};
