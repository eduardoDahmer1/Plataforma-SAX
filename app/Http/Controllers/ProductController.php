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
    // Página inicial com uploads e produtos
    public function home(Request $request)
    {
        $search = $request->get('search');
        $page   = $request->get('page', 1);
        $perPage = 40;

        $cacheKey = "home_items_{$page}_" . md5($search ?? '');

        $items = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($search, $perPage) {
            // Uploads
            $uploads = Upload::select('id', 'title', 'description', 'file_path', 'created_at')
                ->when($search, function ($query) use ($search) {
                    $query->where('title', 'LIKE', "%{$search}%")
                          ->orWhere('description', 'LIKE', "%{$search}%");
                })
                ->get()
                ->map(fn ($u) => (object)[
                    'id'          => $u->id,
                    'title'       => $u->title,
                    'description' => $u->description,
                    'file_path'   => $u->file_path,
                    'photo'       => null,
                    'price'       => null,
                    'type'        => 'upload',
                    'created_at'  => $u->created_at,
                ]);

            // Produtos
            $columns = ['id', 'external_name', 'sku', 'price', 'photo', 'created_at'];
            $products = Product::select($columns)
                ->when($search, function ($query) use ($search) {
                    $query->where('external_name', 'LIKE', "%{$search}%")
                          ->orWhere('sku', 'LIKE', "%{$search}%");
                })
                ->get()
                ->map(fn ($p) => (object)[
                    'id'          => $p->id,
                    'title'       => $p->external_name,
                    'description' => $p->sku,
                    'photo'       => $p->photo,
                    'price'       => $p->price,
                    'type'        => 'product',
                    'created_at'  => $p->created_at,
                ]);

            return $uploads->merge($products)->sortByDesc('created_at')->values();
        });

        // Paginação manual só no array final (porque mescla 2 coleções)
        $pagedItems = $items->forPage($page, $perPage);

        return view('home', [
            'items'    => $pagedItems,
            'page'     => $page,
            'lastPage' => ceil($items->count() / $perPage),
        ]);
    }

    // Página com todos os produtos (com busca e cache)
    public function index(Request $request)
    {
        $search = $request->get('search');
        $page   = $request->get('page', 1);
        $columns = ['id', 'sku', 'external_name', 'slug', 'price', 'stock', 'photo', 'brand_id', 'category_id', 'subcategory_id', 'childcategory_id'];

        $cacheKey = "products_page_{$page}_" . md5($search ?? '');

        $products = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($columns, $search) {
            return Product::select($columns)
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('external_name', 'LIKE', "%{$search}%")
                          ->orWhere('sku', 'LIKE', "%{$search}%")
                          ->orWhere('slug', 'LIKE', "%{$search}%");
                    });
                })
                ->orderByDesc('id')
                ->paginate(10);
        });

        return view('produtos.index', compact('products'));
    }

    // Produtos por categoria
    public function byCategory(Category $category)
    {
        $products = Cache::remember("products_category_{$category->id}", now()->addMinutes(10), function () use ($category) {
            return Product::where('category_id', $category->id)->paginate(12);
        });

        return view('produtos.index', compact('products', 'category'));
    }

    // Produtos por subcategoria
    public function bySubcategory(Subcategory $subcategory)
    {
        $products = Cache::remember("products_subcategory_{$subcategory->id}", now()->addMinutes(10), function () use ($subcategory) {
            return Product::where('subcategory_id', $subcategory->id)->paginate(12);
        });

        return view('produtos.index', compact('products', 'subcategory'));
    }

    // Produtos por childcategory
    public function byChildcategory(Childcategory $childcategory)
    {
        $products = Cache::remember("products_childcategory_{$childcategory->id}", now()->addMinutes(10), function () use ($childcategory) {
            return Product::where('childcategory_id', $childcategory->id)->paginate(12);
        });

        return view('produtos.index', compact('products', 'childcategory'));
    }

    // Detalhes de um produto
    public function show(Product $product)
    {
        // Usa eager load se tiver relações
        $uploads = $product->uploads ?? collect();
        return view('produtos.show', compact('product', 'uploads'));
    }
}
