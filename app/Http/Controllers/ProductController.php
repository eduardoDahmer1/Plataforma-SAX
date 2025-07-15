<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    // Função para listar os produtos com paginação e pesquisa
    public function index(Request $request)
    {
        $query = Product::query();
    
        $columns = [
            'id', 'sku', 'external_name', 'slug', 'price', 'stock', 'photo', 'brand_id', 'category_id'
        ];
    
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('external_name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%");
            });
    
            $products = $query->select($columns)->orderBy('id', 'desc')->paginate(10);
        } else {
            $page = $request->get('page', 1);
            $products = Cache::remember("products_page_$page", now()->addMinutes(5), function () use ($query, $columns) {
                return $query->select($columns)->orderBy('id', 'desc')->paginate(10);
            });
        }
    
        return view('produtos.index', compact('products'));
    }

    // Função para exibir os detalhes de um produto específico
    public function show($id)
    {
        // Recupera o produto pelo ID
        $product = Product::findOrFail($id);
    
        // Carrega os uploads relacionados ao produto
        $uploads = $product->uploads;
    
        // Retorna a view com os detalhes do produto e os uploads relacionados
        return view('produtos.show', compact('product', 'uploads'));
    }    

    // Função para exibir o formulário de criação de um novo produto
    public function create()
    {
        return view('produtos.create');
    }

    // Função para armazenar um novo produto
    public function store(Request $request)
    {
        // Validação dos dados do produto
        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku',
            'external_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            // Adicione outras validações conforme necessário
        ]);

        // Cria o novo produto
        Product::create($request->all());

        // Redireciona para a lista de produtos com uma mensagem de sucesso
        return redirect()->route('product.index')->with('success', 'Produto criado com sucesso!');
    }

    // Função para exibir o formulário de edição de um produto
    public function edit($id)
    {
        // Recupera o produto pelo ID
        $product = Product::findOrFail($id);

        // Retorna a view com o formulário de edição
        return view('produtos.edit', compact('product'));
    }

    // Função para atualizar um produto existente
    public function update(Request $request, $id)
    {
        // Validação dos dados do produto
        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $id,
            'external_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            // Adicione outras validações conforme necessário
        ]);

        // Recupera o produto pelo ID
        $product = Product::findOrFail($id);

        // Atualiza os dados do produto
        $product->update($request->all());

        // Redireciona para a lista de produtos com uma mensagem de sucesso
        return redirect()->route('product.index')->with('success', 'Produto atualizado com sucesso!');
    }

    // Função para excluir um produto
    public function destroy($id)
    {
        // Recupera o produto pelo ID
        $product = Product::findOrFail($id);

        // Exclui o produto
        $product->delete();

        // Redireciona para a lista de produtos com uma mensagem de sucesso
        return redirect()->route('product.index')->with('success', 'Produto excluído com sucesso!');
    }
}