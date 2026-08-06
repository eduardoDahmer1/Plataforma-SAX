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
            ['key' => 'catalog_purchase_paused_title', 'pt' => 'Catálogo em atualização', 'en' => 'Catalog update in progress', 'es' => 'Catálogo en actualización'],
            ['key' => 'catalog_purchase_paused_banner', 'pt' => 'As compras estão temporariamente pausadas. Você pode continuar navegando pela loja.', 'en' => 'Purchases are temporarily paused. You can continue browsing the store.', 'es' => 'Las compras están temporalmente pausadas. Puedes continuar navegando por la tienda.'],
            ['key' => 'catalog_purchase_paused_message', 'pt' => 'Estamos atualizando as informações e a disponibilidade dos produtos. Para sua segurança, não é possível adicionar itens nem finalizar compras neste momento. Tente novamente em breve.', 'en' => 'We are updating product information and availability. For your safety, items cannot be added and purchases cannot be completed at this time. Please try again shortly.', 'es' => 'Estamos actualizando la información y disponibilidad de los productos. Por tu seguridad, no es posible agregar artículos ni finalizar compras en este momento. Inténtalo nuevamente en breve.'],
            ['key' => 'catalog_purchase_paused_continue', 'pt' => 'Continuar navegando', 'en' => 'Continue browsing', 'es' => 'Continuar navegando'],
            ['key' => 'catalog_purchase_paused_button', 'pt' => 'Compra temporariamente indisponível', 'en' => 'Purchasing temporarily unavailable', 'es' => 'Compra temporalmente no disponible'],
        ];

        DB::table('languages')->insertOrIgnore(array_map(
            fn (array $translation) => $translation + ['created_at' => $now, 'updated_at' => $now],
            $translations
        ));

        Cache::forget('all_translations_db');
    }

    public function down(): void
    {
        // Preserva traduções que possam ter sido personalizadas no painel.
    }
};
