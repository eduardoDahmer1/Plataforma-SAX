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
        Schema::table('institucional', function (Blueprint $blueprint) {
            // Adicionando os 3 novos campos para os iframes
            $blueprint->text('iframe_tour_360')->nullable()->after('stat_employees_count');
            $blueprint->text('iframe_ponte_amizade')->nullable()->after('iframe_tour_360');
            $blueprint->text('iframe_centro_cde')->nullable()->after('iframe_ponte_amizade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institucional', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['iframe_tour_360', 'iframe_ponte_amizade', 'iframe_centro_cde']);
        });
    }
};