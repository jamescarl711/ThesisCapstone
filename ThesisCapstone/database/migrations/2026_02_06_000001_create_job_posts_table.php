<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('team');
            $table->string('status')->default('Active');
            $table->date('preferred_start_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('linkedin_job_posting_id')->nullable();
            $table->string('linkedin_task_urn')->nullable();
            $table->string('linkedin_status')->nullable();
            $table->text('linkedin_error')->nullable();
            $table->timestamps();

            $table->foreign('posted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
