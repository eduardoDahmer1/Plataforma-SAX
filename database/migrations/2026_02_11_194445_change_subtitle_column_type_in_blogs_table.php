<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            // Mudando de VARCHAR(255) para TEXT para suportar parágrafos longos
            $table->text('subtitle')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            // Caso precise reverter, ele volta para string (VARCHAR 255)
            $table->string('subtitle', 255)->nullable()->change();
        });
    }
};