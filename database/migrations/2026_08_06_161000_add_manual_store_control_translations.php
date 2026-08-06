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

        $now = now();
        $rows = [
            ['key' => 'store_controls_menu', 'pt' => 'Controle da loja', 'en' => 'Store controls', 'es' => 'Control de la tienda'],
            ['key' => 'store_controls_saved', 'pt' => 'Controles da loja atualizados com sucesso.', 'en' => 'Store controls updated successfully.', 'es' => 'Controles de la tienda actualizados correctamente.'],
            ['key' => 'store_cart_disabled_title', 'pt' => 'Carrinho temporariamente indisponível', 'en' => 'Cart temporarily unavailable', 'es' => 'Carrito temporalmente no disponible'],
            ['key' => 'store_cart_disabled_message', 'pt' => 'O carrinho está temporariamente pausado. Continue navegando e fale conosco pelo WhatsApp se precisar de ajuda.', 'en' => 'The cart is temporarily paused. Keep browsing and contact us on WhatsApp if you need help.', 'es' => 'El carrito está temporalmente pausado. Sigue navegando y contáctanos por WhatsApp si necesitas ayuda.'],
            ['key' => 'store_checkout_disabled_message', 'pt' => 'A finalização de compras está temporariamente pausada. Seus itens continuam guardados no carrinho.', 'en' => 'Checkout is temporarily paused. Your items remain saved in the cart.', 'es' => 'La finalización de compras está temporalmente pausada. Tus artículos permanecen guardados en el carrito.'],
            ['key' => 'store_add_to_cart_disabled_message', 'pt' => 'As compras pelo site estão temporariamente pausadas. Fale conosco pelo WhatsApp para consultar este produto.', 'en' => 'Website purchases are temporarily paused. Contact us on WhatsApp about this product.', 'es' => 'Las compras por el sitio están temporalmente pausadas. Contáctanos por WhatsApp sobre este producto.'],
            ['key' => 'store_payment_disabled_message', 'pt' => 'A forma de pagamento selecionada está temporariamente indisponível. Escolha outra opção.', 'en' => 'The selected payment method is temporarily unavailable. Choose another option.', 'es' => 'El método de pago seleccionado está temporalmente no disponible. Elige otra opción.'],
            ['key' => 'store_whatsapp_product_button', 'pt' => 'Consultar pelo WhatsApp', 'en' => 'Ask on WhatsApp', 'es' => 'Consultar por WhatsApp'],
            ['key' => 'store_checkout_paused_button', 'pt' => 'Checkout temporariamente indisponível', 'en' => 'Checkout temporarily unavailable', 'es' => 'Checkout temporalmente no disponible'],
        ];

        DB::table('languages')->insertOrIgnore(array_map(
            fn (array $row): array => $row + ['created_at' => $now, 'updated_at' => $now],
            $rows
        ));
        Cache::forget('all_translations_db');
    }

    public function down(): void
    {
        // Preserva traduções que possam ter sido personalizadas no painel.
    }
};
