<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'refund_request_status')) {
                $table->string('refund_request_status', 24)->nullable()->index()->after('payment_response_message');
            }
            if (! Schema::hasColumn('orders', 'refund_request_reason')) {
                $table->string('refund_request_reason', 60)->nullable()->after('refund_request_status');
            }
            if (! Schema::hasColumn('orders', 'refund_request_details')) {
                $table->text('refund_request_details')->nullable()->after('refund_request_reason');
            }
            if (! Schema::hasColumn('orders', 'refund_requested_at')) {
                $table->timestamp('refund_requested_at')->nullable()->after('refund_request_details');
            }
            if (! Schema::hasColumn('orders', 'refund_request_resolved_at')) {
                $table->timestamp('refund_request_resolved_at')->nullable()->after('refund_requested_at');
            }
            if (! Schema::hasColumn('orders', 'refund_request_resolved_by')) {
                $table->unsignedBigInteger('refund_request_resolved_by')->nullable()->after('refund_request_resolved_at');
            }
        });

        if (! Schema::hasTable('languages')) {
            return;
        }

        $now = now();
        $translations = [
            ['key' => 'rendix_refund_request_title', 'pt' => 'Solicitar cancelamento e reembolso', 'en' => 'Request cancellation and refund', 'es' => 'Solicitar cancelación y reembolso'],
            ['key' => 'rendix_refund_request_description', 'pt' => 'Se você não deseja mais este pedido, envie uma solicitação para nossa equipe analisar. O reembolso não é automático.', 'en' => 'If you no longer want this order, send a request for our team to review. The refund is not automatic.', 'es' => 'Si ya no desea este pedido, envíe una solicitud para que nuestro equipo la revise. El reembolso no es automático.'],
            ['key' => 'rendix_refund_request_reason_label', 'pt' => 'Motivo da solicitação', 'en' => 'Request reason', 'es' => 'Motivo de la solicitud'],
            ['key' => 'rendix_refund_reason_changed_mind', 'pt' => 'Desisti da compra', 'en' => 'I changed my mind', 'es' => 'Cambié de opinión'],
            ['key' => 'rendix_refund_reason_duplicate', 'pt' => 'Pedido ou pagamento duplicado', 'en' => 'Duplicate order or payment', 'es' => 'Pedido o pago duplicado'],
            ['key' => 'rendix_refund_reason_wrong_data', 'pt' => 'Informei dados incorretos', 'en' => 'I entered incorrect information', 'es' => 'Ingresé datos incorrectos'],
            ['key' => 'rendix_refund_reason_delivery', 'pt' => 'Problema com prazo ou entrega', 'en' => 'Delivery or deadline issue', 'es' => 'Problema con el plazo o la entrega'],
            ['key' => 'rendix_refund_reason_other', 'pt' => 'Outro motivo', 'en' => 'Other reason', 'es' => 'Otro motivo'],
            ['key' => 'rendix_refund_request_details_label', 'pt' => 'Conte mais detalhes (opcional)', 'en' => 'Add more details (optional)', 'es' => 'Agregue más detalles (opcional)'],
            ['key' => 'rendix_refund_request_submit', 'pt' => 'Enviar solicitação', 'en' => 'Submit request', 'es' => 'Enviar solicitud'],
            ['key' => 'rendix_refund_request_confirm', 'pt' => 'Deseja enviar esta solicitação de cancelamento e reembolso para análise?', 'en' => 'Do you want to submit this cancellation and refund request for review?', 'es' => '¿Desea enviar esta solicitud de cancelación y reembolso para revisión?'],
            ['key' => 'rendix_refund_request_success', 'pt' => 'Solicitação enviada. Nossa equipe analisará o cancelamento e o reembolso.', 'en' => 'Request submitted. Our team will review the cancellation and refund.', 'es' => 'Solicitud enviada. Nuestro equipo revisará la cancelación y el reembolso.'],
            ['key' => 'rendix_refund_request_pending_title', 'pt' => 'Reembolso solicitado', 'en' => 'Refund requested', 'es' => 'Reembolso solicitado'],
            ['key' => 'rendix_refund_request_pending_message', 'pt' => 'Sua solicitação está em análise. Você receberá uma notificação quando houver uma atualização.', 'en' => 'Your request is under review. You will receive a notification when it is updated.', 'es' => 'Su solicitud está en revisión. Recibirá una notificación cuando haya una actualización.'],
            ['key' => 'rendix_refund_request_admin_title', 'pt' => 'Solicitação de reembolso Pix', 'en' => 'Pix refund request', 'es' => 'Solicitud de reembolso Pix'],
            ['key' => 'rendix_refund_request_admin_message', 'pt' => 'O cliente solicitou cancelamento e reembolso do pedido #:reference.', 'en' => 'The customer requested cancellation and refund for order #:reference.', 'es' => 'El cliente solicitó la cancelación y el reembolso del pedido #:reference.'],
            ['key' => 'rendix_refund_request_not_available', 'pt' => 'Este pedido não está disponível para solicitar reembolso.', 'en' => 'This order is not eligible for a refund request.', 'es' => 'Este pedido no está disponible para solicitar un reembolso.'],
            ['key' => 'rendix_refund_request_already_pending', 'pt' => 'Já existe uma solicitação de reembolso em análise para este pedido.', 'en' => 'There is already a refund request under review for this order.', 'es' => 'Ya existe una solicitud de reembolso en revisión para este pedido.'],
            ['key' => 'rendix_refund_request_admin_pending', 'pt' => 'O cliente solicitou o reembolso deste pedido.', 'en' => 'The customer requested a refund for this order.', 'es' => 'El cliente solicitó el reembolso de este pedido.'],
            ['key' => 'rendix_refund_request_requested_at', 'pt' => 'Solicitado em', 'en' => 'Requested at', 'es' => 'Solicitado el'],
            ['key' => 'rendix_refund_request_reason', 'pt' => 'Motivo', 'en' => 'Reason', 'es' => 'Motivo'],
        ];

        DB::table('languages')->insertOrIgnore(array_map(
            fn (array $translation): array => $translation + ['created_at' => $now, 'updated_at' => $now],
            $translations,
        ));

        Cache::forget('all_translations_db');
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $columns = [
                'refund_request_status',
                'refund_request_reason',
                'refund_request_details',
                'refund_requested_at',
                'refund_request_resolved_at',
                'refund_request_resolved_by',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
