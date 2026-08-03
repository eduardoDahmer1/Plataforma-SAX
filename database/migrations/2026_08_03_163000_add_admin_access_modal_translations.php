<?php

use Illuminate\Database\Migrations\Migration;
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
            ['key' => 'admin_access_restricted_title', 'pt' => 'Acesso restrito', 'en' => 'Restricted access', 'es' => 'Acceso restringido'],
            ['key' => 'admin_access_restricted_message', 'pt' => 'Seu perfil não possui permissão para acessar a área :area. Você foi direcionado de volta à visão geral do painel.', 'en' => 'Your profile does not have permission to access the :area area. You have been redirected to the admin overview.', 'es' => 'Tu perfil no tiene permiso para acceder al área :area. Fuiste redirigido a la vista general del panel.'],
            ['key' => 'admin_area_sales', 'pt' => 'Vendas', 'en' => 'Sales', 'es' => 'Ventas'],
            ['key' => 'admin_area_system', 'pt' => 'Sistema e administração', 'en' => 'System and administration', 'es' => 'Sistema y administración'],
            ['key' => 'admin_back_to_dashboard', 'pt' => 'Voltar ao painel', 'en' => 'Back to dashboard', 'es' => 'Volver al panel'],
        ];

        DB::table('languages')->insertOrIgnore(array_map(
            fn (array $translation) => $translation + ['created_at' => $now, 'updated_at' => $now],
            $translations
        ));
    }

    public function down(): void
    {
        // Não removemos traduções que podem ter sido personalizadas no painel.
    }
};
