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
            ['key' => 'dashboard_countries_on_demand_note', 'pt' => 'Os países ficam fora do carregamento inicial e abrem somente quando necessário.', 'en' => 'Countries stay out of the initial load and open only when needed.', 'es' => 'Los países no se cargan al inicio y se abren solo cuando sea necesario.'],
            ['key' => 'dashboard_view_countries', 'pt' => 'Ver países', 'en' => 'View countries', 'es' => 'Ver países'],
            ['key' => 'dashboard_loading_data', 'pt' => 'Carregando dados…', 'en' => 'Loading data…', 'es' => 'Cargando datos…'],
            ['key' => 'dashboard_no_country_data', 'pt' => 'Nenhum país identificado neste período.', 'en' => 'No countries identified in this period.', 'es' => 'No se identificaron países en este período.'],
            ['key' => 'dashboard_integration_compact_note', 'pt' => 'O monitor foi resumido para manter a visão geral leve. O histórico técnico completo permanece disponível na área do integrador.', 'en' => 'The monitor was condensed to keep the overview lightweight. The full technical history remains available in the integration area.', 'es' => 'El monitor se resumió para mantener ligera la vista general. El historial técnico completo sigue disponible en el área del integrador.'],
            ['key' => 'dashboard_device_audience', 'pt' => 'Público por dispositivo e tela', 'en' => 'Audience by device and screen', 'es' => 'Público por dispositivo y pantalla'],
            ['key' => 'dashboard_device_audience_note', 'pt' => 'Visitantes únicos e visualizações separados por desktop, celular e tablet.', 'en' => 'Unique visitors and views split by desktop, mobile, and tablet.', 'es' => 'Visitantes únicos y visualizaciones separados por computadora, celular y tablet.'],
            ['key' => 'dashboard_analytics_summary', 'pt' => 'Resumo de tráfego', 'en' => 'Traffic summary', 'es' => 'Resumen de tráfico'],
            ['key' => 'dashboard_screen_sizes', 'pt' => 'Telas mais usadas', 'en' => 'Most used screens', 'es' => 'Pantallas más usadas'],
            ['key' => 'dashboard_screen_sizes_note', 'pt' => 'Área útil do navegador (largura × altura)', 'en' => 'Browser viewport (width × height)', 'es' => 'Área útil del navegador (ancho × alto)'],
            ['key' => 'dashboard_screen_capture_notice', 'pt' => 'As resoluções aparecerão aqui conforme novos acessos forem registrados.', 'en' => 'Screen sizes will appear here as new visits are recorded.', 'es' => 'Las resoluciones aparecerán aquí a medida que se registren nuevas visitas.'],
            ['key' => 'dashboard_secondary_information', 'pt' => 'Informações secundárias', 'en' => 'Secondary information', 'es' => 'Información secundaria'],
            ['key' => 'dashboard_detailed_analysis', 'pt' => 'Análise detalhada', 'en' => 'Detailed analysis', 'es' => 'Análisis detallado'],
            ['key' => 'dashboard_detailed_analysis_note', 'pt' => 'Gráficos, rankings, pedidos e eventos são carregados somente ao abrir esta seção.', 'en' => 'Charts, rankings, orders, and events load only when this section is opened.', 'es' => 'Los gráficos, rankings, pedidos y eventos se cargan solo al abrir esta sección.'],
            ['key' => 'dashboard_open_analysis', 'pt' => 'Abrir análise completa', 'en' => 'Open full analysis', 'es' => 'Abrir análisis completo'],
            ['key' => 'dashboard_minimize_analysis', 'pt' => 'Minimizar análise', 'en' => 'Minimize analysis', 'es' => 'Minimizar análisis'],
            ['key' => 'dashboard_data_loaded', 'pt' => 'Dados atualizados', 'en' => 'Updated data', 'es' => 'Datos actualizados'],
            ['key' => 'close', 'pt' => 'Fechar', 'en' => 'Close', 'es' => 'Cerrar'],
        ];

        DB::table('languages')->upsert(
            array_map(fn (array $row) => $row + ['created_at' => $now, 'updated_at' => $now], $translations),
            ['key'],
            ['pt', 'en', 'es', 'updated_at']
        );

        Cache::forget('all_translations_db');
    }

    public function down(): void
    {
        // Keep translations that may already be in use by published environments.
    }
};
