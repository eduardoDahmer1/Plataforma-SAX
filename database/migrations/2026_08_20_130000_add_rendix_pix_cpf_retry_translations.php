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
            ['key' => 'pix_status_cpf_reversal', 'pt' => 'Pix devolvido por divergência de CPF.', 'en' => 'Pix reversed due to a CPF mismatch.', 'es' => 'Pix devuelto por divergencia de CPF.'],
            ['key' => 'rendix_pix_cpf_mismatch_status', 'pt' => 'Pagamento devolvido por divergência de CPF', 'en' => 'Payment reversed due to a CPF mismatch', 'es' => 'Pago devuelto por divergencia de CPF'],
            ['key' => 'rendix_pix_cpf_mismatch_message', 'pt' => 'O banco devolveu este Pix porque o CPF do titular da conta não coincide com o informado no pedido.', 'en' => 'The bank reversed this Pix because the account holder CPF does not match the CPF on the order.', 'es' => 'El banco devolvió este Pix porque el CPF del titular de la cuenta no coincide con el informado en el pedido.'],
            ['key' => 'rendix_pix_cpf_mismatch_action_title', 'pt' => 'Este QR Code não pode ser reutilizado.', 'en' => 'This QR Code cannot be reused.', 'es' => 'Este código QR no puede reutilizarse.'],
            ['key' => 'rendix_pix_cpf_mismatch_action_message', 'pt' => 'Confira o CPF no seu cadastro e gere um novo Pix. Será criada uma nova cobrança sem alterar os itens do pedido.', 'en' => 'Check the CPF in your profile and generate a new Pix. A new charge will be created without changing the order items.', 'es' => 'Verifique el CPF en su perfil y genere un nuevo Pix. Se creará un nuevo cobro sin modificar los artículos del pedido.'],
            ['key' => 'rendix_pix_cpf_update_required', 'pt' => 'Atualize no cadastro um CPF válido, pertencente ao titular da conta Pix, antes de gerar um novo QR Code.', 'en' => 'Update your profile with a valid CPF belonging to the Pix account holder before generating a new QR Code.', 'es' => 'Actualice su perfil con un CPF válido del titular de la cuenta Pix antes de generar un nuevo código QR.'],
            ['key' => 'rendix_pix_new_generated', 'pt' => 'Um novo Pix foi gerado com o CPF atualizado.', 'en' => 'A new Pix was generated with the updated CPF.', 'es' => 'Se generó un nuevo Pix con el CPF actualizado.'],
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
