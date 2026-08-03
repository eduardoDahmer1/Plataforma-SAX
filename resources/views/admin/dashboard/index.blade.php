@extends('layout.admin')

@section('title', __('messages.admin_dashboard_page_title'))

@section('content')
<div id="dashboard-chart-data" hidden data-traffic-labels="{{ json_encode($trafficLabels) }}" data-traffic-views="{{ json_encode($trafficViews) }}" data-traffic-visitors="{{ json_encode($trafficVisitors) }}" data-traffic-views-label="{{ __('messages.metric_page_views') }}" data-traffic-visitors-label="{{ __('messages.metric_unique_visits') }}" data-payment-labels="{{ json_encode($paymentMethods->keys()->values()) }}" data-payment-values="{{ json_encode($paymentMethods->values()) }}" data-order-labels="{{ json_encode($orderStatuses->keys()->map(fn($key) => ['pending' => __('messages.status_pending'), 'paid' => __('messages.status_paid'), 'processing' => __('messages.status_processing'), 'shipped' => __('messages.status_shipped'), 'completed' => __('messages.status_completed'), 'canceled' => __('messages.status_canceled'), 'failed' => __('messages.status_failed')][$key] ?? ucfirst($key))->values()) }}" data-order-values="{{ json_encode($orderStatuses->values()) }}" data-device-labels="{{ json_encode($devices->keys()->map(fn($key) => ['desktop' => __('messages.device_desktop'), 'tablet' => __('messages.device_tablet'), 'mobile' => __('messages.device_mobile')][$key] ?? ucfirst($key))->values()) }}" data-device-values="{{ json_encode($devices->values()) }}"></div>
<section class="overview-hero mb-4">
    <div class="d-md-flex justify-content-between align-items-center position-relative" style="z-index:1">
        <div>
            <div class="overview-eyebrow">{{ __('messages.admin_dashboard_business_intelligence') }}</div>
            <h1 class="overview-title">{{ __('messages.admin_dashboard_overview') }}</h1>
            <p class="overview-subtitle">{{ __('messages.admin_dashboard_subtitle') }}</p>
        </div>
        <div class="overview-date"><i class="fa-regular fa-calendar me-2"></i>{{ now()->translatedFormat('d \d\e F \d\e Y') }}</div>
    </div>
</section>

