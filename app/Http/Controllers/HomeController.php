<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Generalsetting;
use App\Models\HomeBanner;
use App\Services\DailyMostViewedProducts;
use App\Services\StorefrontLayoutService;
use App\Services\VisibleCatalogProductsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    private function activeProducts()
    {
        return VisibleCatalogProductsService::builder()
            ->with(['brand', 'translations', 'category']);
    }

    private function weeklyRotatedHomeBanners(?Attribute $attribute): array
    {
        if (! $attribute) {
            return [];
        }

        $weekSeed = now()->startOfWeek()->format('o-W');
        $attributeSignature = optional($attribute->updated_at)?->timestamp ?? 'no-update';

        return Cache::remember(
            "home_banner_rotation_{$weekSeed}_{$attributeSignature}",
            now()->endOfWeek(),
            function () use ($attribute, $weekSeed) {
                $available = collect(range(1, 9))
                    ->map(function ($index) use ($attribute) {
                        return [
                            'origin' => $index,
                            'image' => $attribute->{"banner{$index}"} ?? null,
                            'link' => $attribute->{"banner{$index}_link"} ?? null,
                        ];
                    })
                    ->filter(fn ($banner) => filled($banner['image']))
                    ->sortBy(fn ($banner) => sha1($weekSeed.'|'.$banner['origin'].'|'.$banner['image']))
                    ->values();

                $result = [];
                foreach (range(1, 9) as $position) {
                    $entry = $available->get($position - 1);
                    $result["banner{$position}"] = $entry['image'] ?? null;
                    $result["banner{$position}_link"] = $entry['link'] ?? null;
                }

                return $result;
            }
        );
    }

    public function index(Request $request, DailyMostViewedProducts $dailyMostViewedProducts, StorefrontLayoutService $layouts)
    {
        $settings = Cache::remember('general_settings', 600, fn () => Generalsetting::first());
        $homeSections = $settings?->resolvedHomeSections() ?? Generalsetting::defaultHomeSections();
        $categoryLimit = (string) ($homeSections['categories']['category_limit'] ?? 'all');
        $attribute = Cache::remember('system_attributes', 600, fn () => Attribute::first());
        $weeklyBanners = $this->weeklyRotatedHomeBanners($attribute);
        $homeBannerGroups = Cache::remember('home_banners_active_v1', 600, fn () => HomeBanner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('group'));

        $highlightTypes = [
            'destaque', 'mais_vendidos', 'melhores_avaliacoes', 'super_desconto',
            'famosos', 'tendencias', 'promocoes', 'ofertas_relampago', 'navbar',
        ];

        $highlights = [];
        foreach ($highlightTypes as $key) {
            $highlights[$key] = Cache::remember(
                "highlight_products_visible_catalog_v2_{$key}_".now()->format('Y_W'),
                now()->addDays(7),
                fn () => $this->activeProducts()
                    ->where("highlights->{$key}", '1')
                    ->inRandomOrder()
                    ->limit(15)
                    ->get()
            );
        }

        $lancamentos = Cache::remember('home_products_visible_catalog_v2_updated_at', 600,
            fn () => $this->activeProducts()->orderBy('updated_at', 'desc')->take(12)->get()
        );

        $mostViewed = $dailyMostViewedProducts->get(12);

        $categoriesStrip = Cache::remember("categories_home_strip_{$categoryLimit}_v2", 900,
            fn () => Category::select('id', 'name', 'slug', 'photo')
                ->where('status', 1)
                ->orderBy('name')
                ->when($categoryLimit !== 'all', fn ($query) => $query->limit((int) $categoryLimit))
                ->get()
        );

        $brandsSlider = Cache::remember('home_brands_visible_catalog_v5_carousel_15min', 900,
            fn () => Brand::select('id', 'name', 'slug', 'image', 'home_carousel_image')
                ->where('status', 1)
                ->whereIn('id', VisibleCatalogProductsService::builder()->select('products.brand_id'))
                ->where(function ($query) {
                    $query->whereNotNull('home_carousel_image')->where('home_carousel_image', '<>', '')
                        ->orWhere(function ($fallback) {
                            $fallback->whereNotNull('image')->where('image', '<>', '');
                        });
                })
                ->inRandomOrder()
                ->take(10)
                ->get()
        );

        $allCategories = Cache::remember('categories_all', 600,
            fn () => Category::selectRaw("id, COALESCE(NULLIF(name,''),slug) as name, slug")
                ->where('status', 1)
                ->orderBy('name')->get()
        );

        $blogs = Cache::remember('home_blogs', 600, fn () => Blog::latest()->take(9)->get());
        $cartItems = auth()->check()
            ? Cart::where('user_id', auth()->id())->pluck('quantity', 'product_id')->toArray()
            : [];

        return view($layouts->homeView(), [
            'settings' => $settings,
            'homeSections' => $homeSections,
            'attribute' => $attribute,
            'highlights' => $highlights,
            'lancamentos' => $lancamentos,
            'mostViewed' => $mostViewed,
            'categories' => $categoriesStrip,
            'allCategories' => $allCategories,
            'brands' => $brandsSlider,
            'blogs' => $blogs,
            'cartItems' => $cartItems,
            'homeMainBanners' => $homeBannerGroups->get(HomeBanner::GROUP_MAIN, collect()),
            'homeEditorialBanners' => $homeBannerGroups->get(HomeBanner::GROUP_EDITORIAL, collect()),
            'banner1' => $weeklyBanners['banner1'] ?? null,
            'banner2' => $weeklyBanners['banner2'] ?? null,
            'banner3' => $weeklyBanners['banner3'] ?? null,
            'banner4' => $weeklyBanners['banner4'] ?? null,
            'banner5' => $weeklyBanners['banner5'] ?? null,
            'banner6' => $weeklyBanners['banner6'] ?? null,
            'banner7' => $weeklyBanners['banner7'] ?? null,
            'banner8' => $weeklyBanners['banner8'] ?? null,
            'banner9' => $weeklyBanners['banner9'] ?? null,
            'banner1_link' => $weeklyBanners['banner1_link'] ?? null,
            'banner2_link' => $weeklyBanners['banner2_link'] ?? null,
            'banner3_link' => $weeklyBanners['banner3_link'] ?? null,
            'banner4_link' => $weeklyBanners['banner4_link'] ?? null,
            'banner5_link' => $weeklyBanners['banner5_link'] ?? null,
            'banner6_link' => $weeklyBanners['banner6_link'] ?? null,
            'banner7_link' => $weeklyBanners['banner7_link'] ?? null,
            'banner8_link' => $weeklyBanners['banner8_link'] ?? null,
            'banner9_link' => $weeklyBanners['banner9_link'] ?? null,
            'whatsapp_banner' => $attribute->whatsapp_banner ?? null,
        ]);
    }

    public function storeNewsletter(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'contact_type' => 'required',
            'name' => 'required',
        ]);

        Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'contact_type' => $request->contact_type,
            'message' => 'Inscrição na Newsletter',
        ]);

        return redirect()->back()->with('success', 'Inscrição realizada com sucesso!');
    }
}
