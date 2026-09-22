<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_forward_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id')->nullable()->index();
            $table->string('candidate_name');
            $table->string('candidate_email');
            $table->string('store_name')->nullable();
            $table->string('destination')->nullable();
            $table->string('source', 20);
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->unsignedBigInteger('canceled_by')->nullable();
            $table->string('status', 20)->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('error', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_forward_attempts');
    }
};
