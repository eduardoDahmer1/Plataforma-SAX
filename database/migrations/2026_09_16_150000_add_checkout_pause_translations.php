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
            ['key' => 'checkout_pause_message', 'pt' => 'O pagamento online está temporariamente indisponível. Você pode enviar seu pedido pelo WhatsApp e nossa equipe ajudará a confirmar a disponibilidade e finalizar a compra.', 'en' => 'Online payment is temporarily unavailable. You can send your order via WhatsApp and our team will help confirm availability and complete your purchase.', 'es' => 'El pago online no está disponible temporalmente. Puedes enviar tu pedido por WhatsApp y nuestro equipo te ayudará a confirmar disponibilidad y finalizar la compra.'],
            ['key' => 'checkout_pause_send', 'pt' => 'Enviar pedido pelo WhatsApp', 'en' => 'Send order via WhatsApp', 'es' => 'Enviar pedido por WhatsApp'],
            ['key' => 'checkout_pause_intro', 'pt' => 'Olá! Gostaria de confirmar a disponibilidade e finalizar este pedido:', 'en' => 'Hello! I would like to confirm availability and complete this order:', 'es' => '¡Hola! Quisiera confirmar disponibilidad y finalizar este pedido:'],
            ['key' => 'checkout_pause_quantity', 'pt' => 'Quantidade', 'en' => 'Quantity', 'es' => 'Cantidad'],
            ['key' => 'checkout_pause_unit_price', 'pt' => 'Preço unitário', 'en' => 'Unit price', 'es' => 'Precio unitario'],
            ['key' => 'checkout_pause_subtotal', 'pt' => 'Subtotal dos produtos', 'en' => 'Product subtotal', 'es' => 'Subtotal de productos'],
            ['key' => 'checkout_pause_reference', 'pt' => 'Preços e disponibilidade sujeitos a confirmação. Não inclui frete nem descontos.', 'en' => 'Prices and availability are subject to confirmation. Shipping and discounts are not included.', 'es' => 'Precios y disponibilidad sujetos a confirmación. No incluye envío ni descuentos.'],
            ['key' => 'checkout_pause_empty_cart', 'pt' => 'Seu carrinho está vazio.', 'en' => 'Your cart is empty.', 'es' => 'Tu carrito está vacío.'],
            ['key' => 'checkout_pause_no_contact', 'pt' => 'O contato de WhatsApp está indisponível. Tente novamente mais tarde.', 'en' => 'The WhatsApp contact is unavailable. Please try again later.', 'es' => 'El contacto de WhatsApp no está disponible. Inténtalo de nuevo más tarde.'],
            ['key' => 'checkout_pause_stock_changed', 'pt' => 'A disponibilidade de um produto mudou. Revise as quantidades do carrinho antes de continuar.', 'en' => 'Product availability has changed. Review your cart quantities before continuing.', 'es' => 'La disponibilidad de un producto cambió. Revisa las cantidades del carrito antes de continuar.'],
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
