<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            // Aumenta para 100 caracteres para aceitar "paraguai", "brasil", etc.
            $table->string('country', 100)->nullable()->change(); 
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Volta para o que era antes (ajuste o 25 se for outro valor)
            $table->string('country', 25)->nullable()->change();
        });
    }
};
