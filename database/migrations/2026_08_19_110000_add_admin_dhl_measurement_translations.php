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
            [
                'key' => 'admin_dhl_measurement_summary',
                'pt' => 'DHL · :weight kg · :length × :width × :height cm',
                'en' => 'DHL · :weight kg · :length × :width × :height cm',
                'es' => 'DHL · :weight kg · :length × :width × :height cm',
            ],
            [
                'key' => 'admin_dhl_measurement_review',
                'pt' => 'DHL · requer revisão manual',
                'en' => 'DHL · manual review required',
                'es' => 'DHL · requiere revisión manual',
            ],
            [
                'key' => 'admin_dhl_measurement_estimated_title',
                'pt' => 'Média predefinida. Origem: :source',
                'en' => 'Preset average. Source: :source',
                'es' => 'Promedio predefinido. Origen: :source',
            ],
            [
                'key' => 'admin_dhl_measurement_exact_title',
                'pt' => 'Medidas reais cadastradas no produto',
                'en' => 'Actual measurements saved for the product',
                'es' => 'Medidas reales guardadas en el producto',
            ],
        ];

        DB::table('languages')->insertOrIgnore(array_map(
            fn (array $translation) => $translation + ['created_at' => $now, 'updated_at' => $now],
            $translations
        ));

        Cache::forget('all_translations_db');
    }

    public function down(): void
    {
        // Preserve translations that may have been customized in the admin panel.
    }
};
