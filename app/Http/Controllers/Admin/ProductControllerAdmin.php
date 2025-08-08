<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class ProductControllerAdmin extends Controller
{
    // Listagem de produtos
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
    
        return view('admin.products.index', compact('products'));
    }

    // Mostrar formulário de criação
    public function create()
    {
        $brands = Brand::all();
        $categories = Category::all();
        $subcategories = Subcategory::all();
        $childcategories = Childcategory::all();

        return view('admin.products.create', compact('brands', 'categories', 'subcategories', 'childcategories'));
    }

    // Salvar produto novo
    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku',
            'external_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'childcategory_id' => 'nullable|exists:childcategories,id',
        ]);

        $data = $request->only([
            'sku', 'external_name', 'description', 'price', 'stock', 
            'brand_id', 'category_id', 'subcategory_id', 'childcategory_id',
        ]);

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Produto criado com sucesso!');
    }

    // Mostrar formulário de edição
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $brands = Brand::all();
        $categories = Category::all();
        $subcategories = Subcategory::all();
        $childcategories = Childcategory::all();

        return view('admin.products.edit', [
            'item' => $product,
            'type' => 'product',
            'brands' => $brands,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'childcategories' => $childcategories,
        ]);
    }

    // Atualizar produto
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'external_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'childcategory_id' => 'nullable|exists:childcategories,id',
        ]);

        $data = $request->only([
            'sku', 'external_name', 'description', 'price', 'stock',
            'brand_id', 'category_id', 'subcategory_id', 'childcategory_id',
        ]);

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Produto atualizado com sucesso!');
    }

    // Deletar produto
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produto excluído com sucesso!');
    }
}
