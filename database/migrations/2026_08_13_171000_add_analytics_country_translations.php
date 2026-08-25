<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('languages')) {
            return;
        }

        $now = now();
        $translations = [
            ['key' => 'report_visitors_by_country', 'pt' => 'Visitantes por país', 'en' => 'Visitors by country', 'es' => 'Visitantes por país'],
            ['key' => 'report_country', 'pt' => 'País', 'en' => 'Country', 'es' => 'País'],
            ['key' => 'report_identified_visitors', 'pt' => 'Visitantes identificados', 'en' => 'Identified visitors', 'es' => 'Visitantes identificados'],
            ['key' => 'report_country_unknown', 'pt' => 'Não identificado', 'en' => 'Unknown', 'es' => 'No identificado'],
            ['key' => 'report_country_tracking_note', 'pt' => 'A localização é estimada pelo cadastro ou pelo IP e passa a ser registrada nos novos acessos.', 'en' => 'Location is estimated from registration data or IP and is recorded for new visits.', 'es' => 'La ubicación se estima por el registro o la IP y se guarda en las nuevas visitas.'],
        ];

        DB::table('languages')->insertOrIgnore(array_map(
            fn (array $translation) => $translation + ['created_at' => $now, 'updated_at' => $now],
            $translations
        ));

        Cache::forget('all_translations_db');
    }

    public function down(): void
    {
        // Preserve translations that may already be in use.
    }
};
