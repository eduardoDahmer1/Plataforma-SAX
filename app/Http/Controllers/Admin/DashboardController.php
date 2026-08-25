<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\CategoriasFilhas;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteAnalyticsEvent;
use App\Models\Subcategory;
use App\Models\User;
use App\Models\BusinessEvent;
use App\Models\IntegrationMonitor;
use App\Models\IntegrationRun;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
                'COUNT(*) AS total, '
                ."SUM(CASE WHEN payment_method = 'bancard_v2' THEN 1 ELSE 0 END) AS bancard_total, "
                ."SUM(CASE WHEN payment_method = 'rendix_pix' THEN 1 ELSE 0 END) AS pix_total, "
                ."SUM(CASE WHEN payment_method = 'deposito' THEN 1 ELSE 0 END) AS deposit_total, "
                ."SUM(CASE WHEN payment_method = 'whatsapp' THEN 1 ELSE 0 END) AS whatsapp_total"
            )->first();

            return [
                'brands' => Brand::count(),
                'categories' => Category::count(),
                'subcategories' => Subcategory::count(),
                'childcategories' => CategoriasFilhas::count(),
                'active_products' => (int) $productMetrics->active_total,
                'products' => (int) $productMetrics->total,
                'published_blogs' => Blog::published()->count(),
                'customers' => User::whereNotIn('user_type', [User::TYPE_ADMIN_MASTER, User::TYPE_ADMIN_EDITOR])->count(),
                'orders' => (int) $orderMetrics->total,
                'bancard_orders' => (int) $orderMetrics->bancard_total,
                'pix_orders' => (int) $orderMetrics->pix_total,
                'deposit_orders' => (int) $orderMetrics->deposit_total,
                'whatsapp_orders' => (int) $orderMetrics->whatsapp_total,
                'low_stock' => (int) $productMetrics->low_stock,
                'out_of_stock' => (int) $productMetrics->out_of_stock,
                'abandoned_carts' => AbandonedCart::where('status', 'abandoned')->count(),
                'contacts' => Contact::count(),
            ];
        });

        $paymentMethods = Order::query()
            ->select('payment_method', DB::raw('COUNT(*) AS total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->mapWithKeys(fn ($total, $method) => [[
                'bancard_v2' => 'Bancard V2',
                'rendix_pix' => 'Pix Rendix',
                'deposito' => __('messages.payment_deposit'),
                'whatsapp' => 'WhatsApp',
            ][$method] ?? __('messages.payment_other') => $total]);

        $orderStatuses = Order::select('status', DB::raw('COUNT(*) AS total'))
            ->groupBy('status')->pluck('total', 'status');

        $recentOrders = Order::with('user')->latest()->limit(6)->get();
        $topProducts = Product::orderByDesc('views')->limit(6)->get(['id', 'name', 'external_name', 'views', 'stock']);

        $analyticsReady = Cache::remember('schema.site_analytics_events', 3600, fn () => Schema::hasTable('site_analytics_events'));
        $analytics = [
            'views_today' => 0,
            'visitors_today' => 0,
            'clicks_today' => 0,
            'views_30_days' => 0,
        ];
        $trafficLabels = [];
        $trafficViews = [];
        $trafficVisitors = [];
        $topPages = collect();
        $topClicks = collect();
        $devices = collect();
        $businessEventsReady = Cache::remember('schema.business_events', 3600, fn () => Schema::hasTable('business_events'));
        $businessEvents = $businessEventsReady
            ? BusinessEvent::with(['user:id,name,email', 'order:id,order_number'])
                ->latest()->limit(12)->get()
            : collect();
        $integrationMonitoringReady = Cache::remember('schema.integration_monitoring', 3600, fn () =>
            Schema::hasTable('integration_monitors') && Schema::hasTable('integration_runs')
        );
        $integrationEndpointConfigured = filled(config('services.integration_monitor.token'));
        $integrationMonitor = $integrationMonitoringReady
            ? IntegrationMonitor::where('source', 'catalog')->first()
            : null;
        $integrationRuns = $integrationMonitor
            ? IntegrationRun::where('integration_monitor_id', $integrationMonitor->id)
                ->latest('started_at')
                ->limit(5)
                ->get()
            : collect();

        if ($analyticsReady) {
            $analytics['views_today'] = SiteAnalyticsEvent::where('event_type', 'page_view')->where('event_date', $today)->count();
            $analytics['visitors_today'] = SiteAnalyticsEvent::where('event_type', 'page_view')->where('event_date', $today)->distinct('visitor_hash')->count('visitor_hash');
            $analytics['clicks_today'] = SiteAnalyticsEvent::where('event_type', 'click')->where('event_date', $today)->count();
            $analytics['views_30_days'] = SiteAnalyticsEvent::where('event_type', 'page_view')->where('event_date', '>=', $start->toDateString())->count();

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
            $devices = SiteAnalyticsEvent::where('event_type', 'page_view')->where('event_date', '>=', $start->toDateString())
                ->whereNotNull('device_type')->select('device_type', DB::raw('COUNT(*) AS total'))
                ->groupBy('device_type')->pluck('total', 'device_type');
        }

        $reportSelection = $this->resolveReportPeriod($request);
        $selectedReport = $this->buildReport(
            $reportSelection['start'],
            $reportSelection['end'],
            $reportSelection['label']
        );
        $reportFilter = $reportSelection['filter'];

        return view('admin.dashboard.index', compact(
            'metrics', 'analytics', 'analyticsReady', 'paymentMethods', 'orderStatuses', 'recentOrders',
            'topProducts', 'trafficLabels', 'trafficViews', 'trafficVisitors', 'topPages', 'topClicks', 'devices', 'businessEvents',
            'integrationMonitor', 'integrationRuns', 'integrationMonitoringReady', 'integrationEndpointConfigured',
            'selectedReport', 'reportFilter'
        ));
    }

    public function report(Request $request, ?string $period = null): Response
    {
        $selection = $this->resolveReportPeriod($request, $period);
        $report = $this->buildReport($selection['start'], $selection['end'], $selection['label']);
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

    private function buildReport(Carbon $start, Carbon $end, string $label): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end]);
        $paidOrders = Order::whereBetween('created_at', [$start, $end])
            ->where(fn ($query) => $query->where('payment_status', 'paid')->orWhere('status', 'paid'));
        $analyticsReady = Schema::hasTable('site_analytics_events');
        $countryTrackingReady = $analyticsReady && Schema::hasColumn('site_analytics_events', 'country_code');

        $visitorsByCountry = $countryTrackingReady
            ? SiteAnalyticsEvent::where('event_type', 'page_view')
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw("COALESCE(country_code, 'XX') AS country_code")
                ->selectRaw("COALESCE(country_name, ?) AS country_name", [__('messages.report_country_unknown')])
                ->selectRaw('COUNT(DISTINCT visitor_hash) AS visitors')
                ->groupBy('country_code', 'country_name')
                ->orderByDesc('visitors')
                ->get()
            : collect();

        return [
            'period' => $label,
            'start' => $start->copy(),
            'end' => $end->copy(),
            'orders' => (clone $orders)->count(),
            'paid_orders' => (clone $paidOrders)->count(),
            'sales_total' => (float) (clone $paidOrders)->sum('total'),
            'new_customers' => User::whereNotIn('user_type', [User::TYPE_ADMIN_MASTER, User::TYPE_ADMIN_EDITOR])->whereBetween('created_at', [$start, $end])->count(),
            'abandoned_carts' => AbandonedCart::whereBetween('abandoned_at', [$start, $end])->count(),
            'views' => $analyticsReady ? SiteAnalyticsEvent::where('event_type', 'page_view')->whereBetween('created_at', [$start, $end])->count() : 0,
            'visitors' => $analyticsReady ? SiteAnalyticsEvent::where('event_type', 'page_view')->whereBetween('created_at', [$start, $end])->distinct('visitor_hash')->count('visitor_hash') : 0,
            'clicks' => $analyticsReady ? SiteAnalyticsEvent::where('event_type', 'click')->whereBetween('created_at', [$start, $end])->count() : 0,
            'visitors_by_country' => $visitorsByCountry,
            'payment_methods' => (clone $orders)->select('payment_method', DB::raw('COUNT(*) total'))->groupBy('payment_method')->pluck('total', 'payment_method'),
        ];
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
