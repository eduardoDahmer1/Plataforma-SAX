<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Promos passa a ser traduzível: cada idioma guarda seus proprios
     * title/subtitle/button. Antes ficava so na tabela `bridals`, igual
     * para os tres idiomas.
     */
    public function up(): void
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->text('bridal_promos')->nullable()->after('bridal_services');
        });
    }

    public function down(): void
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->dropColumn('bridal_promos');
        });
    }
};
