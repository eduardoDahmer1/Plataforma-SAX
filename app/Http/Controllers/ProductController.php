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
use Illuminate\Support\Facades\Session;

class ProductController extends Controller
{
    /**
     * Home: Exibe produtos ativos e a nova seção de MAIS VISTOS
     */
    public function home(Request $request)
    {
        $search = $request->get('search');
        $page = $request->get('page', 1);
        $perPage = 40;

        // 1. Busca Configurações e Categorias (Necessário para a sua View)
        $settings = Cache::remember('general_settings', 600, fn() => \App\Models\Generalsetting::first());
        $categories = Cache::remember('home_categories', 600, fn() => \App\Models\Category::where('status', 1)->get());

        // 2. Busca Destaques e Lançamentos (Necessário para a sua View)
        $highlightTypes = ['destaque', 'lancamentos'];
        $highlights = [];
        foreach ($highlightTypes as $key) {
            $highlights[$key] = Cache::remember("highlight_products_home_{$key}", 600, function () use ($key) {
                return Product::where("highlights->{$key}", '1')
                    ->where('product_role', 'P')
                    ->where('status', 1)
                    ->with('brand')
                    ->take(10)
                    ->get();
            });
        }

        // 3. Listagem Geral de Produtos (com Cache dinâmico por página/busca)
        $cacheKey = "home_products_list_{$page}_" . md5($search ?? '');
        $items = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($search) {
            $columns = ['id', 'external_name', 'sku', 'price', 'photo', 'brand_id', 'category_id', 'created_at', 'slug', 'product_role', 'views', 'stock'];

            return Product::select($columns)
                ->where('product_role', 'P')
                ->where('status', 1)
                ->when($search, function ($q) use ($search) {
                    $q->where('external_name', 'LIKE', "%{$search}%")->orWhere('sku', 'LIKE', "%{$search}%");
                })
                ->with(['cupons' => fn($q) => $q->ativos(), 'brand'])
                ->orderByDesc('created_at')
                ->get();
        });

        // 4. LÓGICA CORRIGIDA: PRODUTOS MAIS VISTOS
        // Alterei a chave para 'v2' para forçar a atualização do cache
        $mostViewed = Cache::remember('products_most_viewed_v2', now()->addMinutes(10), function () {
            return Product::where('product_role', 'P')
                ->where('status', 1)
                ->where('views', '>', 0) // Garante que traz apenas quem tem visualizações
                ->with(['brand', 'cupons' => fn($q) => $q->ativos()])
                ->orderByDesc('views')
                ->take(10)
                ->get();
        });

        $pagedItems = $items->forPage($page, $perPage);

