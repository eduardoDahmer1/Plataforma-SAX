<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Upload; // Assumindo que exista esse model para uploads
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    // Listagem de produtos com cache e pesquisa
    public function index(Request $request)
    {
        $search = $request->get('search');
        $page = $request->get('page', 1);
        $columns = ['id', 'sku', 'external_name', 'slug', 'price', 'stock', 'photo', 'brand_id', 'category_id'];

        $cacheKey = $search 
            ? null 
            : "products_page_{$page}";

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

    // Exibe detalhes de um produto
    public function show($id)
    {
        $product = Product::findOrFail($id);
        $uploads = $product->uploads ?? null;

        return view('produtos.show', compact('product', 'uploads'));
    }

    // Formulário de criação de produto
    public function create()
    {
        return view('produtos.create');
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
        ]);

        Product::create($request->all());

        return redirect()->route('product.index')->with('success', 'Produto criado com sucesso!');
    }

    // Formulário de edição para produtos (usando view unificada)
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.uploads.edit', [
            'item' => $product,
            'type' => 'product',
        ]);
    }

    // Atualiza produto
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'external_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product->fill($request->only([
            'sku', 'external_name', 'description', 'price', 'stock'
        ]));

        $product->save();

        return redirect()->route('admin.uploads.index')->with('success', 'Arquivo atualizado com sucesso!');
    }

    // Remove produto
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('product.index')->with('success', 'Produto excluído com sucesso!');
    }

    /*
    * Métodos para Uploads — se precisar unificar na mesma controller
    */

    // Listagem uploads (exemplo, se quiser)
    public function uploadsIndex()
    {
        $uploads = Upload::orderBy('id', 'desc')->paginate(10);
        return view('admin.uploads.index', compact('uploads'));
    }

    // Formulário edição uploads (usando mesma view)
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
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx', // ajuste conforme tipos aceitos
        ]);

        $upload->title = $request->title;
        $upload->description = $request->description;

        if ($request->hasFile('file')) {
            // exemplo de salvar arquivo, ajuste conforme seu fluxo
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
