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
            ['key' => 'order_notes_admin_title', 'pt' => 'Atualizações do pedido', 'en' => 'Order updates', 'es' => 'Actualizaciones del pedido'],
            ['key' => 'order_notes_admin_subtitle', 'pt' => 'Publique uma observação para manter o cliente informado.', 'en' => 'Publish a note to keep the customer informed.', 'es' => 'Publique una observación para mantener informado al cliente.'],
            ['key' => 'order_notes_select_label', 'pt' => 'Tipo de observação', 'en' => 'Note type', 'es' => 'Tipo de observación'],
            ['key' => 'order_notes_select_placeholder', 'pt' => 'Selecione uma observação', 'en' => 'Select a note', 'es' => 'Seleccione una observación'],
            ['key' => 'order_notes_other', 'pt' => 'Outro — escrever uma observação', 'en' => 'Other — write a note', 'es' => 'Otro — escribir una observación'],
            ['key' => 'order_notes_custom_label', 'pt' => 'Escreva a observação', 'en' => 'Write the note', 'es' => 'Escriba la observación'],
            ['key' => 'order_notes_custom_placeholder', 'pt' => 'Digite a atualização que o cliente deve receber...', 'en' => 'Enter the update the customer should receive...', 'es' => 'Escriba la actualización que debe recibir el cliente...'],
            ['key' => 'order_notes_publish', 'pt' => 'Publicar atualização', 'en' => 'Publish update', 'es' => 'Publicar actualización'],
            ['key' => 'order_notes_history', 'pt' => 'Histórico publicado', 'en' => 'Published history', 'es' => 'Historial publicado'],
            ['key' => 'order_notes_empty', 'pt' => 'Nenhuma atualização foi publicada para este pedido.', 'en' => 'No updates have been published for this order.', 'es' => 'No se publicaron actualizaciones para este pedido.'],
            ['key' => 'order_notes_published_by', 'pt' => 'Publicado por :name', 'en' => 'Published by :name', 'es' => 'Publicado por :name'],
            ['key' => 'order_notes_customer_title', 'pt' => 'Atualizações da equipe SAX', 'en' => 'Updates from the SAX team', 'es' => 'Actualizaciones del equipo SAX'],
            ['key' => 'order_notes_customer_subtitle', 'pt' => 'Acompanhe aqui as observações publicadas sobre seu pedido.', 'en' => 'Follow the notes published about your order here.', 'es' => 'Siga aquí las observaciones publicadas sobre su pedido.'],
            ['key' => 'order_note_created_success', 'pt' => 'Atualização publicada e cliente notificado com sucesso.', 'en' => 'Update published and customer notified successfully.', 'es' => 'Actualización publicada y cliente notificado correctamente.'],
            ['key' => 'notification_customer_order_note_title', 'pt' => 'Pedido atualizado', 'en' => 'Order updated', 'es' => 'Pedido actualizado'],
            ['key' => 'notification_customer_order_note_message', 'pt' => 'O pedido #:reference recebeu uma nova observação da equipe SAX.', 'en' => 'Order #:reference received a new note from the SAX team.', 'es' => 'El pedido #:reference recibió una nueva observación del equipo SAX.'],
            ['key' => 'order_note_preset_order_review', 'pt' => 'Pedido em análise pela nossa equipe.', 'en' => 'Order under review by our team.', 'es' => 'Pedido en análisis por nuestro equipo.'],
            ['key' => 'order_note_preset_payment_review', 'pt' => 'Pagamento em análise.', 'en' => 'Payment under review.', 'es' => 'Pago en análisis.'],
            ['key' => 'order_note_preset_payment_confirmed', 'pt' => 'Pagamento confirmado.', 'en' => 'Payment confirmed.', 'es' => 'Pago confirmado.'],
            ['key' => 'order_note_preset_picking', 'pt' => 'Pedido em separação.', 'en' => 'Order being picked.', 'es' => 'Pedido en preparación.'],
            ['key' => 'order_note_preset_stock_confirmation', 'pt' => 'Aguardando confirmação de estoque.', 'en' => 'Awaiting stock confirmation.', 'es' => 'Esperando confirmación de stock.'],
            ['key' => 'order_note_preset_customer_information', 'pt' => 'Aguardando informações ou documentos do cliente.', 'en' => 'Awaiting customer information or documents.', 'es' => 'Esperando información o documentos del cliente.'],
            ['key' => 'order_note_preset_shipping_preparation', 'pt' => 'Pedido em preparação para envio.', 'en' => 'Order being prepared for shipment.', 'es' => 'Pedido en preparación para envío.'],
            ['key' => 'order_note_preset_shipped', 'pt' => 'Pedido enviado.', 'en' => 'Order shipped.', 'es' => 'Pedido enviado.'],
            ['key' => 'order_note_preset_ready_for_pickup', 'pt' => 'Pedido disponível para retirada.', 'en' => 'Order ready for pickup.', 'es' => 'Pedido disponible para retiro.'],
            ['key' => 'order_note_preset_customer_contact', 'pt' => 'Nossa equipe entrará em contato com o cliente.', 'en' => 'Our team will contact the customer.', 'es' => 'Nuestro equipo se comunicará con el cliente.'],
        ];

        DB::table('languages')->insertOrIgnore(array_map(
            fn (array $translation) => $translation + ['created_at' => $now, 'updated_at' => $now],
            $translations
        ));

        Cache::forget('all_translations_db');
    }

    public function down(): void
    {
        // As traduções são preservadas para não remover chaves já utilizadas pelo painel.
    }
};
