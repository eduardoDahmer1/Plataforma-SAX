<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Cart;
use App\Models\Blog;
use App\Models\Generalsetting;
use App\Models\Attribute;
use App\Services\DailyMostViewedProducts;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    private const PRODUCT_BASE_SCOPES = [
        ['status', 1],
        ['product_role', 'P'],
        ['stock', '>', 0],
    ];

    private function activeProducts()
    {
        return Product::where('status', 1)
            ->where('is_outlet', false)
            ->where('product_role', 'P')
            ->where('stock', '>', 0)
            ->whereNotNull('photo')
            ->where('photo', '!=', '')
            ->with(['brand', 'translations']);
    }

    private function weeklyRotatedHomeBanners(?Attribute $attribute): array
    {
        if (!$attribute) {
            return [];
        }

        $weekSeed = now()->startOfWeek()->format('o-W');
        $attributeSignature = optional($attribute->updated_at)?->timestamp ?? 'no-update';

        return Cache::remember(
            "home_banner_rotation_{$weekSeed}_{$attributeSignature}",
            now()->endOfWeek(),
            function () use ($attribute, $weekSeed) {
                $available = collect(range(1, 10))
                    ->map(function ($index) use ($attribute) {
                        return [
                            'origin' => $index,
                            'image' => $attribute->{"banner{$index}"} ?? null,
                            'link' => $attribute->{"banner{$index}_link"} ?? null,
                        ];
                    })
                    ->filter(fn ($banner) => filled($banner['image']))
                    ->sortBy(fn ($banner) => sha1($weekSeed . '|' . $banner['origin'] . '|' . $banner['image']))
                    ->values();

                $result = [];
                foreach (range(1, 10) as $position) {
                    $entry = $available->get($position - 1);
                    $result["banner{$position}"] = $entry['image'] ?? null;
                    $result["banner{$position}_link"] = $entry['link'] ?? null;
                }

                return $result;
            }
        );
    }

    public function index(Request $request, DailyMostViewedProducts $dailyMostViewedProducts)
    {
        $settings  = Cache::remember('general_settings',  600, fn() => Generalsetting::first());
        $attribute = Cache::remember('system_attributes', 600, fn() => Attribute::first());
        $weeklyBanners = $this->weeklyRotatedHomeBanners($attribute);

        $highlightTypes = [
            'destaque', 'mais_vendidos', 'melhores_avaliacoes', 'super_desconto',
            'famosos', 'tendencias', 'promocoes', 'ofertas_relampago', 'navbar',
        ];

        $highlights = [];
        foreach ($highlightTypes as $key) {
            $highlights[$key] = Cache::remember(
                "highlight_products_{$key}_" . now()->format('Y_W'),
                now()->addDays(7),
                fn() => $this->activeProducts()
                    ->where("highlights->{$key}", '1')
                    ->inRandomOrder()
                    ->limit(15)
                    ->get()
            );
        }

        $lancamentos = Cache::remember('home_products_updated_at', 600,
            fn() => $this->activeProducts()->orderBy('updated_at', 'desc')->take(12)->get()
        );

        $mostViewed = $dailyMostViewedProducts->get(12);

        $categoriesStrip = Cache::remember('categories_home_strip_random_15min', 900,
            fn() => Category::select('id', 'name', 'slug', 'photo')
                ->where('status', 1)->inRandomOrder()->take(5)->get()
        );

        $brandsSlider = Cache::remember('home_brands_3d_random_15min', 900,
            fn() => Brand::select('id', 'name', 'slug', 'image', 'banner')
                ->where('status', 1)
                ->whereNotNull('image')->where('image', '!=', '')
                ->whereHas('products', fn($q) => $this->applyActiveProductScope($q))
                ->inRandomOrder()->take(10)->get()
        );

        $allCategories = Cache::remember('categories_all', 600,
            fn() => Category::selectRaw("id, COALESCE(NULLIF(name,''),slug) as name, slug")
                ->orderBy('name')->get()
        );

        $blogs     = Cache::remember('home_blogs', 600, fn() => Blog::latest()->take(9)->get());
        $cartItems = auth()->check()
            ? Cart::where('user_id', auth()->id())->pluck('quantity', 'product_id')->toArray()
            : [];

        return view('home', [
            'settings'        => $settings,
            'attribute'       => $attribute,
            'highlights'      => $highlights,
            'lancamentos'     => $lancamentos,
            'mostViewed'      => $mostViewed,
            'categories'      => $categoriesStrip,
            'allCategories'   => $allCategories,
            'brands'          => $brandsSlider,
            'blogs'           => $blogs,
            'cartItems'       => $cartItems,
            'banner1'         => $weeklyBanners['banner1'] ?? null,
            'banner2'         => $weeklyBanners['banner2'] ?? null,
            'banner3'         => $weeklyBanners['banner3'] ?? null,
            'banner4'         => $weeklyBanners['banner4'] ?? null,
            'banner5'         => $weeklyBanners['banner5'] ?? null,
            'banner6'         => $weeklyBanners['banner6'] ?? null,
            'banner7'         => $weeklyBanners['banner7'] ?? null,
            'banner8'         => $weeklyBanners['banner8'] ?? null,
            'banner9'         => $weeklyBanners['banner9'] ?? null,
            'banner10'        => $weeklyBanners['banner10'] ?? null,
            'banner1_link'    => $weeklyBanners['banner1_link'] ?? null,
            'banner2_link'    => $weeklyBanners['banner2_link'] ?? null,
            'banner3_link'    => $weeklyBanners['banner3_link'] ?? null,
            'banner4_link'    => $weeklyBanners['banner4_link'] ?? null,
            'banner5_link'    => $weeklyBanners['banner5_link'] ?? null,
            'banner6_link'    => $weeklyBanners['banner6_link'] ?? null,
            'banner7_link'    => $weeklyBanners['banner7_link'] ?? null,
            'banner8_link'    => $weeklyBanners['banner8_link'] ?? null,
            'banner9_link'    => $weeklyBanners['banner9_link'] ?? null,
            'banner10_link'   => $weeklyBanners['banner10_link'] ?? null,
            'whatsapp_banner' => $attribute->whatsapp_banner ?? null,
        ]);
    }

    public function storeNewsletter(Request $request)
    {
        $request->validate([
            'email'        => 'required|email|max:255',
            'contact_type' => 'required',
            'name'         => 'required',
        ]);

        Contact::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'contact_type' => $request->contact_type,
            'message'      => 'Inscrição na Newsletter',
        ]);

        return redirect()->back()->with('success', 'Inscrição realizada com sucesso!');
    }
}
