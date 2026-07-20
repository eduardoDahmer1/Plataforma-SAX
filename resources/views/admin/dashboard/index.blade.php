@extends('layout.admin')

@section('title', 'Visão geral | Painel SAX')

@push('styles')
<style>
    .overview-hero{background:linear-gradient(135deg,#101828 0%,#27364f 65%,#b39154 160%);border-radius:22px;padding:28px;color:#fff;overflow:hidden;position:relative}.overview-hero:after{content:"";position:absolute;width:240px;height:240px;border:45px solid rgba(255,255,255,.05);border-radius:50%;right:-70px;top:-100px}.overview-eyebrow{font-size:.72rem;letter-spacing:.18em;text-transform:uppercase;color:#e4c98e}.overview-title{font-size:clamp(1.65rem,3vw,2.5rem);font-weight:800;margin:.35rem 0}.overview-subtitle{color:#d0d5dd;margin:0;max-width:680px}.overview-date{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);border-radius:12px;padding:9px 13px;font-size:.8rem;white-space:nowrap}.metric-card,.chart-card,.table-card{background:#fff;border:1px solid #eaecf0;border-radius:18px;box-shadow:0 8px 30px rgba(16,24,40,.05)}.metric-card{padding:18px;height:100%;transition:.2s ease}.metric-card:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(16,24,40,.09)}.metric-icon{width:43px;height:43px;border-radius:13px;display:grid;place-items:center;font-size:1.05rem}.metric-label{font-size:.76rem;color:#667085;text-transform:uppercase;letter-spacing:.05em;margin-top:13px}.metric-value{font-size:1.75rem;font-weight:800;color:#101828;line-height:1.1;margin-top:5px}.metric-note{font-size:.73rem;color:#98a2b3;margin-top:5px}.bg-purple{background:#f4ebff;color:#7f56d9}.bg-blue{background:#eaf2ff;color:#2970ff}.bg-green{background:#eafbf2;color:#039855}.bg-gold{background:#fff7e6;color:#b7791f}.bg-red{background:#fff0f0;color:#d92d20}.bg-cyan{background:#e8f8fb;color:#088ab2}.chart-card,.table-card{padding:20px;height:100%}.card-heading{font-size:.95rem;font-weight:750;color:#101828;margin:0}.card-kicker{font-size:.72rem;color:#98a2b3;margin-top:3px}.chart-wrap{position:relative;height:300px;margin-top:16px}.chart-wrap.small{height:245px}.overview-table{margin:14px 0 0;font-size:.82rem}.overview-table th{font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:#98a2b3;border-bottom-color:#eaecf0}.overview-table td{vertical-align:middle;border-bottom-color:#f2f4f7}.path-cell{max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.rank{width:27px;height:27px;border-radius:8px;background:#f2f4f7;display:grid;place-items:center;font-weight:700;font-size:.7rem}.analytics-empty{background:#fffaeb;border:1px solid #fedf89;color:#93370d;border-radius:14px;padding:14px;font-size:.82rem}.section-title{font-size:1rem;font-weight:800;color:#344054;margin:4px 0 14px}.badge-soft{background:#f2f4f7;color:#344054;border-radius:20px;padding:5px 9px;font-size:.68rem;font-weight:700}@media(max-width:767px){.overview-hero{padding:22px}.overview-date{margin-top:15px}.chart-wrap{height:260px}.metric-value{font-size:1.45rem}}
</style>
<style>
    .sax-admin-content,.overview-hero,.metric-card,.chart-card,.table-card{min-width:0;max-width:100%}
    .chart-card,.table-card{height:auto!important;min-height:0;overflow:hidden}
    .chart-wrap{width:100%;max-width:100%;overflow:hidden}
    .chart-wrap canvas{display:block!important;max-width:100%!important;max-height:100%!important}
    .table-responsive{width:100%;max-width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch}
    .overview-table{width:100%;min-width:520px}
    .overview-table th,.badge-soft{white-space:nowrap}
    @media(max-width:991.98px){.overview-hero{padding:24px}.chart-wrap{height:280px}.chart-wrap.small{height:250px}}
    @media(max-width:575.98px){.overview-hero{padding:20px;border-radius:16px}.overview-hero:after{right:-130px}.overview-title{font-size:1.55rem}.overview-subtitle{font-size:.84rem}.overview-date{display:inline-block;margin-top:15px;white-space:normal}.metric-card{padding:15px}.metric-value{font-size:1.45rem}.metric-label{font-size:.68rem}.metric-note{font-size:.68rem}.chart-card,.table-card{padding:16px;border-radius:14px}.chart-wrap,.chart-wrap.small{height:235px}.overview-table{font-size:.76rem}.section-title{font-size:.92rem}}
</style>
@endpush

@section('content')
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

<div class="table-card mb-4"><div class="d-flex justify-content-between align-items-center"><div><h3 class="card-heading">Pedidos recentes</h3><div class="card-kicker">Últimas movimentações da loja</div></div><a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-dark">Ver todos</a></div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>Pedido</th><th>Cliente</th><th>Pagamento</th><th>Status</th><th>Total</th><th>Data</th></tr></thead><tbody>@forelse($recentOrders as $order)<tr><td><a href="{{ route('admin.orders.show',$order) }}" class="fw-bold text-dark">#{{ $order->order_number ?: $order->id }}</a></td><td>{{ $order->user?->name ?: $order->name ?: 'Visitante' }}</td><td>{{ ['bancard'=>'Bancard','bancard_v2'=>'Bancard V2','deposito'=>'Depósito','whatsapp'=>'WhatsApp'][$order->payment_method] ?? ucfirst($order->payment_method) }}</td><td><span class="badge-soft">{{ ucfirst($order->status) }}</span></td><td>{{ $order->currency_sign ?: 'US$' }} {{ number_format($order->total,2,',','.') }}</td><td>{{ $order->created_at?->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">Nenhum pedido encontrado.</td></tr>@endforelse</tbody></table></div></div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;
    Chart.defaults.font.family = "Montserrat, Arial, sans-serif";
    Chart.defaults.color = '#667085';
    const grid = {color:'rgba(16,24,40,.06)'};
    new Chart(document.getElementById('trafficChart'), {type:'line',data:{labels:@json($trafficLabels),datasets:[{label:'Páginas vistas',data:@json($trafficViews),borderColor:'#2970ff',backgroundColor:'rgba(41,112,255,.1)',fill:true,tension:.35,pointRadius:2},{label:'Visitantes únicos',data:@json($trafficVisitors),borderColor:'#b39154',backgroundColor:'transparent',tension:.35,pointRadius:2}]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{precision:0},grid:grid},x:{grid:{display:false}}}}});
    const doughnut = (id, labels, data, colors) => new Chart(document.getElementById(id),{type:'doughnut',data:{labels:labels,datasets:[{data:data,backgroundColor:colors,borderWidth:0,hoverOffset:5}]},options:{responsive:true,maintainAspectRatio:false,cutout:'68%',plugins:{legend:{position:'bottom',labels:{usePointStyle:true,boxWidth:8}}}}});
    doughnut('paymentsChart', @json($paymentMethods->keys()), @json($paymentMethods->values()), ['#2970ff','#b39154','#12b76a','#98a2b3']);
    doughnut('ordersChart', @json($orderStatuses->keys()), @json($orderStatuses->values()), ['#12b76a','#f79009','#2970ff','#d92d20','#7f56d9','#98a2b3']);
    const deviceLabels = @json($devices->keys()->map(fn($key) => ['desktop'=>'Desktop','tablet'=>'Tablet','mobile'=>'Celular'][$key] ?? ucfirst($key))->values());
    doughnut('devicesChart', deviceLabels, @json($devices->values()), ['#101828','#b39154','#2970ff']);
});
</script>
@endpush
