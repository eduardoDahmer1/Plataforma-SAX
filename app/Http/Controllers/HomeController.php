<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Blog;
use App\Models\Generalsetting;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 1. Configurações Gerais
        $settings = Cache::remember('general_settings', 600, fn() => Generalsetting::first());

        // 2. Tipos de destaques
        $highlightTypes = [
            'destaque', 'mais_vendidos', 'melhores_avaliacoes', 'super_desconto',
            'famosos', 'lancamentos', 'tendencias', 'promocoes',
            'ofertas_relampago', 'navbar'
        ];

        // 3. Busca produtos destacados
        $highlights = [];
        foreach ($highlightTypes as $key) {
            $highlights[$key] = Cache::remember("highlight_products_{$key}", 600, function () use ($key) {
                return Product::where("highlights->{$key}", "1")
                    ->with('brand')
                    ->take(5)
                    ->get();
            });
        }

        // 4. Categorias para o "Category Strip"
        $categoriesStrip = Cache::remember('categories_home_strip_v4', 600, function () {
            $targetSlugs = ['feminino', 'masculino', 'infantil', 'optico', 'casa'];
            return Category::select('id', 'name', 'slug', 'photo')
                ->whereIn('slug', $targetSlugs)
                ->orderByRaw("FIELD(slug, 'feminino', 'masculino', 'infantil', 'optico', 'casa')")
                ->get();
        });

        // 5. CORREÇÃO: Marcas específicas para o "Slider 3D" (Baseado na sua imagem)
        // Mudei o nome da chave do cache para 'home_brands_3d_v2' para forçar a atualização
        $brandsSlider = Cache::remember('home_brands_3d_v2', 600, function () {
            // Nomes exatos conforme sua pasta de imagens
            $selectedNames = [
                'Baccarat',
                'Boss',
                'Celine',
                'JW-PEI',
                'Paul & Shark',
                'Stokke',
                'Valentino',
                'Veja',
                'Vilebrequin',
                'Zadig&Voltaire'
            ];

            return Brand::select('id', 'name', 'slug', 'image', 'banner')
                ->whereIn('name', $selectedNames)
                ->where('status', 1)
                ->orderByRaw("FIELD(name, '".implode("','", $selectedNames)."')")
                ->get();
        });

        // 6. Dados para Filtros e Menus
        $allCategories = Cache::remember(
            'categories_all',
            600,
            fn() =>
            Category::selectRaw("id, COALESCE(NULLIF(name,''),slug) as name, slug")->orderBy('name')->get()
        );

        // 7. Blog
        $blogs = Cache::remember('home_blogs', 600, fn() => Blog::latest()->take(9)->get());

        // 8. Carrinho
        $cartItems = auth()->check()
            ? Cart::where('user_id', auth()->id())->pluck('quantity', 'product_id')->toArray()
            : [];

        return view('home', [
            'settings'        => $settings,
            'highlights'      => $highlights,
            'categories'      => $categoriesStrip,
            'allCategories'   => $allCategories,
            'brands'          => $brandsSlider, // Enviando as marcas filtradas
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