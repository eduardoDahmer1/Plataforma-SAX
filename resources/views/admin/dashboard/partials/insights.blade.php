@php
    $paymentLabels = $paymentMethods->keys()->map(fn ($method) => [
        'bancard_v2' => 'Bancard V2',
        'rendix_pix' => 'Pix Rendix',
        'deposito' => __('messages.payment_deposit'),
        'whatsapp' => 'WhatsApp',
    ][$method] ?? __('messages.payment_other'))->values();
    $orderLabels = $orderStatuses->keys()->map(fn ($key) => [
        'pending' => __('messages.status_pending'),
        'paid' => __('messages.status_paid'),
        'processing' => __('messages.status_processing'),
        'shipped' => __('messages.status_shipped'),
        'completed' => __('messages.status_completed'),
        'canceled' => __('messages.status_canceled'),
        'failed' => __('messages.status_failed'),
    ][$key] ?? ucfirst($key))->values();
@endphp
<div class="dashboard-insights-heading">
    <div>
        <div class="card-kicker">{{ __('messages.dashboard_data_loaded') }}</div>
        <h2 class="card-heading">{{ __('messages.dashboard_detailed_analysis') }}</h2>
    </div>
    <button class="btn btn-sm btn-outline-dark dashboard-close-insights" type="button">
        <i class="fa-solid fa-chevron-up me-1"></i>{{ __('messages.dashboard_minimize_analysis') }}
    </button>
</div>

<div class="dashboard-chart-data" hidden
     data-traffic-labels='@json($trafficLabels)'
     data-traffic-views='@json($trafficViews)'
     data-traffic-visitors='@json($trafficVisitors)'
     data-traffic-views-label="{{ __('messages.metric_page_views') }}"
     data-traffic-visitors-label="{{ __('messages.metric_unique_visits') }}"
     data-payment-labels='@json($paymentLabels)'
     data-payment-values='@json($paymentMethods->values())'
     data-order-labels='@json($orderLabels)'
     data-order-values='@json($orderStatuses->values())'></div>

<div class="row g-3 mt-1">
    <div class="col-xl-8"><div class="chart-card"><h3 class="card-heading">{{ __('messages.dashboard_traffic_30_days') }}</h3><div class="card-kicker">{{ __('messages.dashboard_traffic_note') }}</div><div class="chart-wrap"><canvas data-chart="traffic"></canvas></div></div></div>
    <div class="col-xl-4"><div class="chart-card"><h3 class="card-heading">{{ __('messages.dashboard_orders_by_payment') }}</h3><div class="card-kicker">{{ __('messages.dashboard_payments_note') }}</div><div class="chart-wrap small"><canvas data-chart="payments"></canvas></div></div></div>
</div>

<div class="row g-3 mt-1">
    <div class="col-xl-6"><div class="table-card dashboard-nested-card"><h3 class="card-heading">{{ __('messages.dashboard_top_pages') }}</h3><div class="card-kicker">{{ __('messages.period_last_30_days') }}</div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>#</th><th>{{ __('messages.table_page') }}</th><th>Views</th><th>{{ __('messages.table_people') }}</th></tr></thead><tbody>@forelse($topPages as $page)<tr><td><span class="rank">{{ $loop->iteration }}</span></td><td class="path-cell" title="{{ $page->path }}">{{ $page->path }}</td><td><b>{{ number_format($page->total,0,',','.') }}</b></td><td>{{ number_format($page->visitors,0,',','.') }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">{{ __('messages.dashboard_no_accesses') }}</td></tr>@endforelse</tbody></table></div></div></div>
    <div class="col-xl-6"><div class="table-card dashboard-nested-card"><h3 class="card-heading">{{ __('messages.dashboard_top_clicks') }}</h3><div class="card-kicker">{{ __('messages.dashboard_top_clicks_note') }}</div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>#</th><th>{{ __('messages.table_element') }}</th><th>{{ __('messages.table_page') }}</th><th>{{ __('messages.metric_clicks') }}</th></tr></thead><tbody>@forelse($topClicks as $click)<tr><td><span class="rank">{{ $loop->iteration }}</span></td><td class="path-cell" title="{{ $click->target }}">{{ $click->element_text ?: $click->target ?: __('messages.element_without_text') }}</td><td class="path-cell" title="{{ $click->path }}">{{ $click->path }}</td><td><b>{{ number_format($click->total,0,',','.') }}</b></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">{{ __('messages.dashboard_no_clicks') }}</td></tr>@endforelse</tbody></table></div></div></div>
