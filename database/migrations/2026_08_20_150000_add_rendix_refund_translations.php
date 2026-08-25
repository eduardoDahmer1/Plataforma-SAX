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
            ['key' => 'rendix_cpf_holder_warning_title', 'pt' => 'O CPF precisa ser do titular da conta Pix', 'en' => 'The CPF must belong to the Pix account holder', 'es' => 'El CPF debe pertenecer al titular de la cuenta Pix'],
            ['key' => 'rendix_cpf_holder_warning_message', 'pt' => 'Pague usando uma conta bancária do mesmo titular do CPF informado no pedido. Se os CPFs forem diferentes, a Rendix cancelará a cobrança e devolverá o Pix.', 'en' => 'Pay from a bank account held by the same CPF entered on the order. If the CPFs differ, Rendix will cancel the charge and return the Pix payment.', 'es' => 'Pague desde una cuenta bancaria del mismo titular del CPF informado en el pedido. Si los CPF son diferentes, Rendix cancelará el cobro y devolverá el Pix.'],
            ['key' => 'rendix_refund_card_title', 'pt' => 'Cancelar e reembolsar Pix', 'en' => 'Cancel and refund Pix', 'es' => 'Cancelar y reembolsar Pix'],
            ['key' => 'rendix_refund_card_description', 'pt' => 'Executa primeiro a simulação e depois confirma o reembolso integral diretamente na Rendix.', 'en' => 'First previews and then confirms the full refund directly with Rendix.', 'es' => 'Primero simula y luego confirma el reembolso total directamente con Rendix.'],
            ['key' => 'rendix_refund_amount', 'pt' => 'Valor a reembolsar', 'en' => 'Refund amount', 'es' => 'Importe a reembolsar'],
            ['key' => 'rendix_refund_action', 'pt' => 'Cancelar venda e reembolsar', 'en' => 'Cancel sale and refund', 'es' => 'Cancelar venta y reembolsar'],
            ['key' => 'rendix_refund_confirm', 'pt' => 'Confirma o cancelamento da venda e o reembolso integral do Pix? Esta operação financeira não pode ser desfeita.', 'en' => 'Confirm sale cancellation and full Pix refund? This financial operation cannot be undone.', 'es' => '¿Confirma la cancelación de la venta y el reembolso total del Pix? Esta operación financiera no se puede deshacer.'],
            ['key' => 'rendix_refund_irreversible_notice', 'pt' => 'O pedido será cancelado, o pagamento ficará como reembolsado e o cliente será notificado.', 'en' => 'The order will be canceled, payment marked as refunded, and the customer notified.', 'es' => 'El pedido se cancelará, el pago quedará como reembolsado y el cliente será notificado.'],
            ['key' => 'rendix_refund_success', 'pt' => 'Reembolso Pix confirmado com sucesso.', 'en' => 'Pix refund confirmed successfully.', 'es' => 'Reembolso Pix confirmado correctamente.'],
            ['key' => 'rendix_refund_completed_message', 'pt' => 'Venda cancelada e pagamento reembolsado pela Rendix.', 'en' => 'Sale canceled and payment refunded by Rendix.', 'es' => 'Venta cancelada y pago reembolsado por Rendix.'],
            ['key' => 'rendix_refund_event_message', 'pt' => 'O pedido #:reference teve reembolso integral de US$ :amount confirmado pela Rendix.', 'en' => 'Order #:reference had a full refund of US$ :amount confirmed by Rendix.', 'es' => 'El pedido #:reference tuvo un reembolso total de US$ :amount confirmado por Rendix.'],
            ['key' => 'rendix_refund_not_available', 'pt' => 'Este pedido não possui um pagamento Pix pago e disponível para reembolso.', 'en' => 'This order does not have a paid Pix transaction available for refund.', 'es' => 'Este pedido no tiene una transacción Pix pagada disponible para reembolso.'],
            ['key' => 'rendix_refund_invalid_amount', 'pt' => 'O valor do reembolso é inválido.', 'en' => 'The refund amount is invalid.', 'es' => 'El importe del reembolso no es válido.'],
            ['key' => 'rendix_refund_preview_failed', 'pt' => 'A Rendix não autorizou a simulação do reembolso. Nenhum reembolso foi realizado.', 'en' => 'Rendix did not authorize the refund preview. No refund was made.', 'es' => 'Rendix no autorizó la simulación del reembolso. No se realizó ningún reembolso.'],
            ['key' => 'rendix_refund_failed', 'pt' => 'Não foi possível confirmar o reembolso na Rendix. Tente novamente ou consulte o suporte.', 'en' => 'The refund could not be confirmed with Rendix. Try again or contact support.', 'es' => 'No fue posible confirmar el reembolso en Rendix. Inténtelo nuevamente o consulte al soporte.'],
        ];

        DB::table('languages')->insertOrIgnore(array_map(
            fn (array $translation): array => $translation + ['created_at' => $now, 'updated_at' => $now],
            $translations,
        ));

        Cache::forget('all_translations_db');
    }

    public function down(): void
    {
        // Preserva traduções que possam ter sido personalizadas no painel.
    }
};
