<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Blog;
use App\Models\Generalsetting;
use App\Models\Attribute;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 1. Configurações Gerais
        $settings = Cache::remember('general_settings', 600, fn() => Generalsetting::first());

        // 2. Atributos do Sistema
        $attribute = Cache::remember('system_attributes', 600, fn() => Attribute::first());

        // 3. Tipos de destaques MANUAIS (Removido 'lancamentos' daqui pois agora é automático)
        $highlightTypes = [
            'destaque', 'mais_vendidos', 'melhores_avaliacoes', 'super_desconto',
            'famosos', 'tendencias', 'promocoes', 'ofertas_relampago', 'navbar'
        ];

        // 4. Busca produtos destacados (Manuais via JSON)
        $highlights = [];
        foreach ($highlightTypes as $key) {
            $highlights[$key] = Cache::remember("highlight_products_{$key}", 600, function () use ($key) {
                return Product::where("highlights->{$key}", "1")
                    ->where('product_role', 'P')
                    ->whereNotNull('photo')
                    ->where('photo', '!=', '')
                    ->with('brand')
                    ->take(5)
                    ->get();
            });
        }

        // --- NOVO: 4.1 LANÇAMENTOS (Produtos Editados Recentemente) ---
        // Agora busca os últimos que foram salvos/editados no Admin
        $lancamentos = Cache::remember('home_products_updated_at', 600, function () {
            return Product::where('status', 1)
                ->where('product_role', 'P')
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->with('brand')
                ->orderBy('updated_at', 'DESC') // Ordem pelos editados recentemente
                ->take(12)
                ->get();
        });

        // --- 4.2 MAIS VISTOS ---
        $mostViewed = Cache::remember('home_most_viewed_products', 600, function () {
            return Product::where('status', 1)
                ->where('views', '>', 0)
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->with('brand')
                ->orderBy('views', 'DESC')
                ->take(12)
                ->get();
        });

        // 5. Categorias Strip
        $categoriesStrip = Cache::remember('categories_home_strip_v4', 600, function () {
            $targetSlugs = ['feminino', 'masculino', 'infantil', 'optico', 'casa'];
            return Category::select('id', 'name', 'slug', 'photo')
                ->whereIn('slug', $targetSlugs)
                ->orderByRaw("FIELD(slug, 'feminino', 'masculino', 'infantil', 'optico', 'casa')")
                ->get();
        });

        // 6. Marcas Slider 3D
        $brandsSlider = Cache::remember('home_brands_3d_v2', 600, function () {
            $selectedNames = [
                'Baccarat', 'Boss', 'Celine', 'JW-PEI', 'Paul & Shark',
                'Stokke', 'Valentino', 'Veja', 'Vilebrequin', 'Zadig&Voltaire'
            ];
            return Brand::select('id', 'name', 'slug', 'image', 'banner')
                ->whereIn('name', $selectedNames)
                ->where('status', 1)
                ->orderByRaw("FIELD(name, '".implode("','", $selectedNames)."')")
                ->get();
        });

        // 7. Dados Gerais
        $allCategories = Cache::remember('categories_all', 600, fn() =>
            Category::selectRaw("id, COALESCE(NULLIF(name,''),slug) as name, slug")->orderBy('name')->get()
        );

        $blogs = Cache::remember('home_blogs', 600, fn() => Blog::latest()->take(9)->get());

        $cartItems = auth()->check()
            ? Cart::where('user_id', auth()->id())->pluck('quantity', 'product_id')->toArray()
            : [];

        return view('home', [
            'settings'        => $settings,
            'attribute'       => $attribute,
            'highlights'      => $highlights,
            'lancamentos'     => $lancamentos, // Enviando a nova query automática
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
        ]);
    }
}