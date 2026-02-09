<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();

            // USER who requested
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // SERVICE PROVIDER
            $table->foreignId('service_provider_id')
                  ->constrained('service_providers')
                  ->cascadeOnDelete();

            $table->string('service_type')->nullable();

            // LOCATION
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            $table->string('address_text')->nullable();

            $table->date('preferred_date')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', ['pending', 'accepted', 'rejected', 'completed'])
                  ->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
