<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file = $request->file('file');

            // Validação simples de imagem (aceita qualquer formato de imagem)
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp,bmp,ico,avif,tiff|max:10240' // até 10MB
            ]);

            // Salva no storage público
            $path = $file->store('uploads/images', 'public');

            // Retorna a URL para o editor
            return response()->json([
                'location' => asset('storage/' . $path)
            ]);
        }

        return response()->json(['error' => 'Arquivo inválido'], 400);
    }
}