</div>

<div class="row g-3 mt-1">
    <div class="col-xl-4"><div class="chart-card"><h3 class="card-heading">{{ __('messages.dashboard_order_statuses') }}</h3><div class="card-kicker">{{ __('messages.dashboard_general_distribution') }}</div><div class="chart-wrap small"><canvas data-chart="orders"></canvas></div></div></div>
    <div class="col-xl-8"><div class="table-card dashboard-nested-card"><h3 class="card-heading">{{ __('messages.dashboard_most_viewed_products') }}</h3><div class="card-kicker">{{ __('messages.dashboard_product_ranking_note') }}</div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>#</th><th>{{ __('messages.table_product') }}</th><th>Views</th><th>{{ __('messages.table_stock') }}</th></tr></thead><tbody>@forelse($topProducts as $product)<tr><td><span class="rank">{{ $loop->iteration }}</span></td><td class="path-cell">{{ $product->name ?: $product->external_name ?: '#'.$product->id }}</td><td><b>{{ number_format($product->views ?? 0,0,',','.') }}</b></td><td><span class="badge-soft">{{ $product->stock ?? 0 }}</span></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">{{ __('messages.no_products_found') }}</td></tr>@endforelse</tbody></table></div></div></div>
</div>

<details class="dashboard-collapsible table-card dashboard-nested-card mt-3">
    <summary><div><h3 class="card-heading">{{ __('messages.dashboard_recent_orders') }}</h3><div class="card-kicker">{{ __('messages.dashboard_latest_store_activity') }}</div></div></summary>
    <div class="dashboard-collapsible__body table-responsive"><table class="table overview-table"><thead><tr><th>{{ __('messages.table_order') }}</th><th>{{ __('messages.table_customer') }}</th><th>{{ __('messages.table_status') }}</th><th>{{ __('messages.total') }}</th><th>{{ __('messages.table_date') }}</th></tr></thead><tbody>@forelse($recentOrders as $order)<tr><td><a href="{{ route('admin.orders.show',$order) }}" class="fw-bold text-dark">#{{ $order->order_number ?: $order->id }}</a></td><td>{{ $order->user?->name ?: $order->name ?: __('messages.guest') }}</td><td><span class="badge-soft">{{ ucfirst($order->status) }}</span></td><td>{{ $order->currency_sign ?: 'US$' }} {{ number_format($order->total,2,',','.') }}</td><td>{{ $order->created_at?->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">{{ __('messages.no_orders_found') }}</td></tr>@endforelse</tbody></table></div>
</details>

<details class="dashboard-collapsible table-card dashboard-nested-card mt-3">
    <summary><div><h3 class="card-heading">{{ __('messages.dashboard_recent_events') }}</h3><div class="card-kicker">{{ __('messages.dashboard_recent_events_note') }}</div></div></summary>
    <div class="dashboard-collapsible__body table-responsive"><table class="table overview-table"><thead><tr><th>{{ __('messages.table_when') }}</th><th>{{ __('messages.table_customer') }}</th><th>{{ __('messages.table_event') }}</th><th>{{ __('messages.table_explanation') }}</th></tr></thead><tbody>@forelse($businessEvents as $event)<tr><td>{{ $event->created_at->format('d/m H:i') }}</td><td>{{ $event->user?->name ?: __('messages.not_identified') }}</td><td><span class="badge-soft">{{ $event->title }}</span></td><td>{{ $event->message ?: __('messages.no_additional_details') }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">{{ __('messages.dashboard_no_events') }}</td></tr>@endforelse</tbody></table></div>
</details>
