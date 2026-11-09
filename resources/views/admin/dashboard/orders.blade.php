@extends('layout.admin')

@section('title', 'Painel de pedidos')

@section('content')
@include('admin.dashboard.partials.page-header', [
    'eyebrow' => 'Vendas', 'title' => 'Pedidos', 'icon' => 'fa-bag-shopping',
    'description' => 'Situação dos pedidos e pagamentos sem carregar as demais áreas do painel.',
    'actionRoute' => route('admin.orders.index'), 'actionLabel' => 'Ver todos os pedidos',
])

<section class="table-card dashboard-filter-card mb-4">
    <form method="GET" class="dashboard-filter-grid">
        <div><label for="orders-period">Período</label><select id="orders-period" name="period" class="form-select"><option value="7" @selected($filters['period'] === '7')>Últimos 7 dias</option><option value="30" @selected($filters['period'] === '30')>Últimos 30 dias</option><option value="90" @selected($filters['period'] === '90')>Últimos 90 dias</option><option value="all" @selected($filters['period'] === 'all')>Todo o histórico</option></select></div>
        <div><label for="orders-payment">Situação do pagamento</label><select id="orders-payment" name="payment_status" class="form-select"><option value="all">Todos</option><option value="pending" @selected($filters['payment_status'] === 'pending')>Aguardando</option><option value="paid" @selected($filters['payment_status'] === 'paid')>Pago</option><option value="failed" @selected($filters['payment_status'] === 'failed')>Falhou</option><option value="refunded" @selected($filters['payment_status'] === 'refunded')>Reembolsado</option></select></div>
        <div><label for="orders-status">Etapa do pedido</label><select id="orders-status" name="status" class="form-select"><option value="all">Todas</option>@foreach(['pending'=>'Pendente','processing'=>'Em preparação','paid'=>'Pago','shipped'=>'Enviado','completed'=>'Concluído','canceled'=>'Cancelado','failed'=>'Falhou'] as $value => $label)<option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>@endforeach</select></div>
        <div><label for="orders-method">Forma de pagamento</label><select id="orders-method" name="payment_method" class="form-select"><option value="all">Todas</option><option value="bancard_v2" @selected($filters['payment_method'] === 'bancard_v2')>Bancard</option><option value="rendix_pix" @selected($filters['payment_method'] === 'rendix_pix')>Rendix Pix</option><option value="deposito" @selected($filters['payment_method'] === 'deposito')>Depósito / transferência</option></select></div>
        <div class="dashboard-filter-actions"><button class="btn btn-dark" type="submit"><i class="fa-solid fa-filter me-1"></i> Aplicar</button><a class="btn btn-outline-secondary" href="{{ route('admin.dashboard.orders') }}">Limpar</a></div>
    </form>
</section>

@php $paidRate = (int) ($summary->total ?? 0) > 0 ? round(((int) ($summary->paid ?? 0) / (int) $summary->total) * 100) : 0; @endphp
<div class="dashboard-reading-guide mb-4"><i class="fa-solid fa-circle-info"></i><div><strong>Como ler esta página</strong><span>Os números abaixo consideram {{ mb_strtolower($periodLabel) }} e todos os filtros aplicados. {{ $paidRate }}% dos pedidos deste resultado estão confirmados como pagos.</span></div></div>

<div class="row g-3 mb-4">
    @foreach ([
        ['Todos os pedidos', $summary->total ?? 0, 'fa-receipt', 'bg-blue'],
        ['Pagamentos confirmados', $summary->paid ?? 0, 'fa-circle-check', 'bg-green'],
        ['Aguardando pagamento', $summary->pending_payment ?? 0, 'fa-clock', 'bg-gold'],
        ['Em preparação ou envio', $summary->in_progress ?? 0, 'fa-truck-fast', 'bg-purple'],
        ['Cancelados ou falhos', $summary->canceled ?? 0, 'fa-circle-xmark', 'bg-red'],
    ] as [$label, $value, $icon, $tone])
        <div class="col-12 col-sm-6 col-xl"><div class="metric-card"><div class="metric-icon {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></div><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ number_format((int) $value, 0, ',', '.') }}</div><div class="metric-note">Dentro do resultado filtrado</div></div></div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6"><section class="table-card"><h2 class="card-heading">Formas de pagamento</h2><div class="dashboard-breakdown-list mt-3">@forelse($paymentMethods as $method => $total)<div><span>{{ ['bancard_v2' => 'Bancard', 'rendix_pix' => 'Rendix Pix', 'deposito' => 'Depósito / transferência'][$method] ?? ucfirst(str_replace('_', ' ', $method)) }}</span><strong>{{ number_format($total, 0, ',', '.') }}</strong></div>@empty<p class="text-muted small mb-0">Nenhum pedido registrado.</p>@endforelse</div></section></div>
    <div class="col-lg-6"><section class="table-card"><h2 class="card-heading">Status dos pedidos</h2><div class="dashboard-breakdown-list mt-3">@forelse($orderStatuses as $status => $total)<div><span>{{ __('messages.status_' . $status) }}</span><strong>{{ number_format($total, 0, ',', '.') }}</strong></div>@empty<p class="text-muted small mb-0">Nenhum status registrado.</p>@endforelse</div></section></div>
</div>

<section class="table-card">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3"><div><div class="card-kicker">Resultado filtrado · até 20 registros</div><h2 class="card-heading">Pedidos mais recentes</h2></div><span class="dashboard-live-badge"><i class="fa-solid fa-bolt"></i> Atualiza em até 45 segundos</span></div>
    <div class="table-responsive"><table class="table overview-table"><thead><tr><th>Pedido</th><th>Cliente</th><th>Pagamento</th><th>Status</th><th>Total</th><th>Data</th></tr></thead><tbody>
        @forelse($recentOrders as $order)<tr><td><a href="{{ route('admin.orders.show', $order) }}" class="fw-bold text-dark">#{{ $order->order_number ?: $order->id }}</a></td><td>{{ $order->user?->name ?: $order->name ?: 'Visitante' }}</td><td><span class="badge-soft">{{ __('messages.payment_status_' . $order->payment_status) }}</span></td><td>{{ __('messages.status_' . $order->status) }}</td><td>{{ order_money($order, $order->total) }}</td><td>{{ $order->created_at?->format('d/m/Y H:i') }}</td></tr>
        @empty<tr><td colspan="6" class="text-center text-muted py-4">Nenhum pedido encontrado.</td></tr>@endforelse
    </tbody></table></div>
</section>
@endsection
