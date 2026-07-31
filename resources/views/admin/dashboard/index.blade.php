@extends('layout.admin')

@section('title', 'Visão geral | Painel SAX')

@section('content')
<div id="dashboard-chart-data" hidden data-traffic-labels="{{ json_encode($trafficLabels) }}" data-traffic-views="{{ json_encode($trafficViews) }}" data-traffic-visitors="{{ json_encode($trafficVisitors) }}" data-payment-labels="{{ json_encode($paymentMethods->keys()->values()) }}" data-payment-values="{{ json_encode($paymentMethods->values()) }}" data-order-labels="{{ json_encode($orderStatuses->keys()->values()) }}" data-order-values="{{ json_encode($orderStatuses->values()) }}" data-device-labels="{{ json_encode($devices->keys()->map(fn($key) => ['desktop'=>'Desktop','tablet'=>'Tablet','mobile'=>'Celular'][$key] ?? ucfirst($key))->values()) }}" data-device-values="{{ json_encode($devices->values()) }}"></div>
<section class="overview-hero mb-4">
    <div class="d-md-flex justify-content-between align-items-center position-relative" style="z-index:1">
        <div>
            <div class="overview-eyebrow">Inteligência do negócio</div>
            <h1 class="overview-title">Visão geral da SAX</h1>
            <p class="overview-subtitle">Catálogo, clientes, pedidos e comportamento do público reunidos em um único painel.</p>
        </div>
        <div class="overview-date"><i class="fa-regular fa-calendar me-2"></i>{{ now()->translatedFormat('d \d\e F \d\e Y') }}</div>
    </div>
</section>

<div class="d-flex flex-wrap align-items-center gap-2 mb-4">
    <strong class="me-2">Baixar relatório:</strong>
    <a class="btn btn-sm btn-outline-dark" href="{{ route('admin.reports.download', 'today') }}"><i class="fa-regular fa-file-pdf me-1"></i>Hoje</a>
    <a class="btn btn-sm btn-outline-dark" href="{{ route('admin.reports.download', 'week') }}"><i class="fa-regular fa-file-pdf me-1"></i>7 dias</a>
    <a class="btn btn-sm btn-dark" href="{{ route('admin.reports.download', 'month') }}"><i class="fa-regular fa-file-pdf me-1"></i>Mês</a>
</div>

@php
    $integrationStatus = $integrationMonitor?->status ?? 'never_reported';
    $integrationPresentation = [
        'healthy' => ['Operando normalmente', 'success', 'fa-circle-check'],
        'running' => ['Sincronização em andamento', 'warning', 'fa-arrows-rotate'],
        'failed' => ['Falha na integração', 'danger', 'fa-triangle-exclamation'],
        'stale' => ['Integrador sem comunicação', 'danger', 'fa-plug-circle-xmark'],
        'never_reported' => ['Monitor aguardando o integrador', 'secondary', 'fa-clock'],
    ][$integrationStatus] ?? ['Estado desconhecido', 'secondary', 'fa-circle-question'];
@endphp

