<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('languages')) return;

        DB::table('languages')->insertOrIgnore([
            'key' => 'store_payment_disabled_title',
            'pt' => 'Pagamento temporariamente indisponível',
            'en' => 'Payment temporarily unavailable',
            'es' => 'Pago temporalmente no disponible',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::forget('all_translations_db');
    }

    public function down(): void
    {
        // Preserva traduções personalizadas no painel.
    }
};
