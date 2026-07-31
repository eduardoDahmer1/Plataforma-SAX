<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_monitors', function (Blueprint $table) {
            $table->id();
            $table->string('source', 50)->unique();
            $table->string('name', 100);
            $table->string('status', 20)->default('never_reported');
            $table->string('last_run_id', 80)->nullable();
            $table->timestamp('last_started_at')->nullable();
            $table->timestamp('last_finished_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable()->index();
            $table->timestamp('outage_started_at')->nullable();
            $table->unsignedInteger('consecutive_failures')->default(0);
            $table->string('error_code', 80)->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_failure_notification_at')->nullable();
            $table->timestamps();
        });

        Schema::create('integration_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_monitor_id')->constrained()->cascadeOnDelete();
            $table->string('run_id', 80);
            $table->string('status', 20)->default('running');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('error_code', 80)->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['integration_monitor_id', 'run_id']);
            $table->index(['integration_monitor_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_runs');
        Schema::dropIfExists('integration_monitors');
    }
};
