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
        // Limpa caches antigos de imagens que deram erro (opcional, remova após testar)
        // Cache::forget('categories_home_strip');
        // Cache::forget('home_brands_3d');

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

        // 4. Categorias para o "Category Strip" (Ajustado para pegar as 5 principais)
        $categoriesStrip = Cache::remember('categories_home_strip', 600, function () {
            // Removido o whereNotNull para debugar; se não tiver imagem, o Blade tratará
            return Category::select('id', 'name', 'slug', 'image')
                ->whereHas('products')
                ->orderBy('id', 'desc') // Pega as mais recentes ou ajuste conforme preferir
                ->take(5)
                ->get();
        });

        // 5. Marcas para o "Slider 3D"
        $brands = Cache::remember('home_brands_3d', 600, function () {
            return Brand::select('id', 'name', 'slug', 'image')
                ->whereHas('products')
                ->take(10)
                ->get();
        });

        // 6. Dados para Filtros e Menus
        $allCategories = Cache::remember('categories_all', 600, fn() =>
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
            'brands'          => $brands,
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