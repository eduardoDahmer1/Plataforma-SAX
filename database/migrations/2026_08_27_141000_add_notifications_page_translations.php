<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const KEYS = [
        'notifications_menu',
        'notifications_open_page',
        'notifications_page_title',
        'notifications_page_subtitle',
        'notifications_search_placeholder',
        'notifications_stat_total',
        'notifications_stat_active',
        'notifications_stat_archived',
        'notifications_stat_unread',
        'notifications_open',
        'notifications_apply_filters',
        'notifications_clear_filters',
        'notifications_page_empty',
        'notifications_created_at',
        'notifications_status_read',
        'notifications_status_unread',
        'notifications_status_archived',
    ];

    public function up(): void
    {
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
                ['notifications_menu', 'Notificações', 'Notifications', 'Notificaciones'],
                ['notifications_open_page', 'Ver todas', 'View all', 'Ver todas'],
                ['notifications_page_title', 'Central de notificações', 'Notification center', 'Centro de notificaciones'],
                ['notifications_page_subtitle', 'Acompanhe e organize todas as movimentações do painel.', 'Track and organize all dashboard activity.', 'Consulta y organiza toda la actividad del panel.'],
                ['notifications_search_placeholder', 'Buscar por título ou conteúdo...', 'Search by title or content...', 'Buscar por título o contenido...'],
                ['notifications_stat_total', 'Total', 'Total', 'Total'],
                ['notifications_stat_active', 'Ativas', 'Active', 'Activas'],
                ['notifications_stat_archived', 'Arquivadas', 'Archived', 'Archivadas'],
                ['notifications_stat_unread', 'Não lidas', 'Unread', 'No leídas'],
                ['notifications_open', 'Abrir', 'Open', 'Abrir'],
                ['notifications_apply_filters', 'Aplicar filtros', 'Apply filters', 'Aplicar filtros'],
                ['notifications_clear_filters', 'Limpar filtros', 'Clear filters', 'Limpiar filtros'],
                ['notifications_page_empty', 'Nenhuma notificação encontrada com esses filtros.', 'No notifications were found with these filters.', 'No se encontraron notificaciones con estos filtros.'],
                ['notifications_created_at', 'Recebida em', 'Received at', 'Recibida el'],
                ['notifications_status_read', 'Lida', 'Read', 'Leída'],
                ['notifications_status_unread', 'Não lida', 'Unread', 'No leída'],
                ['notifications_status_archived', 'Arquivada', 'Archived', 'Archivada'],
            ]
        ));
    }

    public function down(): void
    {
        DB::table('languages')->whereIn('key', self::KEYS)->delete();
    }
};
