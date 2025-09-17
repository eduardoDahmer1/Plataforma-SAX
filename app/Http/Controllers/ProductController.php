<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Upload;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function home(Request $request)
    {
        $search  = $request->get('search');
        $page    = $request->get('page', 1);
        $perPage = 40;

        $cacheKey = "home_items_{$page}_" . md5($search ?? '');

        $items = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($search) {
            // Uploads
            $uploads = Upload::select('id', 'title', 'description', 'file_path', 'created_at')
                ->when($search, fn($q) => $q->where('title', 'LIKE', "%{$search}%")
                                           ->orWhere('description', 'LIKE', "%{$search}%"))
                ->get()
                ->map(fn($u) => (object)[
                    'id'          => $u->id,
                    'title'       => $u->title,
                    'description' => $u->description,
                    'photo'       => null,
                    'price'       => null,
                    'price_final' => null,
                    'type'        => 'upload',
                    'created_at'  => $u->created_at,
                ]);

            // Produtos
            $columns = ['id', 'external_name', 'sku', 'price', 'photo', 'brand_id', 'category_id', 'created_at'];
            $products = Product::select($columns)
                ->when($search, fn($q) => $q->where('external_name', 'LIKE', "%{$search}%")
                                           ->orWhere('sku', 'LIKE', "%{$search}%"))
                ->with(['cupons' => fn($q) => $q->ativos()]) // eager load cupons ativos
                ->get()
                ->map(fn($p) => (object)[
                    'id'          => $p->id,
                    'title'       => $p->external_name,
                    'description' => $p->sku,
                    'photo'       => $p->photo,
                    'price'       => $p->price,
                    'price_final' => $this->calcularPrecoComCupon($p),
                    'type'        => 'product',
                    'created_at'  => $p->created_at,
                ]);

            return $uploads->merge($products)->sortByDesc('created_at')->values();
        });

        $pagedItems = $items->forPage($page, $perPage);

        return view('home', [
            'items'    => $pagedItems,
            'page'     => $page,
            'lastPage' => ceil($items->count() / $perPage),
        ]);
    }

    // Página com todos os produtos
    public function index(Request $request)
    {
        $search   = $request->get('search');
        $page     = $request->get('page', 1);
        $perPage  = 12;
        $cacheKey = "products_page_{$page}_" . md5($search ?? '');

        $products = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($search, $perPage) {
            return Product::with(['cupons' => fn($q) => $q->ativos()])
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

    // Produtos por categoria
    public function byCategory(Category $category)
    {
        $products = Cache::remember("products_category_{$category->id}", now()->addMinutes(10), fn() =>
            Product::where('category_id', $category->id)
                   ->with(['cupons' => fn($q) => $q->ativos()])
                   ->paginate(12)
        );

        foreach ($products as $p) {
            $p->price_final = $this->calcularPrecoComCupon($p);
        }

        return view('produtos.index', compact('products', 'category'));
    }

    // Produtos por subcategoria
    public function bySubcategory(Subcategory $subcategory)
    {
        $products = Cache::remember("products_subcategory_{$subcategory->id}", now()->addMinutes(10), fn() =>
            Product::where('subcategory_id', $subcategory->id)
                   ->with(['cupons' => fn($q) => $q->ativos()])
                   ->paginate(12)
        );

        foreach ($products as $p) {
            $p->price_final = $this->calcularPrecoComCupon($p);
        }

        return view('produtos.index', compact('products', 'subcategory'));
    }

    // Produtos por childcategory
    public function byChildcategory(Childcategory $childcategory)
    {
        $products = Cache::remember("products_childcategory_{$childcategory->id}", now()->addMinutes(10), fn() =>
            Product::where('childcategory_id', $childcategory->id)
                   ->with(['cupons' => fn($q) => $q->ativos()])
                   ->paginate(12)
        );

        foreach ($products as $p) {
            $p->price_final = $this->calcularPrecoComCupon($p);
        }

        return view('produtos.index', compact('products', 'childcategory'));
    }

    // Detalhes de um produto
    public function show(Product $product)
    {
        $product->load(['cupons' => fn($q) => $q->ativos()]);
        $product->price_final = $this->calcularPrecoComCupon($product);

        return view('produtos.show', compact('product'));
    }

    // Função helper para calcular preço final com cupom
    private function calcularPrecoComCupon(Product $p)
    {
        $cupons = $p->cupons ?? collect();

        $descontoMax = $cupons->max(fn($c) => $c->tipo === 'percentual'
            ? $p->price * ($c->montante / 100)
            : $c->montante);

        return $descontoMax ? $p->price - $descontoMax : $p->price;
    }
}
