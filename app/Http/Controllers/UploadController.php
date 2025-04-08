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
    public function index()
    {
        // Exibe todos os uploads para a página principal
        $uploads = Upload::latest()->take(5)->get();  // Limita a 5 uploads mais recentes
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'file' => 'nullable|file|max:10240', // Máximo 10MB
        ]);

        $upload = Upload::findOrFail($id);
        $upload->title = $request->title;
        $upload->description = $request->description;

        // Atualiza o arquivo, se houver um novo
        if ($request->hasFile('file')) {
            // Remove o arquivo antigo, se existir
            if (Storage::disk('public')->exists($upload->file_path)) {
                Storage::disk('public')->delete($upload->file_path);
            }

            // Faz o upload do novo arquivo
            $file = $request->file('file');
            $path = $file->store('uploads', 'public');

            // Atualiza os dados do upload
            $upload->file_path = $path;
            $upload->file_type = $file->getClientOriginalExtension();
            $upload->original_name = $file->getClientOriginalName();
            $upload->mime_type = $file->getClientMimeType();
        }

        $upload->save();

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