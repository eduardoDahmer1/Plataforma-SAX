<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('read_at');
            $table->index(['user_id', 'archived_at'], 'notifications_user_archived_index');
        });

        DB::table('languages')->insertOrIgnore(array_map(
            static fn (array $translation): array => [
                'key' => $translation[0],
                'pt' => $translation[1],
                'en' => $translation[2],
                'es' => $translation[3],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                ['notifications_view_label', 'Visualizar notificações', 'View notifications', 'Ver notificaciones'],
                ['notifications_view_active', 'Não arquivadas', 'Not archived', 'No archivadas'],
                ['notifications_view_archived', 'Ver arquivadas', 'View archived', 'Ver archivadas'],
                ['notifications_view_all', 'Ver ativas e arquivadas', 'View active and archived', 'Ver activas y archivadas'],
                ['notifications_limit_label', 'Quantidade exibida', 'Displayed quantity', 'Cantidad mostrada'],
                ['notifications_limit_all', 'Mostrar tudo', 'Show all', 'Mostrar todo'],
                ['notifications_limit_count', 'Mostrar :count', 'Show :count', 'Mostrar :count'],
                ['notifications_select', 'Selecionar notificação', 'Select notification', 'Seleccionar notificación'],
                ['notifications_select_all', 'Selecionar visíveis', 'Select visible', 'Seleccionar visibles'],
                ['notifications_selected_count', ':count selecionada(s)', ':count selected', ':count seleccionada(s)'],
                ['notifications_archive', 'Arquivar', 'Archive', 'Archivar'],
                ['notifications_restore', 'Restaurar', 'Restore', 'Restaurar'],
                ['notifications_delete', 'Excluir', 'Delete', 'Eliminar'],
                ['notifications_delete_confirm', 'Excluir permanentemente as notificações selecionadas?', 'Permanently delete the selected notifications?', '¿Eliminar permanentemente las notificaciones seleccionadas?'],
                ['notifications_action_error', 'Não foi possível concluir a ação. Tente novamente.', 'The action could not be completed. Try again.', 'No se pudo completar la acción. Inténtalo de nuevo.'],
            ]
        ));
    }

    public function down(): void
    {
        DB::table('languages')->whereIn('key', [
            'notifications_view_label',
            'notifications_view_active',
            'notifications_view_archived',
            'notifications_view_all',
            'notifications_limit_label',
            'notifications_limit_all',
            'notifications_limit_count',
            'notifications_select',
            'notifications_select_all',
            'notifications_selected_count',
            'notifications_archive',
            'notifications_restore',
            'notifications_delete',
            'notifications_delete_confirm',
            'notifications_action_error',
        ])->delete();

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_archived_index');
            $table->dropColumn('archived_at');
        });
    }
};
