@extends('layout.admin')

@section('title', 'Métricas e tráfego')

@section('content')
@include('admin.dashboard.partials.page-header', [
    'eyebrow' => 'Audiência', 'title' => 'Métricas e tráfego', 'icon' => 'fa-chart-line',
    'description' => 'Dados de navegação separados das vendas e da integração para abrir com mais rapidez.',
])

<section class="table-card dashboard-filter-card mb-4">
    <form method="GET" class="dashboard-filter-grid dashboard-filter-grid--compact">
        <div><label for="metrics-days">Período de audiência</label><select id="metrics-days" name="days" class="form-select"><option value="7" @selected($days === 7)>Últimos 7 dias</option><option value="30" @selected($days === 30)>Últimos 30 dias</option><option value="90" @selected($days === 90)>Últimos 90 dias</option></select></div>
        <div class="dashboard-filter-actions"><button class="btn btn-dark" type="submit"><i class="fa-solid fa-filter me-1"></i> Atualizar visão</button></div>
    </form>
</section>

@if(!$analyticsReady)
    <div class="analytics-empty mb-4"><i class="fa-solid fa-circle-info me-2"></i>O rastreamento de audiência ainda não está disponível.</div>
@endif

<div class="row g-3 mb-4">
    @foreach ([
        ['Visitantes únicos', $analytics['visitors'], 'fa-users-viewfinder', 'bg-purple', 'Pessoas diferentes identificadas'],
        ['Páginas vistas', $analytics['views'], 'fa-eye', 'bg-blue', 'Total de páginas carregadas'],
        ['Cliques registrados', $analytics['clicks'], 'fa-arrow-pointer', 'bg-green', 'Interações rastreadas na loja'],
        ['Páginas por visitante', number_format($analytics['pages_per_visitor'], 2, ',', '.'), 'fa-chart-column', 'bg-gold', 'Média de navegação por pessoa'],
    ] as [$label,$value,$icon,$tone,$note])
        <div class="col-12 col-sm-6 col-xl-3"><div class="metric-card"><div class="metric-icon {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></div><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ is_numeric($value) ? number_format($value, 0, ',', '.') : $value }}</div><div class="metric-note">{{ $note }} · {{ $days }} dias</div></div></div>
    @endforeach
</div>

<section class="traffic-overview table-card mb-4">
    <div class="d-flex justify-content-between gap-3 mb-3"><div><div class="card-kicker">Últimos {{ $days }} dias</div><h2 class="card-heading">Dispositivos utilizados</h2><p class="metric-section-help">Mostra por onde as pessoas acessam. Use esta informação para priorizar testes em celular, computador ou tablet.</p></div><span class="dashboard-live-badge"><i class="fa-solid fa-signal"></i> Resumo de tráfego</span></div>
    <div class="row g-3">
        @php
            $deviceConfig = ['desktop' => ['Computador', 'fa-desktop'], 'mobile' => ['Celular', 'fa-mobile-screen-button'], 'tablet' => ['Tablet', 'fa-tablet-screen-button']];
            $deviceVisitorsTotal = max(1, (int) $devices->sum('visitors'));
        @endphp
        @foreach($deviceConfig as $deviceKey => [$deviceLabel, $deviceIcon])
            @php $device = $devices->get($deviceKey); $visitors = (int) ($device->visitors ?? 0); $percent = round(($visitors / $deviceVisitorsTotal) * 100); @endphp
            <div class="col-12 col-md-4"><article class="device-stat"><div class="device-stat__icon"><i class="fa-solid {{ $deviceIcon }}"></i></div><div class="device-stat__copy"><span>{{ $deviceLabel }}</span><strong>{{ number_format($visitors, 0, ',', '.') }}</strong><small>{{ $percent }}% · {{ number_format((int) ($device->views ?? 0), 0, ',', '.') }} views</small></div><div class="device-stat__bar"><span style="width: {{ $percent }}%"></span></div></article></div>
        @endforeach
    </div>
    <div class="screen-size-strip mt-3"><div><strong>Tamanhos de tela mais usados</strong><span>Ajuda a validar a experiência responsiva da loja.</span></div><div class="screen-size-list">@forelse($screenSizes as $screen)<span><i class="fa-regular fa-window-maximize"></i>{{ $screen->viewport_width }} × {{ $screen->viewport_height }} <small>{{ $screen->visitors }}</small></span>@empty<span class="screen-size-empty">Ainda não há dimensões suficientes registradas.</span>@endforelse</div></div>
</section>

<section class="dashboard-details-gate table-card" data-dashboard-insights data-url="{{ route('admin.dashboard.insights', ['days' => $days]) }}" data-loading-label="Carregando análise..." data-error-label="Não foi possível carregar a análise.">
    <div class="dashboard-details-gate__intro"><div class="dashboard-details-gate__icon"><i class="fa-solid fa-chart-line"></i></div><div><div class="card-kicker">Carregamento sob demanda</div><h2 class="card-heading">Análise detalhada</h2><p>Gráficos e rankings só são consultados quando você clicar, evitando lentidão nesta página.</p></div><button class="btn btn-dark dashboard-load-insights" type="button" aria-expanded="false"><i class="fa-solid fa-chart-simple me-2"></i><span>Abrir análise</span></button></div>
    <div class="dashboard-insights-content" hidden></div>
</section>
@endsection
