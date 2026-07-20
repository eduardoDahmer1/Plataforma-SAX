<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->date('event_date')->index();
            $table->string('event_type', 20)->index();
            $table->string('visitor_hash', 64)->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('path', 500);
            $table->string('page_title', 255)->nullable();
            $table->string('target', 500)->nullable();
            $table->string('element_text', 160)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('referrer_host', 255)->nullable();
            $table->timestamps();

            $table->index(['event_type', 'event_date']);
            $table->index(['event_type', 'path']);
            $table->index(['event_date', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_analytics_events');
    }
};
