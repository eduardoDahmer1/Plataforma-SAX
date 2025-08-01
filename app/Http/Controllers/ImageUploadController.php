<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Attribute;

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

    public function uploadNoimage(Request $request)
    {
        $request->validate([
            'noimage' => 'required|image|max:10240',
        ]);

        if ($request->hasFile('noimage') && $request->file('noimage')->isValid()) {
            $file = $request->file('noimage');
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
                    return redirect()->route('admin.image.form')->with('error', 'Formato de imagem não suportado.');
            }

            if (!$imageResource) {
                return redirect()->route('admin.image.form')->with('error', 'Falha ao criar recurso de imagem.');
            }

            ob_start();
            imagewebp($imageResource, null, 55);
            $webpData = ob_get_clean();
            imagedestroy($imageResource);

            $filename = 'noimage.webp';
            Storage::disk('public')->delete("uploads/{$filename}");
            Storage::disk('public')->put("uploads/{$filename}", $webpData);

            DB::table('attributes')->where('id', 1)->update(['noimage' => $filename]);

            return redirect('/admin')->with('success', 'Imagem salva com sucesso.');

        }

        return redirect()->route('admin.image.form')->with('error', 'Falha ao enviar noimage.');
    }

    public function deleteNoimage()
    {
        $filename = DB::table('attributes')->where('id', 1)->value('noimage');

        if ($filename && Storage::disk('public')->exists("uploads/{$filename}")) {
            Storage::disk('public')->delete("uploads/{$filename}");
            DB::table('attributes')->where('id', 1)->update(['noimage' => null]);

            return redirect()->route('/admin')->with('success', 'Noimage deletada com sucesso!');
        }

        return redirect()->route('admin.image.form')->with('error', 'Nenhuma noimage para excluir.');
    }

    public function index()
    {
        $attribute = Attribute::first(); // ou do jeito que você estiver pegando

        $webpImage = $attribute?->header_image;
        $noimage = $attribute?->noimage;

        return view('admin.admin', compact('webpImage', 'noimage'));
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

    public function home()
{
    $uploads = DB::table('uploads')
        ->select(
            'id',
            'title',
            'description',
            'created_at',
            DB::raw("'upload' as type"),
            DB::raw("NULL as price"),
            DB::raw("NULL as photo") // <-- ESSENCIAL!
        );

    $products = DB::table('products')
        ->select(
            'id',
            'external_name as title',
            'sku as description',
            'created_at',
            DB::raw("'product' as type"),
            'price',
            'photo'
        );

    $items = $uploads
        ->unionAll($products)
        ->orderBy('created_at', 'desc')
        ->limit(40)
        ->get();

    $attribute = Attribute::first();
    $noimage = $attribute?->noimage;

    return view('pages.home', compact('items', 'noimage'));
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
