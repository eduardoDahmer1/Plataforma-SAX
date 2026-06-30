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
            ->where('product_role', 'P')
            ->where('stock', '>', 0)
            ->whereNotNull('photo')
            ->where('photo', '!=', '')
            ->with('brand');
    }

    public function index(Request $request)
    {
        $settings  = Cache::remember('general_settings',  600, fn() => Generalsetting::first());
        $attribute = Cache::remember('system_attributes', 600, fn() => Attribute::first());

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

        $mostViewed = Cache::remember(
            'home_most_viewed_products_' . now()->format('Y_W'),
            now()->addDays(7),
            fn() => $this->activeProducts()
                ->where('views', '>', 0)
                ->orderBy('views', 'desc')
                ->limit(12)
                ->get()
        );

        $categoriesStrip = Cache::remember('categories_home_strip_random_15min', 900,
            fn() => Category::select('id', 'name', 'slug', 'photo')
                ->where('status', 1)->inRandomOrder()->take(5)->get()
        );

        $brandsSlider = Cache::remember('home_brands_3d_random_15min', 900,
            fn() => Brand::select('id', 'name', 'slug', 'image', 'banner')
                ->where('status', 1)
                ->whereNotNull('image')->where('image', '!=', '')
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
            'banner1'         => $settings->banner1 ?? null,
            'banner2'         => $settings->banner2 ?? null,
            'banner3'         => $settings->banner3 ?? null,
            'banner4'         => $settings->banner4 ?? null,
            'banner5'         => $settings->banner5 ?? null,
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