<section id="integration-monitor" class="table-card mb-4 border border-{{ $integrationPresentation[1] }}">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-start">
        <div>
            <div class="card-kicker">Monitoramento automático</div>
            <h2 class="card-heading mb-2">
                <i class="fa-solid {{ $integrationPresentation[2] }} text-{{ $integrationPresentation[1] }} me-2"></i>
                Integração de produtos
            </h2>
            <span class="badge text-bg-{{ $integrationPresentation[1] }}">{{ $integrationPresentation[0] }}</span>
        </div>
        <div class="small text-muted text-lg-end">
            <div><strong>Última comunicação:</strong> {{ $integrationMonitor?->last_heartbeat_at?->format('d/m/Y H:i:s') ?? 'Ainda não recebida' }}</div>
            <div><strong>Último sucesso:</strong> {{ $integrationMonitor?->last_success_at?->format('d/m/Y H:i:s') ?? 'Ainda não registrado' }}</div>
            <div><strong>Falhas consecutivas:</strong> {{ (int) ($integrationMonitor?->consecutive_failures ?? 0) }}</div>
        </div>
    </div>

    @if($integrationMonitor?->error_message)
        <div class="alert alert-{{ in_array($integrationStatus, ['failed', 'stale'], true) ? 'danger' : 'warning' }} mt-3 mb-3">
            <strong>{{ $integrationMonitor->error_code ?: 'Erro da integração' }}:</strong>
            {{ $integrationMonitor->error_message }}
        </div>
    @elseif(!$integrationMonitor)
        <div class="alert alert-secondary mt-3 mb-3">
            O banco de monitoramento ainda não foi inicializado. Execute as migrations e configure o integrador.
        </div>
    @endif

    <div class="table-responsive mt-3">
        <table class="table overview-table mb-0">
            <thead><tr><th>Início</th><th>Fim</th><th>Resultado</th><th>Duração</th><th>Detalhe</th></tr></thead>
            <tbody>
            @forelse($integrationRuns as $run)
                @php
                    $runStyle = ['success' => 'success', 'failed' => 'danger', 'running' => 'warning'][$run->status] ?? 'secondary';
                    $runLabel = ['success' => 'Concluída', 'failed' => 'Falhou', 'running' => 'Executando'][$run->status] ?? ucfirst($run->status);
                @endphp
                <tr>
                    <td>{{ $run->started_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                    <td>{{ $run->finished_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                    <td><span class="badge text-bg-{{ $runStyle }}">{{ $runLabel }}</span></td>
                    <td>{{ $run->duration_seconds !== null ? gmdate('H:i:s', $run->duration_seconds) : '—' }}</td>
                    <td class="text-break">{{ $run->error_message ?: 'Sem erros registrados' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Nenhuma execução comunicada pelo novo monitor.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

@if(!$analyticsReady)
    <div class="analytics-empty mb-4"><i class="fa-solid fa-circle-info me-2"></i>Os indicadores de audiência começarão a ser registrados assim que a migration de analytics for executada.</div>
@endif

<h2 class="section-title">Audiência hoje</h2>
<div class="row g-3 mb-4">
    @php
        $audienceCards = [
            ['Visitas únicas', $analytics['visitors_today'], 'fa-users-viewfinder', 'bg-purple', 'Pessoas diferentes hoje'],
            ['Páginas vistas', $analytics['views_today'], 'fa-eye', 'bg-blue', 'Visualizações registradas hoje'],
            ['Cliques', $analytics['clicks_today'], 'fa-arrow-pointer', 'bg-green', 'Interações registradas hoje'],
            ['Views em 30 dias', $analytics['views_30_days'], 'fa-chart-column', 'bg-gold', 'Tráfego acumulado no período'],
        ];
    @endphp
    @foreach($audienceCards as [$label,$value,$icon,$color,$note])
        <div class="col-12 col-sm-6 col-xl-3"><div class="metric-card"><div class="metric-icon {{ $color }}"><i class="fa-solid {{ $icon }}"></i></div><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ number_format($value,0,',','.') }}</div><div class="metric-note">{{ $note }}</div></div></div>
    @endforeach
</div>

<h2 class="section-title">Operação da loja</h2>
<div class="row g-3 mb-4">
    @php
        $businessCards = [
            ['Produtos ativos', $metrics['active_products'], 'fa-box-open', 'bg-green', $metrics['products'].' produtos no total'],
            ['Marcas', $metrics['brands'], 'fa-copyright', 'bg-purple', 'Cadastradas no catálogo'],
            ['Categorias', $metrics['categories'], 'fa-tags', 'bg-blue', 'Categorias principais'],
            ['Subcategorias', $metrics['subcategories'], 'fa-tag', 'bg-cyan', 'Níveis intermediários'],
            ['Categorias filhas', $metrics['childcategories'], 'fa-sitemap', 'bg-gold', 'Último nível do catálogo'],
            ['Blogs publicados', $metrics['published_blogs'], 'fa-newspaper', 'bg-purple', 'Ativos e já publicados'],
            ['Clientes', $metrics['customers'], 'fa-user-group', 'bg-blue', 'Administradores excluídos'],
            ['Pedidos', $metrics['orders'], 'fa-receipt', 'bg-green', 'Todos os meios e status'],
            ['Estoque baixo', $metrics['low_stock'], 'fa-triangle-exclamation', 'bg-gold', 'Entre 1 e 5 unidades'],
            ['Sem estoque', $metrics['out_of_stock'], 'fa-ban', 'bg-red', 'Produtos ativos zerados'],
            ['Carrinhos abandonados', $metrics['abandoned_carts'], 'fa-cart-arrow-down', 'bg-red', 'Aguardando recuperação'],
            ['Contatos recebidos', $metrics['contacts'], 'fa-envelope', 'bg-cyan', 'Registros do formulário'],
        ];
    @endphp
    @foreach($businessCards as [$label,$value,$icon,$color,$note])
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3"><div class="metric-card"><div class="metric-icon {{ $color }}"><i class="fa-solid {{ $icon }}"></i></div><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ number_format($value,0,',','.') }}</div><div class="metric-note">{{ $note }}</div></div></div>
    @endforeach
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8"><div class="chart-card"><h3 class="card-heading">Tráfego dos últimos 30 dias</h3><div class="card-kicker">Visualizações e visitantes únicos por dia</div><div class="chart-wrap"><canvas id="trafficChart"></canvas></div></div></div>
    <div class="col-xl-4"><div class="chart-card"><h3 class="card-heading">Pedidos por pagamento</h3><div class="card-kicker">Bancard, depósito, WhatsApp e outros</div><div class="chart-wrap small"><canvas id="paymentsChart"></canvas></div><div class="d-flex justify-content-around text-center mt-2"><div><b>{{ $metrics['bancard_orders'] }}</b><small class="d-block text-muted">Bancard</small></div><div><b>{{ $metrics['deposit_orders'] }}</b><small class="d-block text-muted">Depósito</small></div><div><b>{{ $metrics['whatsapp_orders'] }}</b><small class="d-block text-muted">WhatsApp</small></div></div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-6"><div class="table-card"><h3 class="card-heading">Páginas mais acessadas</h3><div class="card-kicker">Últimos 30 dias</div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>#</th><th>Página</th><th>Views</th><th>Pessoas</th></tr></thead><tbody>@forelse($topPages as $page)<tr><td><span class="rank">{{ $loop->iteration }}</span></td><td class="path-cell" title="{{ $page->path }}">{{ $page->path }}</td><td><b>{{ number_format($page->total,0,',','.') }}</b></td><td>{{ number_format($page->visitors,0,',','.') }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">Ainda não há acessos registrados.</td></tr>@endforelse</tbody></table></div></div></div>
    <div class="col-xl-6"><div class="table-card"><h3 class="card-heading">Cliques mais frequentes</h3><div class="card-kicker">Elemento e página de origem nos últimos 30 dias</div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>#</th><th>Elemento</th><th>Página</th><th>Cliques</th></tr></thead><tbody>@forelse($topClicks as $click)<tr><td><span class="rank">{{ $loop->iteration }}</span></td><td class="path-cell" title="{{ $click->target }}">{{ $click->element_text ?: $click->target ?: 'Elemento sem texto' }}</td><td class="path-cell" title="{{ $click->path }}">{{ $click->path }}</td><td><b>{{ number_format($click->total,0,',','.') }}</b></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">Ainda não há cliques registrados.</td></tr>@endforelse</tbody></table></div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-4"><div class="chart-card"><h3 class="card-heading">Status dos pedidos</h3><div class="card-kicker">Distribuição geral</div><div class="chart-wrap small"><canvas id="ordersChart"></canvas></div></div></div>
    <div class="col-xl-4"><div class="chart-card"><h3 class="card-heading">Dispositivos</h3><div class="card-kicker">Acessos nos últimos 30 dias</div><div class="chart-wrap small"><canvas id="devicesChart"></canvas></div></div></div>
    <div class="col-xl-4"><div class="table-card"><h3 class="card-heading">Produtos mais vistos</h3><div class="card-kicker">Ranking histórico do catálogo</div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>#</th><th>Produto</th><th>Views</th><th>Estoque</th></tr></thead><tbody>@forelse($topProducts as $product)<tr><td><span class="rank">{{ $loop->iteration }}</span></td><td class="path-cell">{{ $product->name ?: $product->external_name ?: '#'.$product->id }}</td><td><b>{{ number_format($product->views ?? 0,0,',','.') }}</b></td><td><span class="badge-soft">{{ $product->stock ?? 0 }}</span></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">Nenhum produto encontrado.</td></tr>@endforelse</tbody></table></div></div></div>
</div>

<div class="table-card mb-4"><div class="d-flex justify-content-between align-items-center"><div><h3 class="card-heading">Pedidos recentes</h3><div class="card-kicker">Últimas movimentações da loja</div></div><a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-dark">Ver todos</a></div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>Pedido</th><th>Cliente</th><th>Pagamento</th><th>Status</th><th>Total</th><th>Data</th></tr></thead><tbody>@forelse($recentOrders as $order)<tr><td><a href="{{ route('admin.orders.show',$order) }}" class="fw-bold text-dark">#{{ $order->order_number ?: $order->id }}</a></td><td>{{ $order->user?->name ?: $order->name ?: 'Visitante' }}</td><td>{{ ['bancard_v2'=>'Bancard V2','rendix_pix'=>'Pix Rendix','deposito'=>'Depósito','whatsapp'=>'WhatsApp'][$order->payment_method] ?? ucfirst($order->payment_method) }}</td><td><span class="badge-soft">{{ ucfirst($order->status) }}</span></td><td>{{ $order->currency_sign ?: 'US$' }} {{ number_format($order->total,2,',','.') }}</td><td>{{ $order->created_at?->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">Nenhum pedido encontrado.</td></tr>@endforelse</tbody></table></div></div>

<div class="table-card mb-4"><h3 class="card-heading">Ocorrências recentes</h3><div class="card-kicker">Resumo simples de pagamentos, checkout, carrinhos e e-mails</div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>Quando</th><th>Cliente</th><th>Ocorrência</th><th>Explicação</th><th>Referência</th></tr></thead><tbody>@forelse($businessEvents as $event)<tr><td>{{ $event->created_at->format('d/m H:i') }}</td><td>{{ $event->user?->name ?: 'Não identificado' }}</td><td><span class="badge-soft">{{ $event->title }}</span></td><td>{{ $event->message ?: 'Sem detalhes adicionais' }}</td><td>@if($event->order)<a href="{{ route('admin.orders.show',$event->order) }}">#{{ $event->order->order_number ?: $event->order_id }}</a>@else{{ $event->reference ?: '—' }}@endif</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">Nenhuma ocorrência registrada após a ativação do monitoramento.</td></tr>@endforelse</tbody></table></div></div>
@endsection
