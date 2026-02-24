<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Cart;
use App\Models\Blog;
use App\Models\Generalsetting;
use App\Models\Attribute; // Importado para os ícones
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 1. Configurações Gerais
        $settings = Cache::remember('general_settings', 600, fn() => Generalsetting::first());

        // 2. Atributos do Sistema (Ícones: Cabide, Info, Ajuda)
        // Adicionado para resolver o erro no componente form-home.blade.php
        $attribute = Cache::remember('system_attributes', 600, fn() => Attribute::first());

        // 3. Tipos de destaques
        $highlightTypes = [
            'destaque', 'mais_vendidos', 'melhores_avaliacoes', 'super_desconto',
            'famosos', 'lancamentos', 'tendencias', 'promocoes',
            'ofertas_relampago', 'navbar'
        ];

        // 4. Busca produtos destacados
        $highlights = [];
        foreach ($highlightTypes as $key) {
            $highlights[$key] = Cache::remember("highlight_products_{$key}", 600, function () use ($key) {
                return Product::where("highlights->{$key}", "1")
                    ->with('brand')
                    ->take(5)
                    ->get();
            });
        }

        // 5. Categorias para o "Category Strip"
        $categoriesStrip = Cache::remember('categories_home_strip_v4', 600, function () {
            $targetSlugs = ['feminino', 'masculino', 'infantil', 'optico', 'casa'];
            return Category::select('id', 'name', 'slug', 'photo')
                ->whereIn('slug', $targetSlugs)
                ->orderByRaw("FIELD(slug, 'feminino', 'masculino', 'infantil', 'optico', 'casa')")
                ->get();
        });

        // 6. Marcas específicas para o "Slider 3D"
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

        // 7. Dados para Filtros e Menus
        $allCategories = Cache::remember(
            'categories_all',
            600,
            fn() =>
            Category::selectRaw("id, COALESCE(NULLIF(name,''),slug) as name, slug")->orderBy('name')->get()
        );

        // 8. Blog
        $blogs = Cache::remember('home_blogs', 600, fn() => Blog::latest()->take(9)->get());

        // 9. Carrinho
        $cartItems = auth()->check()
            ? Cart::where('user_id', auth()->id())->pluck('quantity', 'product_id')->toArray()
            : [];

        return view('home', [
            'settings'        => $settings,
            'attribute'       => $attribute, // Variável enviada para a View
            'highlights'      => $highlights,
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