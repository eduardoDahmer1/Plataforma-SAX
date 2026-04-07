<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa a migração para adicionar o campo.
     */
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            // Criando como 'text' para permitir textos longos, 
            // e 'nullable' para não quebrar registros já existentes.
            $table->text('text_topo')->nullable()->after('details_status');
        });
    }

    /**
     * Reverte a migração (remove o campo).
     */
    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('text_topo');
        });
    }
};