        return view('home', [
            'items' => $pagedItems,
            'mostViewed' => $mostViewed, // Variável crucial para a nova seção
            'settings' => $settings,
            'categories' => $categories,
            'highlights' => $highlights,
            'page' => $page,
            'lastPage' => ceil($items->count() / $perPage),
        ]);
    }

    /**
     * Index de Produtos (Listagem Geral)
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $page = $request->get('page', 1);
        $perPage = 12;
        $cacheKey = "products_page_{$page}_" . md5($search ?? '');

        $products = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($search, $perPage) {
            return Product::with(['cupons' => fn($q) => $q->ativos()])
                ->where('product_role', 'P')
                ->when(
                    $search,
                    fn($q) => $q
                        ->where('external_name', 'LIKE', "%{$search}%")
                        ->orWhere('sku', 'LIKE', "%{$search}%")
                        ->orWhere('slug', 'LIKE', "%{$search}%"),
                )
                ->orderByDesc('id')
                ->paginate($perPage);
        });

        foreach ($products as $p) {
            $p->price_final = $this->calcularPrecoComCupon($p);
        }

        return view('produtos.index', compact('products'));
    }

    /**
     * Exibe o produto e registra a visualização (Global e Individual por Usuário)
     */
    public function show($id_or_slug)
    {
        // 1. Busca o produto principal com as relações necessárias
        $product = Product::where('id', $id_or_slug)
            ->orWhere('slug', $id_or_slug)
            ->with(['brand', 'category', 'subcategory', 'categoriasfilhas'])
            ->firstOrFail();

        // --- LÓGICA DE REGISTRO DE VISUALIZAÇÃO GLOBAL ---
        $sessionKey = 'viewed_product_' . $product->id;
        if (!Session::has($sessionKey)) {
            // Incrementa o contador global de popularidade
            $product->increment('views');
            Session::put($sessionKey, true);
        }

        // --- NOVO: LÓGICA DE HISTÓRICO PERSISTENTE (PARA O PRÓPRIO USUÁRIO) ---
        if (auth()->check()) {
            // Registra ou atualiza o momento da visualização na tabela pivô
            \DB::table('product_views_history')->updateOrInsert(
                [
                    'user_id' => auth()->id(),
                    'product_id' => $product->id,
                ],
                [
                    'updated_at' => now(),
                    'created_at' => now(), // created_at é ignorado se o registro já existir no update
                ],
            );

            // Limpa o cache do histórico do usuário para que a Home atualize imediatamente
            Cache::forget('user_history_' . auth()->id());
        }

        // --- LÓGICA DE TRAVA BRIDAL ---
        $bridalBrandIds = [641, 610, 664, 1236, 1237, 1444, 951];
        $isBridal = false;

        if ($product->brand_id && in_array($product->brand_id, $bridalBrandIds)) {
            $isBridal = true;
        }

        if (!$isBridal && $product->category && str_contains(strtolower($product->category->name), 'bridal')) {
            $isBridal = true;
        }

        // Definição de preços e descontos
        $product->current_price = $product->promotion_price > 0 ? $product->promotion_price : $product->price;
        $product->has_discount = $product->previous_price > $product->current_price;

        // --- LÓGICA DE VARIANTES (Tamanho/Talla) ---
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
            $query->where('parent_id', $masterId)->orWhere('id', $masterId);
        })
            ->where('status', 1)
            ->get();

        // --- LÓGICA DE CORES RELACIONADAS ---
        $colorGroupId = !empty($product->color_parent_id) ? (int) $product->color_parent_id : (int) $product->id;

        $coresRelacionadas = Product::where(function ($query) use ($colorGroupId) {
            $query->where('color_parent_id', $colorGroupId)->orWhere('id', $colorGroupId);
        })
            ->where('status', 1)
            ->where('product_role', 'P')
            ->get();

        // --- ATRIBUTOS E CONFIGURAÇÕES ---
        $attribute = Cache::remember('system_attributes', 600, fn() => Attribute::first());
        $settings = Cache::remember('general_settings', 600, fn() => Generalsetting::first());

        // --- PRODUTOS SIMILARES E MAIS VISTOS (Geral) ---
        $similares = $this->getSimilares($product);

        $mostViewed = Cache::remember('show_most_viewed_products', 600, function () {
            return Product::where('status', 1)->where('views', '>', 0)->whereNotNull('photo')->where('photo', '!=', '')->with('brand')->orderBy('views', 'DESC')->take(12)->get();
        });

        return view('produtos.show', [
            'product' => $product,
            'isBridal' => $isBridal,
            'siblings' => $siblings,
            'coresRelacionadas' => $coresRelacionadas,
            'colorSiblings' => $coresRelacionadas,
            'similares' => $similares,
            'mostViewed' => $mostViewed,
            'settings' => $settings,
            'attribute' => $attribute,
        ]);
    }

    private function getSimilares(Product $product)
    {
        $limit = 8;

        return Cache::remember("pdp_similares_{$product->id}_v11", now()->addMinutes(10), function () use ($product, $limit) {
            $palabraClave = $this->palabraClaveSimilar($product);

            $baseQuery = Product::where('status', 1)
                ->where('product_role', 'P')
                ->where('stock', '>', 0)
                ->where('id', '!=', $product->id)
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->with('brand');

            // Escalera de búsqueda: del filtro más específico al más amplio.
            // Si tiene palabraClave: primero busca con ella; si queda corto, baja al fallback sin palabra.
            $niveles = [];

            if ($palabraClave) {
                $niveles[] = ['childcategory_id', $product->childcategory_id, $palabraClave];
                $niveles[] = ['subcategory_id',   $product->subcategory_id,   $palabraClave];
                $niveles[] = ['category_id',      $product->category_id,      $palabraClave];
            }

            $niveles[] = ['childcategory_id', $product->childcategory_id, null];
            $niveles[] = ['subcategory_id',   $product->subcategory_id,   null];
            $niveles[] = ['category_id',      $product->category_id,      null];

            $similares = collect();

            foreach ($niveles as [$campo, $id, $palabra]) {
                $faltan = $limit - $similares->count();
                if ($faltan <= 0) break;
                if (!$id) continue;

                $resultado = (clone $baseQuery)
                    ->where($campo, $id)
                    ->when($palabra, fn($q) => $q->where('external_name', 'LIKE', "%{$palabra}%"))
                    ->whereNotIn('id', $similares->pluck('id'))
                    ->inRandomOrder()
                    ->take($faltan)
                    ->get();

                $similares = $similares->merge($resultado);
            }

            return $similares->take($limit);
        });
    }

    private function palabraClaveSimilar(Product $product): ?string
    {
        $name = mb_strtolower($product->external_name ?? ($product->name ?? ''));
        foreach (array_keys($this->palabrasClaveProductos()) as $palabra) {
            if (str_contains($name, mb_strtolower($palabra))) {
                return $palabra;
            }
        }

        return null;
    }

    private function palabrasClaveProductos(): array
    {
        $path = public_path('data/product_keywords.json');
        if (!is_file($path)) {
            return [];
        }

        return Cache::remember('product_keywords_' . filemtime($path), now()->addHour(), function () use ($path) {
            $keywords = json_decode(file_get_contents($path), true);
            return is_array($keywords) ? $keywords : [];
        });
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
        $descontoMax = $cupons->max(fn($c) => $c->tipo === 'percentual' ? $p->price * ($c->montante / 100) : $c->montante);
        return $descontoMax ? max(0, $p->price - $descontoMax) : $p->price;
    }
}