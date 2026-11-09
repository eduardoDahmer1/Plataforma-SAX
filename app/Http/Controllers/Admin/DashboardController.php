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
use App\Models\IntegrationMonitor;
use App\Models\IntegrationRun;
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
        if ($request->user()?->isAdminEditor()) {
            return redirect()->route('admin.products.index');
        }

        return view('admin.dashboard.index');
    }

    public function orders(Request $request): View
    {
        $filters = [
            'period' => $this->dashboardPeriod($request),
            'payment_status' => $this->allowedFilter($request, 'payment_status', ['pending', 'paid', 'failed', 'refunded']),
            'status' => $this->allowedFilter($request, 'status', ['pending', 'processing', 'paid', 'shipped', 'completed', 'canceled', 'cancelled', 'failed']),
            'payment_method' => $this->allowedFilter($request, 'payment_method', ['bancard_v2', 'rendix_pix', 'deposito']),
        ];
        $cacheKey = 'admin.dashboard.orders.'.md5(json_encode($filters));

        $data = Cache::remember($cacheKey, now()->addSeconds(45), function () use ($filters): array {
            $orders = $this->filteredOrders($filters);
            $summary = (clone $orders)
                ->selectRaw('COUNT(*) AS total')
                ->selectRaw("SUM(CASE WHEN payment_status = 'paid' OR status = 'paid' THEN 1 ELSE 0 END) AS paid")
                ->selectRaw("SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) AS pending_payment")
                ->selectRaw("SUM(CASE WHEN status IN ('processing', 'shipped') THEN 1 ELSE 0 END) AS in_progress")
                ->selectRaw("SUM(CASE WHEN status IN ('canceled', 'cancelled', 'failed') THEN 1 ELSE 0 END) AS canceled")
                ->first();

            return [
                'summary' => $summary,
                'paymentMethods' => (clone $orders)->select('payment_method', DB::raw('COUNT(*) AS total'))
                    ->groupBy('payment_method')->orderByDesc('total')->pluck('total', 'payment_method'),
                'orderStatuses' => (clone $orders)->select('status', DB::raw('COUNT(*) AS total'))
                    ->groupBy('status')->orderByDesc('total')->pluck('total', 'status'),
                'recentOrders' => (clone $orders)->with('user:id,name')->latest()->limit(20)->get(),
            ];
        });
        $data['filters'] = $filters;
        $data['periodLabel'] = $this->dashboardPeriodLabel($filters['period']);

        return view('admin.dashboard.orders', $data);
    }

    public function integration(Request $request): View
    {
        $runStatus = $this->allowedFilter($request, 'run_status', ['running', 'success', 'failed']);
        if ($request->boolean('refresh')) {
            Cache::forget('admin.dashboard.integration.'.$runStatus);
        }

        $data = Cache::remember('admin.dashboard.integration.'.$runStatus, now()->addSeconds(30), function () use ($runStatus): array {
            $ready = Schema::hasTable('integration_monitors') && Schema::hasTable('integration_runs');
            $monitor = $ready ? IntegrationMonitor::where('source', 'catalog')->first() : null;
            $runs = collect();
            if ($monitor) {
                $runsQuery = IntegrationRun::where('integration_monitor_id', $monitor->id);
                if ($runStatus !== 'all') {
                    $runsQuery->where('status', $runStatus);
                }
                $runs = $runsQuery->latest('started_at')->limit(15)->get();
            }

            return [
                'integrationMonitoringReady' => $ready,
                'integrationEndpointConfigured' => filled(config('services.integration_monitor.token')),
                'integrationMonitor' => $monitor,
                'integrationRuns' => $runs,
            ];
        });
        $data['runStatus'] = $runStatus;

        return view('admin.dashboard.integration', $data);
    }

    public function users(Request $request): View
    {
        $filters = [
            'period' => $this->dashboardPeriod($request),
            'verification' => $this->allowedFilter($request, 'verification', ['verified', 'unverified']),
            'country' => mb_substr(trim($request->string('country')->toString()), 0, 100),
            'q' => mb_substr(trim($request->string('q')->toString()), 0, 100),
        ];

        $data = Cache::remember('admin.dashboard.users.'.md5(json_encode($filters)), now()->addMinute(), function () use ($filters): array {
            $customers = User::query()->whereNotIn('user_type', [User::TYPE_ADMIN_MASTER, User::TYPE_ADMIN_EDITOR]);
            $filtered = clone $customers;
            $this->applyCreatedPeriod($filtered, $filters['period']);
            if ($filters['verification'] === 'verified') {
                $filtered->whereNotNull('email_verified_at');
            } elseif ($filters['verification'] === 'unverified') {
                $filtered->whereNull('email_verified_at');
            }
            if ($filters['country'] !== '') {
                $filtered->where('country', $filters['country']);
            }
            if ($filters['q'] !== '') {
                $search = '%'.$filters['q'].'%';
                $filtered->where(fn ($query) => $query->where('name', 'like', $search)->orWhere('email', 'like', $search));
            }

            return [
                'totalCustomers' => (clone $customers)->count(),
                'filteredCustomers' => (clone $filtered)->count(),
                'verifiedCustomers' => (clone $filtered)->whereNotNull('email_verified_at')->count(),
                'unverifiedCustomers' => (clone $filtered)->whereNull('email_verified_at')->count(),
                'recentCustomers' => (clone $filtered)->latest()->limit(20)->get(['id', 'name', 'email', 'country', 'email_verified_at', 'created_at']),
                'customerCountries' => (clone $filtered)
                    ->selectRaw("COALESCE(NULLIF(country, ''), 'Não informado') AS country_label")
                    ->selectRaw('COUNT(*) AS total')
                    ->groupBy('country_label')->orderByDesc('total')->limit(8)->get(),
                'countryOptions' => (clone $customers)->whereNotNull('country')->where('country', '<>', '')
                    ->distinct()->orderBy('country')->limit(100)->pluck('country'),
            ];
        });
        $data['filters'] = $filters;
        $data['periodLabel'] = $this->dashboardPeriodLabel($filters['period']);

        return view('admin.dashboard.users', $data);
    }

    public function catalog(Request $request): View
    {
        $filters = [
            'status' => $this->allowedFilter($request, 'status', ['active', 'inactive']),
            'stock' => $this->allowedFilter($request, 'stock', ['healthy', 'low', 'out']),
            'sort' => $this->allowedFilter($request, 'sort', ['views_desc', 'stock_asc', 'recent'], 'views_desc'),
            'q' => mb_substr(trim($request->string('q')->toString()), 0, 100),
        ];

        $data = Cache::remember('admin.dashboard.catalog.'.md5(json_encode($filters)), now()->addMinute(), function () use ($filters): array {
            $productMetrics = Product::query()->selectRaw(
                'COUNT(*) AS total, '
                .'SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active_total, '
                .'SUM(CASE WHEN status = 1 AND stock BETWEEN 1 AND 5 THEN 1 ELSE 0 END) AS low_stock, '
                .'SUM(CASE WHEN status = 1 AND stock <= 0 THEN 1 ELSE 0 END) AS out_of_stock'
            )->first();

            $products = Product::query();
            if ($filters['status'] === 'active') {
                $products->where('status', 1);
            } elseif ($filters['status'] === 'inactive') {
                $products->where('status', 0);
            }
            if ($filters['stock'] === 'healthy') {
                $products->where('stock', '>', 5);
            } elseif ($filters['stock'] === 'low') {
                $products->whereBetween('stock', [1, 5]);
            } elseif ($filters['stock'] === 'out') {
                $products->where('stock', '<=', 0);
            }
            if ($filters['q'] !== '') {
                $search = '%'.$filters['q'].'%';
                $products->where(fn ($query) => $query->where('name', 'like', $search)
                    ->orWhere('external_name', 'like', $search)->orWhere('sku', 'like', $search));
            }
            match ($filters['sort']) {
                'stock_asc' => $products->orderBy('stock'),
                'recent' => $products->latest(),
                default => $products->orderByDesc('views'),
            };

            return [
                'productMetrics' => $productMetrics,
                'brandsCount' => Brand::count(),
                'categoriesCount' => Category::count(),
                'filteredProducts' => (clone $products)->count(),
                'topProducts' => $products->limit(20)->get(['id', 'sku', 'name', 'external_name', 'views', 'stock', 'status']),
                'lowStockProducts' => Product::query()->where('status', 1)->where('stock', '<=', 5)
                    ->orderBy('stock')->limit(10)->get(['id', 'name', 'external_name', 'stock']),
            ];
        });
        $data['filters'] = $filters;

        return view('admin.dashboard.catalog', $data);
    }

    public function metrics(Request $request): View
    {
        $days = $this->dashboardAnalyticsDays($request);
        $start = now()->subDays($days - 1)->startOfDay();
        $analyticsReady = Cache::remember('schema.site_analytics_events', 3600, fn () => Schema::hasTable('site_analytics_events'));
        $analytics = [
            'views' => 0,
            'visitors' => 0,
            'clicks' => 0,
            'pages_per_visitor' => 0,
        ];
        $devices = collect();
        $screenSizes = collect();
        if ($analyticsReady) {
            $summary = SiteAnalyticsEvent::query()
                ->where('event_date', '>=', $start->toDateString())
                ->selectRaw('SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) AS views', ['page_view'])
                ->selectRaw('COUNT(DISTINCT CASE WHEN event_type = ? THEN visitor_hash END) AS visitors', ['page_view'])
                ->selectRaw('SUM(CASE WHEN event_type = ? THEN 1 ELSE 0 END) AS clicks', ['click'])
                ->first();

            $views = (int) $summary->views;
            $visitors = (int) $summary->visitors;
            $analytics = [
                'views' => $views,
                'visitors' => $visitors,
                'clicks' => (int) $summary->clicks,
                'pages_per_visitor' => $visitors > 0 ? round($views / $visitors, 2) : 0,
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

        return view('admin.dashboard.metrics', compact('analytics', 'analyticsReady', 'devices', 'screenSizes', 'days'));
    }

    public function reports(Request $request): View
    {
        $selection = $this->resolveReportPeriod($request);
        $selectedReport = $this->buildReport($selection['start'], $selection['end'], $selection['label'], false);
        $reportFilter = $selection['filter'];

        return view('admin.dashboard.reports', compact('selectedReport', 'reportFilter'));
    }

    public function insights(Request $request): View
    {
        $days = $this->dashboardAnalyticsDays($request);
        $start = now()->subDays($days - 1)->startOfDay();
        $analyticsReady = Cache::remember('schema.site_analytics_events', 3600, fn () => Schema::hasTable('site_analytics_events'));

        $data = Cache::remember('admin.dashboard.insights.'.$days, now()->addMinute(), function () use ($analyticsReady, $start): array {
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
            ];
        });
        $data['days'] = $days;

        return view('admin.dashboard.partials.insights', $data);
    }

    private function dashboardPeriod(Request $request): string
    {
        return in_array($request->string('period')->toString(), ['7', '30', '90', 'all'], true)
            ? $request->string('period')->toString()
            : '30';
    }

    private function dashboardPeriodLabel(string $period): string
    {
        return match ($period) {
            '7' => 'Últimos 7 dias',
            '90' => 'Últimos 90 dias',
            'all' => 'Todo o histórico',
            default => 'Últimos 30 dias',
        };
    }

    private function dashboardAnalyticsDays(Request $request): int
    {
        $days = (int) $request->integer('days', 30);

        return in_array($days, [7, 30, 90], true) ? $days : 30;
    }

    private function allowedFilter(Request $request, string $key, array $allowed, string $default = 'all'): string
    {
        $value = $request->string($key)->toString();

        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function applyCreatedPeriod($query, string $period): void
    {
        if ($period !== 'all') {
            $query->where('created_at', '>=', now()->subDays(((int) $period) - 1)->startOfDay());
        }
    }

    private function filteredOrders(array $filters)
    {
        $orders = Order::query();
        $this->applyCreatedPeriod($orders, $filters['period']);
        foreach (['payment_status', 'status', 'payment_method'] as $field) {
            if ($filters[$field] !== 'all') {
                $orders->where($field, $filters[$field]);
            }
        }

        return $orders;
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
