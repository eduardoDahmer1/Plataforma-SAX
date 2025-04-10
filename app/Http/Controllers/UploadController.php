<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Exibe os uploads recentes (home).
     */
    public function index(Request $request)
    {
        $query = Upload::query();

        // Verifica se há um termo de pesquisa
        if ($request->has('search') && $request->search != '') {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Recupera os uploads com paginação
        $uploads = $query->paginate(5);

        return view('pages.home', compact('uploads')); // Home exibe os 5 uploads mais recentes
    }

    /**
     * Exibe a página com todos os uploads.
     */
    public function allUploads()
    {
        // Exibe todos os uploads para a página index
        $uploads = Upload::all();  // Aqui você pode aplicar a paginação, se necessário
        return view('uploads.index', compact('uploads')); // A página com todos os uploads
    }

    /**
     * Exibe o formulário de criação.
     */
    public function create()
    {
        return view('uploads.create');
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
    
        return redirect()->route('uploads.index')->with('success', 'Arquivo enviado com sucesso!');
    }
    

    /**
     * Exibe o formulário de edição.
     */
    public function edit(string $id)
    {
        $upload = Upload::findOrFail($id);  // Recupera o upload com base no ID
        return view('uploads.edit', compact('upload'));
    }

    /**
     * Atualiza um upload existente.
     */
    public function update(Request $request, string $id)
    {
        // Valida os dados
        $request->validate([
            'title' => 'required|string|max:10240',
            'description' => 'nullable|string|max:10240',
            'file' => 'nullable|file|max:10240', // Máximo 10MB
        ]);

        // Encontra o upload pelo ID
        $upload = Upload::findOrFail($id);

        // Atualiza o título e a descrição
        $upload->title = $request->title;
        $upload->description = $request->description;

        // Verifica se um novo arquivo foi enviado
        if ($request->hasFile('file')) {
            // Remove o arquivo antigo, se existir
            if ($upload->file_path && Storage::disk('public')->exists($upload->file_path)) {
                Storage::disk('public')->delete($upload->file_path); // Exclui o arquivo anterior
            }

            // Faz o upload do novo arquivo
            $file = $request->file('file');
            $path = $file->store('uploads', 'public'); // Faz o upload e armazena no diretório 'uploads'

            // Atualiza as informações no banco de dados
            $upload->file_path = $path;
            $upload->file_type = $file->getClientOriginalExtension(); // Tipo de arquivo (extensão)
            $upload->original_name = $file->getClientOriginalName(); // Nome original do arquivo
            $upload->mime_type = $file->getClientMimeType(); // Tipo MIME do arquivo
        }

        // Salva as alterações no banco de dados
        $upload->save();

        // Redireciona com uma mensagem de sucesso
        return redirect()->route('uploads.index')->with('success', 'Arquivo atualizado com sucesso!');
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

        return redirect()->route('uploads.index')->with('success', 'Arquivo excluído com sucesso!');
    }

        public function show($id)
    {
        // Recupera o upload com base no ID
        $upload = Upload::findOrFail($id);

        return view('uploads.show', compact('upload'));
    }
}