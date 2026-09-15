<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteAnalyticsEvent;
use App\Models\User;
use App\Models\BusinessEvent;
use App\Models\IntegrationMonitor;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $start = now()->subDays(29)->startOfDay();

        $metrics = Cache::remember('admin.dashboard.metrics', now()->addSeconds(30), function () {
            $productMetrics = Product::query()->selectRaw(
                'COUNT(*) AS total, '
                .'SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active_total, '
                .'SUM(CASE WHEN status = 1 AND stock BETWEEN 1 AND 5 THEN 1 ELSE 0 END) AS low_stock, '
                .'SUM(CASE WHEN status = 1 AND stock <= 0 THEN 1 ELSE 0 END) AS out_of_stock'
            )->first();
            $orderMetrics = Order::query()->selectRaw(
                'COUNT(*) AS total'
            )->first();

            return [
                'brands' => Brand::count(),
                'categories' => Category::count(),
                'active_products' => (int) $productMetrics->active_total,
                'products' => (int) $productMetrics->total,
                'customers' => User::whereNotIn('user_type', [User::TYPE_ADMIN_MASTER, User::TYPE_ADMIN_EDITOR])->count(),
                'orders' => (int) $orderMetrics->total,
                'low_stock' => (int) $productMetrics->low_stock,
            ];
        });

        $analyticsReady = Cache::remember('schema.site_analytics_events', 3600, fn () => Schema::hasTable('site_analytics_events'));
        $analytics = [
            'views_today' => 0,
            'visitors_today' => 0,
            'clicks_today' => 0,
            'views_30_days' => 0,
        ];
        $devices = collect();
        $screenSizes = collect();
        $integrationMonitoringReady = Cache::remember('schema.integration_monitoring', 3600, fn () =>
            Schema::hasTable('integration_monitors') && Schema::hasTable('integration_runs')
        );
        $integrationEndpointConfigured = filled(config('services.integration_monitor.token'));
        $integrationMonitor = $integrationMonitoringReady
            ? IntegrationMonitor::where('source', 'catalog')->first()
            : null;

        if ($analyticsReady) {
            $summary = SiteAnalyticsEvent::query()
                ->where('event_date', '>=', $start->toDateString())
                ->selectRaw('SUM(CASE WHEN event_type = ? AND event_date = ? THEN 1 ELSE 0 END) AS views_today', ['page_view', $today])
                ->selectRaw('COUNT(DISTINCT CASE WHEN event_type = ? AND event_date = ? THEN visitor_hash END) AS visitors_today', ['page_view', $today])
                ->selectRaw('SUM(CASE WHEN event_type = ? AND event_date = ? THEN 1 ELSE 0 END) AS clicks_today', ['click', $today])
                ->selectRaw('SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) AS views_30_days', ['page_view'])
                ->first();

            $analytics = [
                'views_today' => (int) $summary->views_today,
                'visitors_today' => (int) $summary->visitors_today,
                'clicks_today' => (int) $summary->clicks_today,
                'views_30_days' => (int) $summary->views_30_days,
            ];

            $devices = SiteAnalyticsEvent::where('event_type', 'page_view')->where('event_date', '>=', $start->toDateString())
                ->whereNotNull('device_type')
                ->select('device_type', DB::raw('COUNT(*) AS views'), DB::raw('COUNT(DISTINCT visitor_hash) AS visitors'))
                ->groupBy('device_type')->get()->keyBy('device_type');

            $screenTrackingReady = Cache::remember(
                'schema.site_analytics_screen_dimensions',
                3600,
                fn () => Schema::hasColumn('site_analytics_events', 'viewport_width')
            );
            if ($screenTrackingReady) {
                $screenSizes = SiteAnalyticsEvent::where('event_type', 'page_view')
                    ->where('event_date', '>=', $start->toDateString())
                    ->whereNotNull('viewport_width')
                    ->whereNotNull('viewport_height')
                    ->select('viewport_width', 'viewport_height', DB::raw('COUNT(DISTINCT visitor_hash) AS visitors'))
                    ->groupBy('viewport_width', 'viewport_height')
                    ->orderByDesc('visitors')->limit(6)->get();
            }
        }

        $reportSelection = $this->resolveReportPeriod($request);
        $selectedReport = $this->buildReport(
            $reportSelection['start'],
            $reportSelection['end'],
            $reportSelection['label'],
            false
        );
        $reportFilter = $reportSelection['filter'];

        return view('admin.dashboard.index', compact(
            'metrics', 'analytics', 'analyticsReady', 'devices', 'screenSizes',
            'integrationMonitor', 'integrationMonitoringReady', 'integrationEndpointConfigured',
            'selectedReport', 'reportFilter'
        ));
    }

    public function insights(): View
    {
        $start = now()->subDays(29)->startOfDay();
        $analyticsReady = Cache::remember('schema.site_analytics_events', 3600, fn () => Schema::hasTable('site_analytics_events'));

        $data = Cache::remember('admin.dashboard.insights', now()->addMinute(), function () use ($analyticsReady, $start): array {
            $trafficLabels = [];
            $trafficViews = [];
            $trafficVisitors = [];
            $topPages = collect();
            $topClicks = collect();

            if ($analyticsReady) {
                $daily = SiteAnalyticsEvent::where('event_type', 'page_view')
                    ->where('event_date', '>=', $start->toDateString())
                    ->select('event_date', DB::raw('COUNT(*) AS views'), DB::raw('COUNT(DISTINCT visitor_hash) AS visitors'))
                    ->groupBy('event_date')->orderBy('event_date')->get()->keyBy(fn ($row) => Carbon::parse($row->event_date)->toDateString());

                for ($date = $start->copy(); $date->lte(now()); $date->addDay()) {
                    $key = $date->toDateString();
                    $trafficLabels[] = $date->format('d/m');
                    $trafficViews[] = (int) ($daily->get($key)->views ?? 0);
                    $trafficVisitors[] = (int) ($daily->get($key)->visitors ?? 0);
                }

                $topPages = SiteAnalyticsEvent::where('event_type', 'page_view')->where('event_date', '>=', $start->toDateString())
                    ->select('path', DB::raw('COUNT(*) AS total'), DB::raw('COUNT(DISTINCT visitor_hash) AS visitors'))
                    ->groupBy('path')->orderByDesc('total')->limit(8)->get();
                $topClicks = SiteAnalyticsEvent::where('event_type', 'click')->where('event_date', '>=', $start->toDateString())
                    ->select('path', 'element_text', 'target', DB::raw('COUNT(*) AS total'))
                    ->groupBy('path', 'element_text', 'target')->orderByDesc('total')->limit(8)->get();
            }

            return [
                'trafficLabels' => $trafficLabels,
                'trafficViews' => $trafficViews,
                'trafficVisitors' => $trafficVisitors,
                'topPages' => $topPages,
                'topClicks' => $topClicks,
                'paymentMethods' => Order::select('payment_method', DB::raw('COUNT(*) AS total'))->groupBy('payment_method')->pluck('total', 'payment_method'),
                'orderStatuses' => Order::select('status', DB::raw('COUNT(*) AS total'))->groupBy('status')->pluck('total', 'status'),
                'topProducts' => Product::orderByDesc('views')->limit(6)->get(['id', 'name', 'external_name', 'views', 'stock']),
                'recentOrders' => Order::with('user:id,name')->latest()->limit(6)->get(),
                'businessEvents' => Schema::hasTable('business_events')
                    ? BusinessEvent::with(['user:id,name', 'order:id,order_number'])->latest()->limit(8)->get()
                    : collect(),
            ];
        });

        return view('admin.dashboard.partials.insights', $data);
    }

    public function countries(Request $request): View
    {
        $selection = $this->resolveReportPeriod($request);
        $countries = $this->visitorsByCountry($selection['start'], $selection['end']);

        return view('admin.dashboard.partials.countries', compact('countries', 'selection'));
    }

    public function report(Request $request, ?string $period = null): Response
    {
        $selection = $this->resolveReportPeriod($request, $period);
        $report = $this->buildReport($selection['start'], $selection['end'], $selection['label'], true);
        $filename = sprintf(
            'relatorio-sax-%s-%s-a-%s.pdf',
            $selection['type'],
            $selection['start']->format('Y-m-d'),
            $selection['end']->format('Y-m-d')
        );

        return Pdf::loadView('admin.dashboard.report', compact('report'))
            ->setPaper('a4')
            ->download($filename);
    }

    private function buildReport(Carbon $start, Carbon $end, string $label, bool $includeCountries = true): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end]);
        $orderSummary = (clone $orders)
            ->selectRaw('COUNT(*) AS orders')
            ->selectRaw("SUM(CASE WHEN payment_status = 'paid' OR status = 'paid' THEN 1 ELSE 0 END) AS paid_orders")
            ->selectRaw("SUM(CASE WHEN payment_status = 'paid' OR status = 'paid' THEN total ELSE 0 END) AS sales_total")
            ->first();
        $analyticsReady = Schema::hasTable('site_analytics_events');
        $analyticsSummary = $analyticsReady
            ? SiteAnalyticsEvent::whereBetween('created_at', [$start, $end])
                ->selectRaw("SUM(CASE WHEN event_type = 'page_view' THEN 1 ELSE 0 END) AS views")
                ->selectRaw("COUNT(DISTINCT CASE WHEN event_type = 'page_view' THEN visitor_hash END) AS visitors")
                ->selectRaw("SUM(CASE WHEN event_type = 'click' THEN 1 ELSE 0 END) AS clicks")
                ->first()
            : null;
        $visitorsByCountry = $includeCountries ? $this->visitorsByCountry($start, $end) : collect();

        return [
            'period' => $label,
            'start' => $start->copy(),
            'end' => $end->copy(),
            'orders' => (int) $orderSummary->orders,
            'paid_orders' => (int) $orderSummary->paid_orders,
            'sales_total' => (float) $orderSummary->sales_total,
            'new_customers' => User::whereNotIn('user_type', [User::TYPE_ADMIN_MASTER, User::TYPE_ADMIN_EDITOR])->whereBetween('created_at', [$start, $end])->count(),
            'abandoned_carts' => AbandonedCart::whereBetween('abandoned_at', [$start, $end])->count(),
            'views' => (int) ($analyticsSummary?->views ?? 0),
            'visitors' => (int) ($analyticsSummary?->visitors ?? 0),
            'clicks' => (int) ($analyticsSummary?->clicks ?? 0),
            'visitors_by_country' => $visitorsByCountry,
            'payment_methods' => (clone $orders)->select('payment_method', DB::raw('COUNT(*) total'))->groupBy('payment_method')->pluck('total', 'payment_method'),
        ];
    }

    private function visitorsByCountry(Carbon $start, Carbon $end)
    {
        $ready = Schema::hasTable('site_analytics_events')
            && Schema::hasColumn('site_analytics_events', 'country_code');

        return $ready
            ? SiteAnalyticsEvent::where('event_type', 'page_view')
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw("COALESCE(country_code, 'XX') AS country_code")
                ->selectRaw("COALESCE(country_name, ?) AS country_name", [__('messages.report_country_unknown')])
                ->selectRaw('COUNT(DISTINCT visitor_hash) AS visitors')
                ->groupBy('country_code', 'country_name')
                ->orderByDesc('visitors')->get()
            : collect();
    }

    private function resolveReportPeriod(Request $request, ?string $legacyPeriod = null): array
    {
        $now = now();

        if ($legacyPeriod !== null) {
            [$type, $start, $end, $label] = match ($legacyPeriod) {
                'week' => ['week', $now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(), __('messages.period_last_seven_days')],
                'month' => ['month', $now->copy()->startOfMonth(), $now->copy()->endOfDay(), __('messages.period_current_month')],
                default => ['day', $now->copy()->startOfDay(), $now->copy()->endOfDay(), __('messages.period_today')],
            };

            return [
                'type' => $type,
                'start' => $start,
                'end' => $end,
                'label' => $label,
                'filter' => $this->normalizedReportFilter($type, $start, $end),
            ];
        }

        $type = in_array($request->string('report_type')->toString(), ['day', 'week', 'month', 'custom'], true)
            ? $request->string('report_type')->toString()
            : 'day';

        [$start, $end] = match ($type) {
            'week' => $this->weekRange($request->string('report_week')->toString(), $now),
            'month' => $this->monthRange($request->string('report_month')->toString(), $now),
            'custom' => $this->customRange(
                $request->string('report_start')->toString(),
                $request->string('report_end')->toString(),
                $now
            ),
            default => $this->dayRange($request->string('report_day')->toString(), $now),
        };

        return [
            'type' => $type,
            'start' => $start,
            'end' => $end,
            'label' => $this->reportPeriodLabel($type, $start, $end),
            'filter' => $this->normalizedReportFilter($type, $start, $end),
        ];
    }

    private function dayRange(string $value, Carbon $fallback): array
    {
        $date = $this->dateFromFormat('Y-m-d', $value) ?? $fallback->copy();

        return [$date->copy()->startOfDay(), $date->copy()->endOfDay()];
    }

    private function weekRange(string $value, Carbon $fallback): array
    {
        if (preg_match('/^(\d{4})-W(\d{2})$/', $value, $parts)) {
            $candidate = $fallback->copy()->setISODate((int) $parts[1], (int) $parts[2], 1)->startOfDay();

            if ($candidate->format('o-\\WW') === $value) {
                return [$candidate, $candidate->copy()->endOfWeek()->endOfDay()];
            }
        }

        $start = $fallback->copy()->startOfWeek()->startOfDay();

        return [$start, $start->copy()->endOfWeek()->endOfDay()];
    }

    private function monthRange(string $value, Carbon $fallback): array
    {
        $month = preg_match('/^\d{4}-\d{2}$/', $value)
            ? $this->dateFromFormat('Y-m-d', $value . '-01')
            : null;
        $month ??= $fallback->copy();

        return [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()->endOfDay()];
    }

    private function customRange(string $startValue, string $endValue, Carbon $fallback): array
    {
        $start = $this->dateFromFormat('Y-m-d', $startValue) ?? $fallback->copy();
        $end = $this->dateFromFormat('Y-m-d', $endValue) ?? $start->copy();

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        return [$start->startOfDay(), $end->endOfDay()];
    }

    private function dateFromFormat(string $format, string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!' . $format, $value, config('app.timezone'));

            return $date && $date->format($format) === $value ? $date : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function reportPeriodLabel(string $type, Carbon $start, Carbon $end): string
    {
        return match ($type) {
            'day' => __('messages.report_period_day_label', ['date' => $start->format('d/m/Y')]),
            'week' => __('messages.report_period_week_label', [
                'start' => $start->format('d/m/Y'),
                'end' => $end->format('d/m/Y'),
            ]),
            'month' => __('messages.report_period_month_label', ['month' => ucfirst($start->translatedFormat('F Y'))]),
            default => __('messages.report_period_custom_label', [
                'start' => $start->format('d/m/Y'),
                'end' => $end->format('d/m/Y'),
            ]),
        };
    }

    private function normalizedReportFilter(string $type, Carbon $start, Carbon $end): array
    {
        return [
            'type' => $type,
            'day' => $start->format('Y-m-d'),
            'week' => $start->format('o-\\WW'),
            'month' => $start->format('Y-m'),
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
        ];
    }
}
