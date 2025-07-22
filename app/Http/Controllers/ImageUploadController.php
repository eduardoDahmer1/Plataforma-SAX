<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ImageUploadController extends Controller
{
    public function form()
{
    $attribute = DB::table('attributes')->find(1);
    $webpImage = $attribute?->header_image;

    // Se a imagem foi salva na sessão (logo após upload), usa ela
    if(session('webpImage')) {
        $webpImage = session('webpImage');
    }

    return view('admin.image-upload', compact('webpImage'));
}

public function index()
{
    $attribute = DB::table('attributes')->find(1);
    $webpImage = $attribute?->header_image;

    return view('admin.admin', compact('webpImage'));
}

    
    public function upload(Request $request)
    {
        $request->validate([
            'header_image' => 'required|image|max:10240',
        ]);

        if ($request->hasFile('header_image') && $request->file('header_image')->isValid()) {

            $file = $request->file('header_image');
            $tempPath = $file->getRealPath();

            $imageResource = null;
            $extension = strtolower($file->getClientOriginalExtension());

            switch ($extension) {
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
                    return redirect()->route('admin.index')->with('error', 'Formato de imagem não suportado.');
            }

            if (!$imageResource) {
                return redirect()->route('admin.index')->with('error', 'Falha ao criar recurso de imagem.');
            }

            ob_start();
            imagewebp($imageResource, null, 55);
            $webpData = ob_get_clean();
            imagedestroy($imageResource);

            $attributeId = 1;

            $filename = 'header_image.webp'; // nome fixo para evitar múltiplos arquivos

            // Apaga a imagem antiga, se existir
            if (Storage::disk('public')->exists("uploads/{$filename}")) {
                Storage::disk('public')->delete("uploads/{$filename}");
            }

            // Salva a nova imagem com o nome fixo
            Storage::disk('public')->put("uploads/{$filename}", $webpData);

            // Atualiza o nome fixo no banco
            DB::table('attributes')->where('id', $attributeId)->update([
                'header_image' => $filename,
            ]);

            return redirect()->route('admin.index')->with('success', 'Imagem enviada e salva com sucesso no banco!');
        }

        return redirect()->route('admin.index')->with('error', 'Nenhuma imagem válida enviada.');
    }

    public function delete(Request $request)
    {
        $attributeId = 1; // ajuste conforme seu cenário
        $webpImage = DB::table('attributes')->where('id', $attributeId)->value('header_image');

        if ($webpImage && Storage::disk('public')->exists("uploads/{$webpImage}")) {
            Storage::disk('public')->delete("uploads/{$webpImage}");
            DB::table('attributes')->where('id', $attributeId)->update(['header_image' => null]);
            return redirect()->route('admin.index')->with('success', 'Imagem excluída com sucesso!');
        }

        return redirect()->route('admin.index')->with('error', 'Nenhuma imagem encontrada para excluir.');
    }
}
