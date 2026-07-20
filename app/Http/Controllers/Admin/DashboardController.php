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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $start = now()->subDays(29)->startOfDay();

        $metrics = [
            'brands' => Brand::count(),
            'categories' => Category::count(),
            'subcategories' => Subcategory::count(),
            'childcategories' => CategoriasFilhas::count(),
            'active_products' => Product::where('status', 1)->count(),
            'products' => Product::count(),
            'published_blogs' => Blog::published()->count(),
            'customers' => User::where('user_type', '<>', 1)->count(),
            'orders' => Order::count(),
            'bancard_orders' => Order::whereIn('payment_method', ['bancard', 'bancard_v2'])->count(),
            'deposit_orders' => Order::where('payment_method', 'deposito')->count(),
            'whatsapp_orders' => Order::where('payment_method', 'whatsapp')->count(),
            'low_stock' => Product::where('status', 1)->where('stock', '>', 0)->where('stock', '<=', 5)->count(),
            'out_of_stock' => Product::where('status', 1)->where('stock', '<=', 0)->count(),
            'abandoned_carts' => AbandonedCart::where('status', 'abandoned')->count(),
            'contacts' => Contact::count(),
        ];

        $paymentMethods = Order::query()
            ->selectRaw("CASE WHEN payment_method IN ('bancard', 'bancard_v2') THEN 'Bancard' WHEN payment_method = 'deposito' THEN 'Depósito' WHEN payment_method = 'whatsapp' THEN 'WhatsApp' ELSE 'Outros' END AS label")
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('label')->pluck('total', 'label');

        $orderStatuses = Order::select('status', DB::raw('COUNT(*) AS total'))
            ->groupBy('status')->pluck('total', 'status');

        $recentOrders = Order::with('user')->latest()->limit(6)->get();
        $topProducts = Product::orderByDesc('views')->limit(6)->get(['id', 'name', 'external_name', 'views', 'stock']);

        $analyticsReady = Schema::hasTable('site_analytics_events');
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

        return view('admin.dashboard.index', compact(
            'metrics', 'analytics', 'analyticsReady', 'paymentMethods', 'orderStatuses', 'recentOrders',
            'topProducts', 'trafficLabels', 'trafficViews', 'trafficVisitors', 'topPages', 'topClicks', 'devices'
        ));
    }
}
