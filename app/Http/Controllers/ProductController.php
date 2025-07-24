<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Subcategory;
use App\Models\Childcategory;
use App\Models\Brand;
use App\Models\Category;

class ProductController extends Controller
{
    // Listagem de produtos com cache e pesquisa
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

        return view('admin.uploads.index', compact('products'));
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        $uploads = $product->uploads; // ← agora estamos pegando todos os uploads
    
        return view('produtos.show', compact('product', 'uploads'));
    }
    
    // Formulário de criação do produto
    public function create()
    {
        $brands = Brand::all();
        $categories = Category::all();
        $subcategories = Subcategory::all();
        $childcategories = Childcategory::all();

        return view('admin.uploads.create', compact('brands', 'categories', 'subcategories', 'childcategories'));
    }

    // Armazena novo produto
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
            'sku',
            'external_name',
            'description',
            'price',
            'stock',
            'brand_id',
            'category_id',
            'subcategory_id',
            'childcategory_id',
        ]);

        Product::create($data);

        return redirect()->route('admin.uploads.index')->with('success', 'Produto criado com sucesso!');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
    
        $brands = Brand::all();
        $categories = Category::all();
        $subcategories = Subcategory::all();
        $childcategories = Childcategory::all();
    
        return view('admin.uploads.edit', [
            'item' => $product,
            'type' => 'product',
            'brands' => $brands,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'childcategories' => $childcategories,
        ]);
    }

    // Atualiza o produto
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
            'sku',
            'external_name',
            'description',
            'price',
            'stock',
            'brand_id',
            'category_id',
            'subcategory_id',
            'childcategory_id',
        ]);

        $product->update($data);

        return redirect()->route('admin.uploads.index')->with('success', 'Produto atualizado com sucesso!');
    }

    // Remove produto
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.uploads.index')->with('success', 'Produto excluído com sucesso!');
    }


    /*
     * Métodos para Uploads — se precisar unificar na mesma controller
     */

    // Listagem uploads
    public function uploadsIndex()
    {
        $uploads = Upload::orderBy('id', 'desc')->paginate(10);
        return view('admin.uploads.index', compact('uploads'));
    }

    // Formulário edição uploads
    public function editUpload($id)
    {
        $upload = Upload::findOrFail($id);
        return view('admin.uploads.edit', [
            'item' => $upload,
            'type' => 'upload',
        ]);
    }

    // Atualiza upload
    public function updateUpload(Request $request, $id)
    {
        $upload = Upload::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        $upload->title = $request->title;
        $upload->description = $request->description;

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('uploads');
            $upload->file = $path;
        }

        $upload->save();

        return redirect()->route('admin.uploads.index')->with('success', 'Upload atualizado com sucesso!');
    }

    // Remove upload
    public function destroyUpload($id)
    {
        $upload = Upload::findOrFail($id);
        $upload->delete();

        return redirect()->route('admin.uploads.index')->with('success', 'Upload excluído com sucesso!');
    }
}
