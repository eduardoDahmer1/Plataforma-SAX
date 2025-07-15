<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Product;


class UploadController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $perPage = 40;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
    
        // Busca uploads
        $uploadsQuery = Upload::select(
            'id',
            'title',
            'description',
            'created_at',
            DB::raw("'upload' as type"),
            DB::raw('NULL as price'),
            'file_path',
            'original_name'
        );
    
        if ($search) {
            $uploadsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
    
        $uploads = $uploadsQuery->get();
    
        // Busca produtos
        $productsQuery = Product::select(
            'id',
            DB::raw("external_name as title"),
            DB::raw("sku as description"),
            'created_at',
            DB::raw("'product' as type"),
            'price',
            DB::raw('NULL as file_path'),
            DB::raw('NULL as original_name')
        );
    
        if ($search) {
            $productsQuery->where(function ($q) use ($search) {
                $q->where('external_name', 'like', '%' . $search . '%')
                  ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }
    
        $products = $productsQuery->get();
    
        // Une as coleções e ordena por data
        $merged = $uploads->merge($products)->sortByDesc('created_at')->values();
    
        // Paginação manual com LengthAwarePaginator
        $currentItems = $merged->forPage($currentPage, $perPage);
    
        $paginated = new LengthAwarePaginator(
            $currentItems,
            $merged->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    
        return view('admin.uploads.index', ['uploads' => $paginated]);
    }
    

    /**
     * Exibe a página com todos os uploads.
     */
    public function allUploads()
    {
        // Carrega todos os uploads com a relação 'user' de forma antecipada
        $uploads = Upload::with('user')->get();  // Carrega todos os uploads com o usuário

        return view('admin.uploads.index', compact('uploads')); // A página com todos os uploads
    }

    /**
     * Exibe o formulário de criação.
     */
    public function create()
    {
        return view('admin.uploads.create');
    }

    /**
     * Armazena um novo upload.
     */
    public function store(Request $request)
    {
        // Valida o arquivo enviado
        $request->validate([
            'file' => 'required|file|max:10240', // Máximo 10MB
            'title' => 'required|string|max:10240',
            'description' => 'nullable|string|max:10000', // Definindo um valor máximo válido para description
        ]);
    
        $file = $request->file('file');
        $path = $file->store('uploads', 'public');
    
        Upload::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_type' => $file->getClientOriginalExtension(),
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'user_id' => auth()->id() ?? null,  // Opcional, se o usuário estiver autenticado
        ]);
    
        return redirect()->route('admin.uploads.index')->with('success', 'Arquivo enviado com sucesso!');
    }
    

    /**
     * Exibe o formulário de edição.
     */
    public function edit(string $id)
    {
        $upload = Upload::findOrFail($id);  // Recupera o upload com base no ID
        return view('admin.uploads.edit', compact('upload'));
    }

    public function update(Request $request, string $id)
    {
        // Validação dos dados
        $request->validate([
            'title' => 'required|string|max:10240',
            'description' => 'nullable|string|max:10240',
            'file' => 'nullable|file|max:10240', // máximo 10MB
        ]);
    
        $upload = Upload::findOrFail($id);
    
        // Atualiza título e descrição
        $upload->title = $request->title;
        $upload->description = $request->description;
    
        // Verifica se novo arquivo foi enviado
        if ($request->hasFile('file')) {
            // Deleta arquivo antigo se existir
            if ($upload->file_path && Storage::disk('public')->exists($upload->file_path)) {
                Storage::disk('public')->delete($upload->file_path);
            }
    
            // Faz upload do novo arquivo
            $file = $request->file('file');
            $path = $file->store('uploads', 'public');
    
            // Atualiza os dados do arquivo
            $upload->file_path = $path;
            $upload->file_type = $file->getClientOriginalExtension();
            $upload->original_name = $file->getClientOriginalName();
            $upload->mime_type = $file->getClientMimeType();
        }
    
        $upload->save();
    
        return redirect()->route('admin.uploads.index')->with('success', 'Arquivo atualizado com sucesso!');
    }

    /**
     * Remove um upload do banco e do armazenamento.
     */
    public function destroy(string $id)
    {
        $upload = Upload::findOrFail($id);

        // Exclui o arquivo do armazenamento, se existir
        if (Storage::disk('public')->exists($upload->file_path)) {
            Storage::disk('public')->delete($upload->file_path);
        }

        // Exclui o upload do banco de dados
        $upload->delete();

        return redirect()->route('admin.uploads.index')->with('success', 'Arquivo excluído com sucesso!');
    }

    public function show($id)
    {
        // Recupera o upload com base no ID
        $upload = Upload::findOrFail($id);

        return view('admin.uploads.show', compact('upload'));
    }
}