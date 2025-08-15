<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    // Página inicial com uploads e produtos
    public function home(Request $request)
    {
        $search = $request->get('search');
        $page = $request->get('page', 1);

        // Carrega uploads
        $uploads = Upload::select('id', 'title', 'description', 'file_path', 'created_at')
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
            })
            ->get()
            ->map(function ($u) {
                return (object)[
                    'id' => $u->id,
                    'title' => $u->title,
                    'description' => $u->description,
                    'file_path' => $u->file_path,
                    'photo' => null,
                    'price' => null,
                    'type' => 'upload',
                    'created_at' => $u->created_at
                ];
            });

        // Carrega produtos
        $columns = ['id', 'external_name', 'sku', 'price', 'photo', 'created_at'];
        $products = Product::select($columns)
            ->when($search, function ($query) use ($search) {
                $query->where('external_name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
            })
            ->get()
            ->map(function ($p) {
                return (object)[
                    'id' => $p->id,
                    'title' => $p->external_name,
                    'description' => $p->sku,
                    'photo' => $p->photo,
                    'price' => $p->price,
                    'type' => 'product',
                    'created_at' => $p->created_at
                ];
            });

        // Junta uploads e produtos e ordena por data
        $items = $uploads->merge($products)->sortByDesc('created_at');

        // Paginação manual
        $perPage = 40;
        $currentPage = $page;
        $pagedItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return view('home', [
            'items' => $pagedItems,
            'page' => $currentPage,
            'lastPage' => ceil($items->count() / $perPage)
        ]);
    }

    // Listagem de produtos (página de produtos)
    public function index(Request $request)
    {
        $search = $request->get('search');
        $page = $request->get('page', 1);
        $columns = ['id', 'sku', 'external_name', 'slug', 'price', 'stock', 'photo', 'brand_id', 'category_id', 'subcategory_id', 'childcategory_id'];

        $cacheKey = $search ? null : "products_page_{$page}";

        $products = $cacheKey
            ? Cache::remember($cacheKey, now()->addMinutes(5), function () use ($columns) {
                return Product::select($columns)->orderBy('id', 'desc')->paginate(10);
            })
            : Product::select($columns)
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('external_name', 'LIKE', "%{$search}%")
                          ->orWhere('sku', 'LIKE', "%{$search}%")
                          ->orWhere('slug', 'LIKE', "%{$search}%");
                    });
                })
                ->orderBy('id', 'desc')
                ->paginate(10);

        return view('produtos.index', compact('products'));
    }

    // Detalhes do produto
    public function show($id)
    {
        $product = Product::findOrFail($id);
        $uploads = $product->uploads;

        return view('produtos.show', compact('product', 'uploads'));
    }
}
