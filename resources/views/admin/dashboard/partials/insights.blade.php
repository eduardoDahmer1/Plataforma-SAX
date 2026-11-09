<div class="dashboard-insights-heading">
    <div><div class="card-kicker">Dados carregados</div><h2 class="card-heading">Análise detalhada de tráfego</h2></div>
    <button class="btn btn-sm btn-outline-dark dashboard-close-insights" type="button"><i class="fa-solid fa-chevron-up me-1"></i> Minimizar</button>
</div>

<div class="dashboard-chart-data" hidden
     data-traffic-labels='@json($trafficLabels)'
     data-traffic-views='@json($trafficViews)'
     data-traffic-visitors='@json($trafficVisitors)'
     data-traffic-views-label="Páginas vistas"
     data-traffic-visitors-label="Visitantes únicos"></div>

<div class="chart-card dashboard-nested-card mt-3">
    <h3 class="card-heading">Tráfego nos últimos {{ $days }} dias</h3>
    <div class="card-kicker">Visualizações e visitantes únicos por dia</div>
    <div class="chart-wrap"><canvas data-chart="traffic"></canvas></div>
</div>

<div class="row g-3 mt-1">
    <div class="col-xl-6"><div class="table-card dashboard-nested-card"><h3 class="card-heading">Páginas mais visitadas</h3><div class="card-kicker">Últimos {{ $days }} dias</div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>#</th><th>Página</th><th>Views</th><th>Pessoas</th></tr></thead><tbody>@forelse($topPages as $page)<tr><td><span class="rank">{{ $loop->iteration }}</span></td><td class="path-cell" title="{{ $page->path }}">{{ $page->path }}</td><td><b>{{ number_format($page->total,0,',','.') }}</b></td><td>{{ number_format($page->visitors,0,',','.') }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">Nenhum acesso registrado.</td></tr>@endforelse</tbody></table></div></div></div>
    <div class="col-xl-6"><div class="table-card dashboard-nested-card"><h3 class="card-heading">Elementos mais clicados</h3><div class="card-kicker">Interações registradas no período</div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>#</th><th>Elemento</th><th>Página</th><th>Cliques</th></tr></thead><tbody>@forelse($topClicks as $click)<tr><td><span class="rank">{{ $loop->iteration }}</span></td><td class="path-cell" title="{{ $click->target }}">{{ $click->element_text ?: $click->target ?: 'Sem texto' }}</td><td class="path-cell" title="{{ $click->path }}">{{ $click->path }}</td><td><b>{{ number_format($click->total,0,',','.') }}</b></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">Nenhum clique registrado.</td></tr>@endforelse</tbody></table></div></div></div>
</div>
