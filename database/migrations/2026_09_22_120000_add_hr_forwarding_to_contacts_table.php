<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->timestamp('hr_sent_at')->nullable();
            $table->string('hr_sent_to')->nullable();
            $table->timestamp('hr_attempted_at')->nullable();
            $table->string('hr_last_error', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['hr_sent_at', 'hr_sent_to', 'hr_attempted_at', 'hr_last_error']);
        });
    }
};
