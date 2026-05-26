<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('palaces', function (Blueprint $table) {
            // Cria o campo para o PDF após a descrição do jantar
            $table->string('gastronomia_menu_pdf')->nullable()->after('gastronomia_jantar_desc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('palaces', function (Blueprint $table) {
            $table->dropColumn('gastronomia_menu_pdf');
        });
    }
};