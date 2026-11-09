@extends('layout.admin')

@section('title', 'Relatórios')

@section('content')
@include('admin.dashboard.partials.page-header', [
    'eyebrow' => 'Resultados', 'title' => 'Relatórios', 'icon' => 'fa-file-lines',
    'description' => 'Escolha o período e carregue somente os dados necessários para a análise comercial.',
])

<section class="table-card mb-4">
    <div class="dashboard-quick-periods mb-3"><span>Atalhos:</span><a href="{{ route('admin.dashboard.reports', ['report_type' => 'day', 'report_day' => now()->format('Y-m-d')]) }}">Hoje</a><a href="{{ route('admin.dashboard.reports', ['report_type' => 'week', 'report_week' => now()->format('o-\WW')]) }}">Esta semana</a><a href="{{ route('admin.dashboard.reports', ['report_type' => 'month', 'report_month' => now()->format('Y-m')]) }}">Este mês</a></div>
    <form method="GET" action="{{ route('admin.dashboard.reports') }}" id="dashboard-report-form">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-3"><label class="form-label fw-semibold" for="report-type">Tipo de período</label><select class="form-select" id="report-type" name="report_type"><option value="day" @selected($reportFilter['type'] === 'day')>Dia</option><option value="week" @selected($reportFilter['type'] === 'week')>Semana</option><option value="month" @selected($reportFilter['type'] === 'month')>Mês</option><option value="custom" @selected($reportFilter['type'] === 'custom')>Personalizado</option></select></div>
            <div class="col-12 col-md-3 report-period-field" data-report-period="day" @if($reportFilter['type'] !== 'day') hidden @endif><label class="form-label fw-semibold" for="report-day">Dia</label><input class="form-control" id="report-day" name="report_day" type="date" value="{{ $reportFilter['day'] }}"></div>
            <div class="col-12 col-md-3 report-period-field" data-report-period="week" @if($reportFilter['type'] !== 'week') hidden @endif><label class="form-label fw-semibold" for="report-week">Semana</label><input class="form-control" id="report-week" name="report_week" type="week" value="{{ $reportFilter['week'] }}"></div>
            <div class="col-12 col-md-3 report-period-field" data-report-period="month" @if($reportFilter['type'] !== 'month') hidden @endif><label class="form-label fw-semibold" for="report-month">Mês</label><input class="form-control" id="report-month" name="report_month" type="month" value="{{ $reportFilter['month'] }}"></div>
            <div class="col-6 col-md-3 report-period-field" data-report-period="custom" @if($reportFilter['type'] !== 'custom') hidden @endif><label class="form-label fw-semibold" for="report-start">Início</label><input class="form-control" id="report-start" name="report_start" type="date" value="{{ $reportFilter['start'] }}"></div>
            <div class="col-6 col-md-3 report-period-field" data-report-period="custom" @if($reportFilter['type'] !== 'custom') hidden @endif><label class="form-label fw-semibold" for="report-end">Fim</label><input class="form-control" id="report-end" name="report_end" type="date" value="{{ $reportFilter['end'] }}"></div>
            <div class="col-12 col-md-auto"><button class="btn btn-dark w-100" type="submit"><i class="fa-solid fa-filter me-1"></i> Aplicar período</button></div>
        </div>
    </form>
</section>

@php
    $paymentConversion = $selectedReport['orders'] > 0 ? round(($selectedReport['paid_orders'] / $selectedReport['orders']) * 100) : 0;
    $pagesPerVisitor = $selectedReport['visitors'] > 0 ? round($selectedReport['views'] / $selectedReport['visitors'], 2) : 0;
@endphp
<div class="dashboard-report-period mb-3"><i class="fa-regular fa-calendar"></i><strong>{{ $selectedReport['period'] }}</strong></div>
<div class="dashboard-reading-guide mb-4"><i class="fa-solid fa-circle-info"></i><div><strong>Resumo do período</strong><span>{{ $paymentConversion }}% dos pedidos criados foram confirmados como pagos. Cada visitante abriu em média {{ number_format($pagesPerVisitor, 2, ',', '.') }} páginas. Compare períodos equivalentes para evitar conclusões incorretas.</span></div></div>
<div class="row g-3 mb-4">
    @foreach ([
        ['Vendas pagas', $selectedReport['paid_orders'], 'fa-circle-check', 'bg-green'],
        ['Valor vendido', 'US$ '.number_format($selectedReport['sales_total'], 2, ',', '.'), 'fa-money-bill-trend-up', 'bg-blue'],
        ['Pedidos criados', $selectedReport['orders'], 'fa-receipt', 'bg-purple'],
        ['Clientes novos', $selectedReport['new_customers'], 'fa-user-plus', 'bg-cyan'],
        ['Visitantes', $selectedReport['visitors'], 'fa-users', 'bg-gold'],
        ['Cliques', $selectedReport['clicks'], 'fa-arrow-pointer', 'bg-blue'],
        ['Páginas vistas', $selectedReport['views'], 'fa-eye', 'bg-green'],
        ['Carrinhos abandonados', $selectedReport['abandoned_carts'], 'fa-cart-arrow-down', 'bg-red'],
    ] as [$label,$value,$icon,$tone])
        <div class="col-6 col-lg-3"><div class="metric-card"><div class="metric-icon {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></div><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ is_numeric($value) ? number_format($value, 0, ',', '.') : $value }}</div><div class="metric-note">Somente no período selecionado</div></div></div>
    @endforeach
</div>

<section class="table-card">
    <div class="dashboard-report-actions">
        <div><h2 class="card-heading">Exportação e detalhes</h2><p class="text-muted small mb-0">O PDF e os países são carregados apenas quando solicitados.</p></div>
        <div><button class="btn btn-outline-dark" type="button" data-bs-toggle="modal" data-bs-target="#dashboardCountriesModal" data-dashboard-countries-url="{{ route('admin.dashboard.countries', request()->query()) }}"><i class="fa-solid fa-earth-americas me-1"></i> Ver países</button><a class="btn btn-dark" href="{{ route('admin.reports.download', request()->query()) }}"><i class="fa-solid fa-file-pdf me-1"></i> Baixar PDF</a></div>
    </div>
</section>

<div class="modal fade" id="dashboardCountriesModal" tabindex="-1" aria-hidden="true" data-loading-label="Carregando países..."><div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"><div class="modal-content dashboard-modal-content"><div class="modal-header"><h2 class="modal-title fs-6 fw-bold">Visitantes por país</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div><div class="modal-body" data-dashboard-countries-body><div class="dashboard-loading"><span class="spinner-border spinner-border-sm"></span>Carregando países...</div></div></div></div></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('report-type');
    const periodFields = document.querySelectorAll('.report-period-field');
    if (!typeSelect) return;
    const update = function () { periodFields.forEach(function (field) { field.hidden = field.dataset.reportPeriod !== typeSelect.value; }); };
    typeSelect.addEventListener('change', update); update();
});
</script>
@endsection
