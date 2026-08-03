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
            ['key' => 'report_filter_kicker', 'pt' => 'Relatórios gerenciais', 'en' => 'Management reports', 'es' => 'Informes gerenciales'],
            ['key' => 'report_filter_title', 'pt' => 'Consultar e baixar relatório', 'en' => 'View and download report', 'es' => 'Consultar y descargar informe'],
            ['key' => 'report_filter_subtitle', 'pt' => 'Escolha um dia, uma semana, um mês ou um intervalo para visualizar os números e baixar o mesmo período em PDF.', 'en' => 'Choose a day, week, month or custom range to view the figures and download the same period as a PDF.', 'es' => 'Elija un día, una semana, un mes o un intervalo para ver las cifras y descargar el mismo período en PDF.'],
            ['key' => 'report_current_week', 'pt' => 'Semana atual', 'en' => 'Current week', 'es' => 'Semana actual'],
            ['key' => 'report_current_month', 'pt' => 'Mês atual', 'en' => 'Current month', 'es' => 'Mes actual'],
            ['key' => 'report_period_type', 'pt' => 'Período do relatório', 'en' => 'Report period', 'es' => 'Período del informe'],
            ['key' => 'report_type_day', 'pt' => 'Dia específico', 'en' => 'Specific day', 'es' => 'Día específico'],
            ['key' => 'report_type_week', 'pt' => 'Semana específica', 'en' => 'Specific week', 'es' => 'Semana específica'],
            ['key' => 'report_type_month', 'pt' => 'Mês específico', 'en' => 'Specific month', 'es' => 'Mes específico'],
            ['key' => 'report_type_custom', 'pt' => 'Intervalo personalizado', 'en' => 'Custom range', 'es' => 'Intervalo personalizado'],
            ['key' => 'report_choose_day', 'pt' => 'Escolha o dia', 'en' => 'Choose the day', 'es' => 'Elija el día'],
            ['key' => 'report_choose_week', 'pt' => 'Escolha a semana', 'en' => 'Choose the week', 'es' => 'Elija la semana'],
            ['key' => 'report_choose_month', 'pt' => 'Escolha o mês', 'en' => 'Choose the month', 'es' => 'Elija el mes'],
            ['key' => 'report_start_date', 'pt' => 'Data inicial', 'en' => 'Start date', 'es' => 'Fecha inicial'],
            ['key' => 'report_end_date', 'pt' => 'Data final', 'en' => 'End date', 'es' => 'Fecha final'],
            ['key' => 'report_view', 'pt' => 'Visualizar relatório', 'en' => 'View report', 'es' => 'Ver informe'],
            ['key' => 'report_download_pdf', 'pt' => 'Baixar PDF', 'en' => 'Download PDF', 'es' => 'Descargar PDF'],
            ['key' => 'report_selected_period', 'pt' => 'Período visualizado', 'en' => 'Displayed period', 'es' => 'Período visualizado'],
            ['key' => 'report_period_day_label', 'pt' => 'Dia :date', 'en' => 'Day :date', 'es' => 'Día :date'],
            ['key' => 'report_period_week_label', 'pt' => 'Semana de :start a :end', 'en' => 'Week from :start to :end', 'es' => 'Semana del :start al :end'],
            ['key' => 'report_period_month_label', 'pt' => ':month', 'en' => ':month', 'es' => ':month'],
            ['key' => 'report_period_custom_label', 'pt' => 'Período de :start a :end', 'en' => 'Period from :start to :end', 'es' => 'Período del :start al :end'],
        ];

        DB::table('languages')->insertOrIgnore(array_map(
            fn (array $translation) => $translation + ['created_at' => $now, 'updated_at' => $now],
            $translations
        ));

        Cache::forget('all_translations_db');
    }

    public function down(): void
    {
        // As traduções são preservadas para não remover chaves que já existiam no painel.
    }
};
