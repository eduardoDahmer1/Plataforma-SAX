<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;
use App\Models\Generalsetting;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Home: Exibe apenas produtos ativos (Role 'P')
     */
    public function home(Request $request)
    {
        $search  = $request->get('search');
        $page    = $request->get('page', 1);
        $perPage = 40;

        $cacheKey = "home_products_{$page}_" . md5($search ?? '');

        $items = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($search) {
            $columns = [
                'id', 'external_name', 'sku', 'price', 'photo', 
                'brand_id', 'category_id', 'created_at', 'slug', 'product_role'
            ];

            return Product::select($columns)
                ->where('product_role', 'P')
                ->when($search, function ($q) use ($search) {
                    $q->where('external_name', 'LIKE', "%{$search}%")
                        ->orWhere('sku', 'LIKE', "%{$search}%");
                })
                ->with(['cupons' => fn($q) => $q->ativos()])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($p) => (object)[
                    'id'          => $p->id,
                    'title'       => $p->external_name,
                    'description' => $p->sku,
                    'photo'       => $p->photo,
                    'price'       => $p->price,
                    'price_final' => $this->calcularPrecoComCupon($p),
                    'type'        => 'product',
                    'slug'        => $p->slug,
                    'created_at'  => $p->created_at,
                ]);
        });

        $pagedItems = $items->forPage($page, $perPage);

        return view('home', [
            'items'    => $pagedItems,
            'page'     => $page,
            'lastPage' => ceil($items->count() / $perPage),
        ]);
    }

    /**
     * Index de Produtos (Listagem Geral)
     */
    public function index(Request $request)
    {
        $search   = $request->get('search');
        $page     = $request->get('page', 1);
        $perPage  = 12;
        $cacheKey = "products_page_{$page}_" . md5($search ?? '');

        $products = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($search, $perPage) {
            return Product::with(['cupons' => fn($q) => $q->ativos()])
                ->where('product_role', 'P')
                ->when($search, fn($q) => $q->where('external_name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%"))
                ->orderByDesc('id')
                ->paginate($perPage);
        });

        foreach ($products as $p) {
            $p->price_final = $this->calcularPrecoComCupon($p);
        }

        return view('produtos.index', compact('products'));
    }

    public function show($id_or_slug)
    {
        // 1. Busca o produto principal com relacionamentos
        $product = Product::where('id', $id_or_slug)
            ->orWhere('slug', $id_or_slug)
            ->with(['brand', 'category', 'subcategory', 'categoriasfilhas'])
            ->firstOrFail();

        // --- LÓGICA DE TRAVA BRIDAL POR ID ---
        // IDs extraídos das suas consultas SQL no phpMyAdmin
        $bridalBrandIds = [
            641,  // Pronovias
            610,  // St. Patrick
            664,  // Rosa Clara
            1236, // Marchesa
            1237, // Vera Wang
            1444, // Ladybird
            951,  // Allure
            // Adicione aqui os IDs de Nicole Milano e White One quando ativos
        ];

        $isBridal = false;
        
        // Verifica por ID da Marca (Mais seguro que o nome)
        if ($product->brand_id && in_array($product->brand_id, $bridalBrandIds)) {
            $isBridal = true;
        }

        // Backup: Verifica por Categoria (slug ou nome contendo 'bridal')
        if (!$isBridal && $product->category && str_contains(strtolower($product->category->name), 'bridal')) {
            $isBridal = true;
        }
        // ------------------------------

        // Preços e Descontos
        $product->current_price = $product->promotion_price > 0 ? $product->promotion_price : $product->price;
        $product->has_discount = $product->previous_price > $product->current_price;

        // 2. Variantes de talla: el hijo `F` apunta al ancla vertical en `parent_id`.
        $masterId = (int) $product->id;
        if ($product->product_role === 'F' && !empty($product->parent_id)) {
            if (is_string($product->parent_id) && str_contains($product->parent_id, ',')) {
                $parentIds = array_values(array_filter(array_map('trim', explode(',', $product->parent_id))));
                $masterId = (int) ($parentIds[0] ?? $masterId);
            } else {
                $masterId = (int) $product->parent_id;
            }
        }

        $siblings = Product::where(function ($query) use ($masterId) {
                $query->where('parent_id', $masterId)
                    ->orWhere('id', $masterId);
            })
            ->where('status', 1)
            ->get();

        // 3. Familia de color: todos los colores de la misma familia apuntan al mismo ancla.
        $colorGroupId = !empty($product->color_parent_id)
            ? (int) $product->color_parent_id
            : (int) $product->id;

        $coresRelacionadas = Product::where(function ($query) use ($colorGroupId) {
                $query->where('color_parent_id', $colorGroupId)
                    ->orWhere('id', $colorGroupId);
            })
            ->where('status', 1)
            ->where('product_role', 'P')
            ->get();

        // 4. Atributos e Configurações com Cache
        $attribute = Cache::remember('system_attributes', 600, fn() => Attribute::first());
        $settings = Cache::remember('general_settings', 600, fn() => Generalsetting::first());

        // 5. Busca produtos destacados
        $highlightTypes = ['destaque', 'lancamentos'];
        $highlights = [];
        foreach ($highlightTypes as $key) {
            $highlights[$key] = Cache::remember("highlight_products_show_{$key}", 600, function () use ($key) {
                return Product::where("highlights->{$key}", "1")
                    ->where('product_role', 'P')
                    ->with('brand')
                    ->take(10)
                    ->get();
            });
        }

        return view('produtos.show', [
            'product'           => $product,
            'isBridal'          => $isBridal, // Variável para o Blade esconder preços/carrinho
            'siblings'          => $siblings,
            'coresRelacionadas' => $coresRelacionadas,
            'colorSiblings'     => $coresRelacionadas,
            'highlights'        => $highlights,
            'settings'          => $settings,
            'attribute'         => $attribute
        ]);
    }

    /**
     * Listagem por Categorias
     */
    public function byCategory(Category $category)
    {
        $products = Product::where('category_id', $category->id)
            ->where('product_role', 'P')
            ->with(['cupons' => fn($q) => $q->ativos()])
            ->paginate(12);

        foreach ($products as $p) {
            $p->price_final = $this->calcularPrecoComCupon($p);
        }

        return view('produtos.index', compact('products', 'category'));
    }

    /**
     * Listagem por Subcategorias
     */
    public function bySubcategory(Subcategory $subcategory)
    {
        $products = Product::where('subcategory_id', $subcategory->id)
            ->where('product_role', 'P')
            ->with(['cupons' => fn($q) => $q->ativos()])
            ->paginate(12);

        foreach ($products as $p) {
            $p->price_final = $this->calcularPrecoComCupon($p);
        }

        return view('produtos.index', compact('products', 'subcategory'));
    }

    /**
     * Listagem por CategoriasFilhas
     */
    public function byCategoriasFilhas(CategoriasFilhas $CategoriasFilhas)
    {
        $products = Product::where('childcategory_id', $CategoriasFilhas->id)
            ->where('product_role', 'P')
            ->with(['cupons' => fn($q) => $q->ativos()])
            ->paginate(12);

        foreach ($products as $p) {
            $p->price_final = $this->calcularPrecoComCupon($p);
        }

        return view('produtos.index', compact('products', 'CategoriasFilhas'));
    }

    /**
     * Helper: Calcula o preço considerando o melhor cupom ativo
     */
    private function calcularPrecoComCupon(Product $p)
    {
        $cupons = $p->cupons ?? collect();
        if ($cupons->isEmpty()) {
            return $p->price;
        }

        $descontoMax = $cupons->max(fn($c) => $c->tipo === 'percentual'
            ? $p->price * ($c->montante / 100)
            : $c->montante);

        return $descontoMax ? max(0, $p->price - $descontoMax) : $p->price;
    }
}