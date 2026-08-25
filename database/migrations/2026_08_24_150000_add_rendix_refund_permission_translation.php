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

        DB::table('languages')->insertOrIgnore([
            [
                'key' => 'rendix_refund_permission_denied',
                'pt' => 'A Rendix recusou o reembolso porque o operador ou MerchantId de produção não possui permissão para cancelar vendas. Solicite à Rendix/Rendimento Pay que habilite “Permitir cancelar venda” e reembolso para este operador e tente novamente.',
                'en' => 'Rendix rejected the refund because the production operator or MerchantId is not authorized to cancel sales. Ask Rendix/Rendimento Pay to enable sale cancellation and refund permissions for this operator, then try again.',
                'es' => 'Rendix rechazó el reembolso porque el operador o MerchantId de producción no tiene permiso para cancelar ventas. Solicite a Rendix/Rendimento Pay que habilite la cancelación de ventas y reembolsos para este operador y vuelva a intentarlo.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Cache::forget('all_translations_db');
    }

    public function down(): void
    {
        // Preserva a tradução caso ela tenha sido personalizada no painel.
    }
};
