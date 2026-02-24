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
        Schema::table('attributes', function (Blueprint $blueprint) {
            // Adicionando os 3 campos para os ícones (SVG ou PNG)
            // Usamos nullable para não quebrar registros existentes
            $blueprint->string('icon_info')->nullable()->after('logo_palace');
            $blueprint->string('icon_cabide')->nullable()->after('icon_info');
            $blueprint->string('icon_help')->nullable()->after('icon_cabide');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['icon_info', 'icon_cabide', 'icon_help']);
        });
    }
};