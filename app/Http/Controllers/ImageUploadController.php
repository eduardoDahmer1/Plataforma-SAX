<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    // Exibe o formulário de upload
    public function form()
    {
        // Traz a imagem atual que está salva, caso tenha sido feita um upload anterior
        $webpImage = session('webpImage');  // Pega a imagem da sessão (se existir)
        return view('upload-image-form', compact('webpImage'));
    }

    // Faz o upload da imagem e converte para WebP
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // até 10MB
        ]);

        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            $file = $request->file('image');
            $originalExtension = strtolower($file->getClientOriginalExtension());
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $webpName = $originalName . '.webp';
            $tempPath = $file->getRealPath();

            // Criar recurso de imagem baseado no tipo
            switch ($originalExtension) {
                case 'jpeg':
                case 'jpg':
                    $imageResource = imagecreatefromjpeg($tempPath);
                    break;
                case 'png':
                    $imageResource = imagecreatefrompng($tempPath);
                    break;
                case 'gif':
                    $imageResource = imagecreatefromgif($tempPath);
                    break;
                default:
                    return back()->with('error', 'Formato de imagem não suportado para conversão WebP.');
            }

            if (!$imageResource) {
                return back()->with('error', 'Falha ao criar recurso de imagem.');
            }

            // Converte para WebP e captura o conteúdo
            ob_start();
            imagewebp($imageResource, null, 55);
            $webpData = ob_get_clean();
            imagedestroy($imageResource);

            // Verifica se já existe uma imagem antes de salvar a nova
            $oldImage = session('webpImage');
            if ($oldImage) {
                // Exclui a imagem antiga se houver
                Storage::disk('public')->delete("uploads/{$oldImage}");
            }

            // Salva o arquivo WebP com o nome original
            Storage::disk('public')->put("uploads/{$webpName}", $webpData);

            // Salva o nome da imagem na sessão
            session(['webpImage' => $webpName]);

            // Retorna à página com o nome da imagem convertida
            return back()->with('success', "Imagem convertida e salva com sucesso! Nome: {$webpName}");
        }

        return back()->with('error', 'Nenhuma imagem válida enviada.');
    }

    // Exclui a imagem convertida
    public function delete(Request $request)
    {
        $webpImage = session('webpImage');  // Pega a imagem da sessão

        if ($webpImage && Storage::disk('public')->exists("uploads/{$webpImage}")) {
            // Exclui a imagem convertida
            Storage::disk('public')->delete("uploads/{$webpImage}");

            // Remove o nome da imagem da sessão
            session()->forget('webpImage');

            return back()->with('success', 'Imagem excluída com sucesso!');
        }

        return back()->with('error', 'Nenhuma imagem encontrada para excluir.');
    }
}
