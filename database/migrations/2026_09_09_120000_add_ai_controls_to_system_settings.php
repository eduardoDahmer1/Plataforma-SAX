<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->boolean('ai_enabled')->default(true);
            $table->string('ai_key_source')->default('environment');
            $table->text('ai_api_key')->nullable();
        });

        // Paused jobs can be released repeatedly without exhausting an 8-bit counter.
        Schema::table('product_ai_jobs', function (Blueprint $table) {
            $table->unsignedInteger('attempts')->change();
        });
    }

    public function down(): void
    {
        // Keep the wider attempts counter: narrowing it could truncate paused jobs.
        Schema::table('system_settings', fn (Blueprint $table) => $table->dropColumn([
            'ai_enabled', 'ai_key_source', 'ai_api_key',
        ]));
    }
};
