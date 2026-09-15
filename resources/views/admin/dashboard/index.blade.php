@extends('layout.admin')

@section('title', __('messages.admin_dashboard_page_title'))

@section('content')
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

    <details class="dashboard-report-filters mb-3">
        <summary><i class="fa-solid fa-sliders me-2"></i>{{ __('messages.report_filter_title') }}</summary>
    <form method="GET" action="{{ route('admin.index') }}" id="dashboard-report-form" class="p-3 bg-light">
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
    </details>

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

    <div class="dashboard-secondary-action mt-3">
        <div>
            <strong>{{ __('messages.report_visitors_by_country') }}</strong>
            <span>{{ __('messages.dashboard_countries_on_demand_note') }}</span>
        </div>
        <button class="btn btn-outline-dark" type="button" data-bs-toggle="modal" data-bs-target="#dashboardCountriesModal" data-dashboard-countries-url="{{ route('admin.dashboard.countries', request()->query()) }}">
            <i class="fa-solid fa-earth-americas me-2"></i>{{ __('messages.dashboard_view_countries') }}
        </button>
    </div>
</section>

<div class="modal fade" id="dashboardCountriesModal" tabindex="-1" aria-labelledby="dashboardCountriesTitle" aria-hidden="true" data-loading-label="{{ __('messages.dashboard_loading_data') }}" data-error-label="{{ __('messages.update_error') }}">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content dashboard-modal-content">
            <div class="modal-header">
                <div>
                    <div class="card-kicker">{{ __('messages.report_selected_period') }}</div>
                    <h2 class="modal-title fs-5" id="dashboardCountriesTitle">{{ __('messages.report_visitors_by_country') }}</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
            </div>
            <div class="modal-body" data-dashboard-countries-body>
                <div class="dashboard-loading" role="status"><span class="spinner-border spinner-border-sm"></span>{{ __('messages.dashboard_loading_data') }}</div>
            </div>
        </div>
    </div>
</div>

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

<details id="integration-monitor" class="dashboard-collapsible table-card mb-4 border border-{{ $integrationPresentation[1] }}">
    <summary>
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
    </summary>

    <div class="dashboard-collapsible__body">
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

    <div class="small text-muted mt-3">
        {{ __('messages.dashboard_integration_compact_note') }}
    </div>
    </div>
</details>

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

<section class="traffic-overview table-card mb-4" aria-labelledby="traffic-overview-title">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-2 mb-3">
        <div>
            <div class="card-kicker">{{ __('messages.period_last_30_days') }}</div>
            <h2 class="card-heading" id="traffic-overview-title">{{ __('messages.dashboard_device_audience') }}</h2>
            <p class="text-muted small mb-0 mt-1">{{ __('messages.dashboard_device_audience_note') }}</p>
        </div>
        <span class="dashboard-live-badge"><i class="fa-solid fa-signal"></i>{{ __('messages.dashboard_analytics_summary') }}</span>
    </div>
    <div class="row g-3">
        @php
            $deviceConfig = [
                'desktop' => [__('messages.device_desktop'), 'fa-desktop'],
                'mobile' => [__('messages.device_mobile'), 'fa-mobile-screen-button'],
                'tablet' => [__('messages.device_tablet'), 'fa-tablet-screen-button'],
            ];
            $deviceVisitorsTotal = max(1, (int) $devices->sum('visitors'));
        @endphp
        @foreach($deviceConfig as $deviceKey => [$deviceLabel, $deviceIcon])
            @php
                $device = $devices->get($deviceKey);
                $deviceVisitors = (int) ($device->visitors ?? 0);
                $devicePercent = round(($deviceVisitors / $deviceVisitorsTotal) * 100);
            @endphp
            <div class="col-12 col-md-4">
                <article class="device-stat">
                    <div class="device-stat__icon"><i class="fa-solid {{ $deviceIcon }}"></i></div>
                    <div class="device-stat__copy">
                        <span>{{ $deviceLabel }}</span>
                        <strong>{{ number_format($deviceVisitors, 0, ',', '.') }}</strong>
                        <small>{{ $devicePercent }}% · {{ number_format((int) ($device->views ?? 0), 0, ',', '.') }} views</small>
                    </div>
                    <div class="device-stat__bar"><span style="width: {{ $devicePercent }}%"></span></div>
                </article>
            </div>
        @endforeach
    </div>
    <div class="screen-size-strip mt-3">
        <div>
            <strong>{{ __('messages.dashboard_screen_sizes') }}</strong>
            <span>{{ __('messages.dashboard_screen_sizes_note') }}</span>
        </div>
        <div class="screen-size-list">
            @forelse($screenSizes as $screen)
                <span><i class="fa-regular fa-window-maximize"></i>{{ $screen->viewport_width }} × {{ $screen->viewport_height }} <small>{{ $screen->visitors }}</small></span>
            @empty
                <span class="screen-size-empty">{{ __('messages.dashboard_screen_capture_notice') }}</span>
            @endforelse
        </div>
    </div>
</section>

<h2 class="section-title">{{ __('messages.dashboard_store_operation') }}</h2>
<div class="row g-3 mb-4">
    @php
        $businessCards = [
            [__('messages.metric_active_products'), $metrics['active_products'], 'fa-box-open', 'bg-green', __('messages.metric_products_total', ['count' => $metrics['products']])],
            [__('messages.metric_brands'), $metrics['brands'], 'fa-copyright', 'bg-purple', __('messages.metric_brands_note')],
            [__('messages.metric_categories'), $metrics['categories'], 'fa-tags', 'bg-blue', __('messages.metric_categories_note')],
            [__('messages.metric_customers'), $metrics['customers'], 'fa-user-group', 'bg-blue', __('messages.metric_customers_note')],
            [__('messages.metric_orders'), $metrics['orders'], 'fa-receipt', 'bg-green', __('messages.metric_orders_note')],
            [__('messages.metric_low_stock'), $metrics['low_stock'], 'fa-triangle-exclamation', 'bg-gold', __('messages.metric_low_stock_note')],
        ];
    @endphp
    @foreach($businessCards as [$label,$value,$icon,$color,$note])
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3"><div class="metric-card"><div class="metric-icon {{ $color }}"><i class="fa-solid {{ $icon }}"></i></div><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ number_format($value,0,',','.') }}</div><div class="metric-note">{{ $note }}</div></div></div>
    @endforeach
</div>

<section class="dashboard-details-gate table-card mb-4" data-dashboard-insights data-url="{{ route('admin.dashboard.insights') }}" data-loading-label="{{ __('messages.dashboard_loading_data') }}" data-error-label="{{ __('messages.update_error') }}">
    <div class="dashboard-details-gate__intro">
        <div class="dashboard-details-gate__icon"><i class="fa-solid fa-chart-line"></i></div>
        <div>
            <div class="card-kicker">{{ __('messages.dashboard_secondary_information') }}</div>
            <h2 class="card-heading">{{ __('messages.dashboard_detailed_analysis') }}</h2>
            <p>{{ __('messages.dashboard_detailed_analysis_note') }}</p>
        </div>
        <button class="btn btn-dark dashboard-load-insights" type="button" aria-expanded="false">
            <i class="fa-solid fa-chart-simple me-2"></i><span>{{ __('messages.dashboard_open_analysis') }}</span>
        </button>
    </div>
    <div class="dashboard-insights-content" hidden></div>
</section>

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