<section class="table-card mb-4" id="dashboard-report-period">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
        <div>
            <div class="card-kicker">{{ __('messages.report_filter_kicker') }}</div>
            <h2 class="card-heading mb-1">{{ __('messages.report_filter_title') }}</h2>
            <p class="text-muted small mb-0">{{ __('messages.report_filter_subtitle') }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-start">
            <a class="btn btn-sm {{ $reportFilter['type'] === 'day' && $reportFilter['day'] === now()->format('Y-m-d') ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.index', ['report_type' => 'day', 'report_day' => now()->format('Y-m-d')]) }}">
                <i class="fa-solid fa-calendar-day me-1"></i>{{ __('messages.period_today') }}
            </a>
            <a class="btn btn-sm {{ $reportFilter['type'] === 'week' && $reportFilter['week'] === now()->format('o-\WW') ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.index', ['report_type' => 'week', 'report_week' => now()->format('o-\WW')]) }}">
                <i class="fa-solid fa-calendar-week me-1"></i>{{ __('messages.report_current_week') }}
            </a>
            <a class="btn btn-sm {{ $reportFilter['type'] === 'month' && $reportFilter['month'] === now()->format('Y-m') ? 'btn-dark' : 'btn-outline-dark' }}" href="{{ route('admin.index', ['report_type' => 'month', 'report_month' => now()->format('Y-m')]) }}">
                <i class="fa-regular fa-calendar me-1"></i>{{ __('messages.report_current_month') }}
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.index') }}" id="dashboard-report-form" class="border rounded-3 p-3 bg-light mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-4 col-xl-3">
                <label class="form-label fw-semibold" for="report-type">{{ __('messages.report_period_type') }}</label>
                <select class="form-select" id="report-type" name="report_type">
                    <option value="day" @selected($reportFilter['type'] === 'day')>{{ __('messages.report_type_day') }}</option>
                    <option value="week" @selected($reportFilter['type'] === 'week')>{{ __('messages.report_type_week') }}</option>
                    <option value="month" @selected($reportFilter['type'] === 'month')>{{ __('messages.report_type_month') }}</option>
                    <option value="custom" @selected($reportFilter['type'] === 'custom')>{{ __('messages.report_type_custom') }}</option>
                </select>
            </div>

            <div class="col-12 col-md-4 col-xl-3 report-period-field" data-report-period="day" @if($reportFilter['type'] !== 'day') hidden @endif>
                <label class="form-label fw-semibold" for="report-day">{{ __('messages.report_choose_day') }}</label>
                <input class="form-control" id="report-day" name="report_day" type="date" value="{{ $reportFilter['day'] }}">
            </div>
            <div class="col-12 col-md-4 col-xl-3 report-period-field" data-report-period="week" @if($reportFilter['type'] !== 'week') hidden @endif>
                <label class="form-label fw-semibold" for="report-week">{{ __('messages.report_choose_week') }}</label>
                <input class="form-control" id="report-week" name="report_week" type="week" value="{{ $reportFilter['week'] }}">
            </div>
            <div class="col-12 col-md-4 col-xl-3 report-period-field" data-report-period="month" @if($reportFilter['type'] !== 'month') hidden @endif>
                <label class="form-label fw-semibold" for="report-month">{{ __('messages.report_choose_month') }}</label>
                <input class="form-control" id="report-month" name="report_month" type="month" value="{{ $reportFilter['month'] }}">
            </div>
            <div class="col-12 col-md-4 col-xl-3 report-period-field" data-report-period="custom" @if($reportFilter['type'] !== 'custom') hidden @endif>
                <label class="form-label fw-semibold" for="report-start">{{ __('messages.report_start_date') }}</label>
                <input class="form-control" id="report-start" name="report_start" type="date" value="{{ $reportFilter['start'] }}">
            </div>
            <div class="col-12 col-md-4 col-xl-3 report-period-field" data-report-period="custom" @if($reportFilter['type'] !== 'custom') hidden @endif>
                <label class="form-label fw-semibold" for="report-end">{{ __('messages.report_end_date') }}</label>
                <input class="form-control" id="report-end" name="report_end" type="date" value="{{ $reportFilter['end'] }}">
            </div>

            <div class="col-12 col-xl d-flex flex-wrap gap-2 justify-content-xl-end">
                <button class="btn btn-dark" type="submit">
                    <i class="fa-solid fa-chart-column me-1"></i>{{ __('messages.report_view') }}
                </button>
                <button class="btn btn-outline-dark" type="submit" formaction="{{ route('admin.reports.download') }}">
                    <i class="fa-regular fa-file-pdf me-1"></i>{{ __('messages.report_download_pdf') }}
                </button>
            </div>
        </div>
    </form>

    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 align-items-md-center mb-3">
        <div>
            <span class="text-muted small">{{ __('messages.report_selected_period') }}</span>
            <strong class="d-block">{{ $selectedReport['period'] }}</strong>
        </div>
        <span class="badge text-bg-dark align-self-start align-self-md-center">
            {{ $selectedReport['start']->format('d/m/Y') }} — {{ $selectedReport['end']->format('d/m/Y') }}
        </span>
    </div>

    @php
        $reportCards = [
            [__('messages.report_paid_sales'), $selectedReport['paid_orders'], 'fa-circle-check'],
            [__('messages.report_sold_value_base'), 'US$ '.number_format($selectedReport['sales_total'], 2, ',', '.'), 'fa-money-bill-trend-up'],
            [__('messages.report_orders_created'), $selectedReport['orders'], 'fa-receipt'],
            [__('messages.report_new_customers'), $selectedReport['new_customers'], 'fa-user-plus'],
            [__('messages.report_visitors'), $selectedReport['visitors'], 'fa-users'],
            [__('messages.report_clicks'), $selectedReport['clicks'], 'fa-arrow-pointer'],
            [__('messages.report_page_views'), $selectedReport['views'], 'fa-eye'],
            [__('messages.report_abandoned_carts'), $selectedReport['abandoned_carts'], 'fa-cart-arrow-down'],
        ];
    @endphp
    <div class="row g-2">
        @foreach($reportCards as [$label, $value, $icon])
            <div class="col-6 col-lg-3">
                <div class="border rounded-3 p-3 h-100 bg-white">
                    <div class="small text-muted text-uppercase"><i class="fa-solid {{ $icon }} me-1"></i>{{ $label }}</div>
                    <div class="fs-5 fw-bold mt-1">{{ is_numeric($value) ? number_format($value, 0, ',', '.') : $value }}</div>
                </div>
            </div>
        @endforeach
    </div>

    @if($selectedReport['payment_methods']->isNotEmpty())
        <div class="d-flex flex-wrap gap-2 mt-3 align-items-center">
            <span class="small text-muted me-1">{{ __('messages.report_orders_by_payment') }}:</span>
            @foreach($selectedReport['payment_methods'] as $method => $total)
                <span class="badge rounded-pill text-bg-light border">
                    {{ ['bancard_v2' => 'Bancard V2', 'rendix_pix' => 'Pix Rendix', 'deposito' => __('messages.payment_deposit'), 'whatsapp' => 'WhatsApp'][$method] ?? ucfirst($method ?: __('messages.payment_other')) }}: {{ $total }}
                </span>
            @endforeach
        </div>
    @endif
</section>

@php
    $integrationStatus = $integrationMonitor?->status ?? 'never_reported';
    $integrationPresentation = [
        'healthy' => [__('messages.integration_status_healthy'), 'success', 'fa-circle-check'],
        'running' => [__('messages.integration_status_running'), 'warning', 'fa-arrows-rotate'],
        'failed' => [__('messages.integration_status_failed'), 'danger', 'fa-triangle-exclamation'],
        'stale' => [__('messages.integration_status_stale'), 'danger', 'fa-plug-circle-xmark'],
        'never_reported' => [__('messages.integration_status_waiting'), 'secondary', 'fa-clock'],
    ][$integrationStatus] ?? [__('messages.integration_status_unknown'), 'secondary', 'fa-circle-question'];
@endphp

<section id="integration-monitor" class="table-card mb-4 border border-{{ $integrationPresentation[1] }}">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-start">
        <div>
            <div class="card-kicker">{{ __('messages.integration_automatic_monitoring') }}</div>
            <h2 class="card-heading mb-2">
                <i class="fa-solid {{ $integrationPresentation[2] }} text-{{ $integrationPresentation[1] }} me-2"></i>
                {{ __('messages.integration_products_title') }}
            </h2>
            <span class="badge text-bg-{{ $integrationPresentation[1] }}">{{ $integrationPresentation[0] }}</span>
        </div>
        <div class="small text-muted text-lg-end">
            <div><strong>{{ __('messages.integration_last_communication') }}</strong> {{ $integrationMonitor?->last_heartbeat_at?->format('d/m/Y H:i:s') ?? __('messages.integration_not_received') }}</div>
            <div><strong>{{ __('messages.integration_last_success') }}</strong> {{ $integrationMonitor?->last_success_at?->format('d/m/Y H:i:s') ?? __('messages.integration_not_registered') }}</div>
            <div><strong>{{ __('messages.integration_consecutive_failures') }}</strong> {{ (int) ($integrationMonitor?->consecutive_failures ?? 0) }}</div>
        </div>
    </div>

    @if($integrationMonitor?->error_message)
        <div class="alert alert-{{ in_array($integrationStatus, ['failed', 'stale'], true) ? 'danger' : 'warning' }} mt-3 mb-3">
            <strong>{{ $integrationMonitor->error_code ?: __('messages.integration_error') }}:</strong>
            {{ $integrationMonitor->error_message }}
        </div>
    @elseif(!$integrationMonitoringReady)
        <div class="alert alert-secondary mt-3 mb-3">
            {{ __('messages.integration_not_initialized') }}
        </div>
    @elseif(!$integrationEndpointConfigured)
        <div class="alert alert-warning mt-3 mb-3">
            {{ __('messages.integration_token_missing') }}
        </div>
    @elseif(!$integrationMonitor)
        <div class="alert alert-info mt-3 mb-3">
            {{ __('messages.integration_waiting_first_report') }}
        </div>
    @endif

    <div class="table-responsive mt-3">
        <table class="table overview-table mb-0">
            <thead><tr><th>{{ __('messages.table_start') }}</th><th>{{ __('messages.table_end') }}</th><th>{{ __('messages.table_result') }}</th><th>{{ __('messages.table_duration') }}</th><th>{{ __('messages.table_detail') }}</th></tr></thead>
            <tbody>
            @forelse($integrationRuns as $run)
                @php
                    $runStyle = ['success' => 'success', 'failed' => 'danger', 'running' => 'warning'][$run->status] ?? 'secondary';
                    $runLabel = ['success' => __('messages.integration_run_completed'), 'failed' => __('messages.integration_run_failed'), 'running' => __('messages.integration_run_running')][$run->status] ?? ucfirst($run->status);
                @endphp
                <tr>
                    <td>{{ $run->started_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                    <td>{{ $run->finished_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                    <td><span class="badge text-bg-{{ $runStyle }}">{{ $runLabel }}</span></td>
                    <td>{{ $run->duration_seconds !== null ? gmdate('H:i:s', $run->duration_seconds) : '—' }}</td>
                    <td class="text-break">{{ $run->error_message ?: __('messages.integration_no_errors') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">{{ __('messages.integration_no_runs') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

@if(!$analyticsReady)
    <div class="analytics-empty mb-4"><i class="fa-solid fa-circle-info me-2"></i>{{ __('messages.analytics_not_ready') }}</div>
@endif

<h2 class="section-title">{{ __('messages.dashboard_audience_today') }}</h2>
<div class="row g-3 mb-4">
    @php
        $audienceCards = [
            [__('messages.metric_unique_visits'), $analytics['visitors_today'], 'fa-users-viewfinder', 'bg-purple', __('messages.metric_unique_visits_note')],
            [__('messages.metric_page_views'), $analytics['views_today'], 'fa-eye', 'bg-blue', __('messages.metric_page_views_note')],
            [__('messages.metric_clicks'), $analytics['clicks_today'], 'fa-arrow-pointer', 'bg-green', __('messages.metric_clicks_note')],
            [__('messages.metric_views_30_days'), $analytics['views_30_days'], 'fa-chart-column', 'bg-gold', __('messages.metric_views_30_days_note')],
        ];
    @endphp
    @foreach($audienceCards as [$label,$value,$icon,$color,$note])
        <div class="col-12 col-sm-6 col-xl-3"><div class="metric-card"><div class="metric-icon {{ $color }}"><i class="fa-solid {{ $icon }}"></i></div><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ number_format($value,0,',','.') }}</div><div class="metric-note">{{ $note }}</div></div></div>
    @endforeach
</div>

<h2 class="section-title">{{ __('messages.dashboard_store_operation') }}</h2>
<div class="row g-3 mb-4">
    @php
        $businessCards = [
            [__('messages.metric_active_products'), $metrics['active_products'], 'fa-box-open', 'bg-green', __('messages.metric_products_total', ['count' => $metrics['products']])],
            [__('messages.metric_brands'), $metrics['brands'], 'fa-copyright', 'bg-purple', __('messages.metric_brands_note')],
            [__('messages.metric_categories'), $metrics['categories'], 'fa-tags', 'bg-blue', __('messages.metric_categories_note')],
            [__('messages.metric_subcategories'), $metrics['subcategories'], 'fa-tag', 'bg-cyan', __('messages.metric_subcategories_note')],
            [__('messages.metric_child_categories'), $metrics['childcategories'], 'fa-sitemap', 'bg-gold', __('messages.metric_child_categories_note')],
            [__('messages.metric_published_blogs'), $metrics['published_blogs'], 'fa-newspaper', 'bg-purple', __('messages.metric_published_blogs_note')],
            [__('messages.metric_customers'), $metrics['customers'], 'fa-user-group', 'bg-blue', __('messages.metric_customers_note')],
            [__('messages.metric_orders'), $metrics['orders'], 'fa-receipt', 'bg-green', __('messages.metric_orders_note')],
            [__('messages.metric_low_stock'), $metrics['low_stock'], 'fa-triangle-exclamation', 'bg-gold', __('messages.metric_low_stock_note')],
            [__('messages.metric_out_of_stock'), $metrics['out_of_stock'], 'fa-ban', 'bg-red', __('messages.metric_out_of_stock_note')],
            [__('messages.metric_abandoned_carts'), $metrics['abandoned_carts'], 'fa-cart-arrow-down', 'bg-red', __('messages.metric_abandoned_carts_note')],
            [__('messages.metric_contacts'), $metrics['contacts'], 'fa-envelope', 'bg-cyan', __('messages.metric_contacts_note')],
        ];
    @endphp
    @foreach($businessCards as [$label,$value,$icon,$color,$note])
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3"><div class="metric-card"><div class="metric-icon {{ $color }}"><i class="fa-solid {{ $icon }}"></i></div><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ number_format($value,0,',','.') }}</div><div class="metric-note">{{ $note }}</div></div></div>
    @endforeach
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8"><div class="chart-card"><h3 class="card-heading">{{ __('messages.dashboard_traffic_30_days') }}</h3><div class="card-kicker">{{ __('messages.dashboard_traffic_note') }}</div><div class="chart-wrap"><canvas id="trafficChart"></canvas></div></div></div>
    <div class="col-xl-4"><div class="chart-card"><h3 class="card-heading">{{ __('messages.dashboard_orders_by_payment') }}</h3><div class="card-kicker">{{ __('messages.dashboard_payments_note') }}</div><div class="chart-wrap small"><canvas id="paymentsChart"></canvas></div><div class="d-flex justify-content-around text-center mt-2"><div><b>{{ $metrics['bancard_orders'] }}</b><small class="d-block text-muted">Bancard</small></div><div><b>{{ $metrics['deposit_orders'] }}</b><small class="d-block text-muted">{{ __('messages.payment_deposit') }}</small></div><div><b>{{ $metrics['whatsapp_orders'] }}</b><small class="d-block text-muted">WhatsApp</small></div></div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-6"><div class="table-card"><h3 class="card-heading">{{ __('messages.dashboard_top_pages') }}</h3><div class="card-kicker">{{ __('messages.period_last_30_days') }}</div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>#</th><th>{{ __('messages.table_page') }}</th><th>Views</th><th>{{ __('messages.table_people') }}</th></tr></thead><tbody>@forelse($topPages as $page)<tr><td><span class="rank">{{ $loop->iteration }}</span></td><td class="path-cell" title="{{ $page->path }}">{{ $page->path }}</td><td><b>{{ number_format($page->total,0,',','.') }}</b></td><td>{{ number_format($page->visitors,0,',','.') }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">{{ __('messages.dashboard_no_accesses') }}</td></tr>@endforelse</tbody></table></div></div></div>
    <div class="col-xl-6"><div class="table-card"><h3 class="card-heading">{{ __('messages.dashboard_top_clicks') }}</h3><div class="card-kicker">{{ __('messages.dashboard_top_clicks_note') }}</div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>#</th><th>{{ __('messages.table_element') }}</th><th>{{ __('messages.table_page') }}</th><th>{{ __('messages.metric_clicks') }}</th></tr></thead><tbody>@forelse($topClicks as $click)<tr><td><span class="rank">{{ $loop->iteration }}</span></td><td class="path-cell" title="{{ $click->target }}">{{ $click->element_text ?: $click->target ?: __('messages.element_without_text') }}</td><td class="path-cell" title="{{ $click->path }}">{{ $click->path }}</td><td><b>{{ number_format($click->total,0,',','.') }}</b></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">{{ __('messages.dashboard_no_clicks') }}</td></tr>@endforelse</tbody></table></div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-4"><div class="chart-card"><h3 class="card-heading">{{ __('messages.dashboard_order_statuses') }}</h3><div class="card-kicker">{{ __('messages.dashboard_general_distribution') }}</div><div class="chart-wrap small"><canvas id="ordersChart"></canvas></div></div></div>
    <div class="col-xl-4"><div class="chart-card"><h3 class="card-heading">{{ __('messages.dashboard_devices') }}</h3><div class="card-kicker">{{ __('messages.dashboard_devices_note') }}</div><div class="chart-wrap small"><canvas id="devicesChart"></canvas></div></div></div>
    <div class="col-xl-4"><div class="table-card"><h3 class="card-heading">{{ __('messages.dashboard_most_viewed_products') }}</h3><div class="card-kicker">{{ __('messages.dashboard_product_ranking_note') }}</div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>#</th><th>{{ __('messages.table_product') }}</th><th>Views</th><th>{{ __('messages.table_stock') }}</th></tr></thead><tbody>@forelse($topProducts as $product)<tr><td><span class="rank">{{ $loop->iteration }}</span></td><td class="path-cell">{{ $product->name ?: $product->external_name ?: '#'.$product->id }}</td><td><b>{{ number_format($product->views ?? 0,0,',','.') }}</b></td><td><span class="badge-soft">{{ $product->stock ?? 0 }}</span></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">{{ __('messages.no_products_found') }}</td></tr>@endforelse</tbody></table></div></div></div>
</div>

<div class="table-card mb-4"><div class="d-flex justify-content-between align-items-center"><div><h3 class="card-heading">{{ __('messages.dashboard_recent_orders') }}</h3><div class="card-kicker">{{ __('messages.dashboard_latest_store_activity') }}</div></div><a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-dark">{{ __('messages.view_all') }}</a></div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>{{ __('messages.table_order') }}</th><th>{{ __('messages.table_customer') }}</th><th>{{ __('messages.table_payment') }}</th><th>{{ __('messages.table_status') }}</th><th>{{ __('messages.total') }}</th><th>{{ __('messages.table_date') }}</th></tr></thead><tbody>@forelse($recentOrders as $order)<tr><td><a href="{{ route('admin.orders.show',$order) }}" class="fw-bold text-dark">#{{ $order->order_number ?: $order->id }}</a></td><td>{{ $order->user?->name ?: $order->name ?: __('messages.guest') }}</td><td>{{ ['bancard_v2'=>'Bancard V2','rendix_pix'=>'Pix Rendix','deposito'=>__('messages.payment_deposit'),'whatsapp'=>'WhatsApp'][$order->payment_method] ?? ucfirst($order->payment_method) }}</td><td><span class="badge-soft">{{ ucfirst($order->status) }}</span></td><td>{{ $order->currency_sign ?: 'US$' }} {{ number_format($order->total,2,',','.') }}</td><td>{{ $order->created_at?->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">{{ __('messages.no_orders_found') }}</td></tr>@endforelse</tbody></table></div></div>

<div class="table-card mb-4"><h3 class="card-heading">{{ __('messages.dashboard_recent_events') }}</h3><div class="card-kicker">{{ __('messages.dashboard_recent_events_note') }}</div><div class="table-responsive"><table class="table overview-table"><thead><tr><th>{{ __('messages.table_when') }}</th><th>{{ __('messages.table_customer') }}</th><th>{{ __('messages.table_event') }}</th><th>{{ __('messages.table_explanation') }}</th><th>{{ __('messages.table_reference') }}</th></tr></thead><tbody>@forelse($businessEvents as $event)<tr><td>{{ $event->created_at->format('d/m H:i') }}</td><td>{{ $event->user?->name ?: __('messages.not_identified') }}</td><td><span class="badge-soft">{{ $event->title }}</span></td><td>{{ $event->message ?: __('messages.no_additional_details') }}</td><td>@if($event->order)<a href="{{ route('admin.orders.show',$event->order) }}">#{{ $event->order->order_number ?: $event->order_id }}</a>@else{{ $event->reference ?: '—' }}@endif</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">{{ __('messages.dashboard_no_events') }}</td></tr>@endforelse</tbody></table></div></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('report-type');
    const periodFields = document.querySelectorAll('.report-period-field');

    if (!typeSelect || !periodFields.length) {
        return;
    }

    const updatePeriodFields = function () {
        periodFields.forEach(function (field) {
            field.hidden = field.dataset.reportPeriod !== typeSelect.value;
        });
    };

    typeSelect.addEventListener('change', updatePeriodFields);
    updatePeriodFields();
});
</script>
@endsection
