<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Attribute;

class ImageUploadController extends Controller
{
    public function index()
    {
        $attribute = Attribute::first();
        $webpImage = $attribute?->header_image;
        $noimage = $attribute?->noimage;
        $banners = [
            'banner1' => $attribute?->banner1,
            'banner2' => $attribute?->banner2,
            'banner3' => $attribute?->banner3,
            'banner4' => $attribute?->banner4,
            'banner5' => $attribute?->banner5,
            'banner6' => $attribute?->banner6,
            'banner7' => $attribute?->banner7,
            'banner8' => $attribute?->banner8,
            'banner9' => $attribute?->banner9,
            'banner10' => $attribute?->banner10,
        ];

        return view('admin.admin', compact('webpImage', 'noimage', 'banners'));
    }

    private function processImageUpload($file, $filename)
    {
        $tempPath = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        // Se for webp, só salva direto
        if ($extension === 'webp') {
            Storage::disk('public')->delete("uploads/{$filename}");
            Storage::disk('public')->putFileAs('uploads', $file, $filename);
            return $filename;
        }

        $imageResource = null;

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
                return null;
        }

        if (!$imageResource) return null;

        ob_start();
        imagewebp($imageResource, null, 80);
        $webpData = ob_get_clean();
        imagedestroy($imageResource);

        Storage::disk('public')->delete("uploads/{$filename}");
        Storage::disk('public')->put("uploads/{$filename}", $webpData);

        return $filename;
    }

    private function uploadImage(Request $request, $field, $filename)
    {
        $request->validate([
            $field => 'required|image|max:10240',
        ]);

        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            $file = $request->file($field);
            $processed = $this->processImageUpload($file, $filename);

            if (!$processed) {
                return redirect()->route('admin.index')->with('error', 'Formato de imagem não suportado.');
            }

            DB::table('attributes')->where('id', 1)->update([$field => $filename]);
            return redirect()->route('admin.index')->with('success', ucfirst($field) . ' enviada com sucesso!');
        }

        return redirect()->route('admin.index')->with('error', 'Nenhuma imagem válida enviada.');
    }

    private function deleteImage($field)
    {
        $filename = DB::table('attributes')->where('id', 1)->value($field);

        if ($filename && Storage::disk('public')->exists("uploads/{$filename}")) {
            Storage::disk('public')->delete("uploads/{$filename}");
            DB::table('attributes')->where('id', 1)->update([$field => null]);
            return redirect()->route('admin.index')->with('success', ucfirst($field) . ' excluída com sucesso!');
        }

        return redirect()->route('admin.index')->with('error', 'Nenhuma ' . $field . ' para excluir.');
    }

    public function uploadHeader(Request $request)
    {
        $request->validate([
            'header_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    
        $path = $request->file('header_image')->storeAs(
            'uploads',
            'header_image.webp',
            'public'
        );
    
        DB::table('attributes')->where('id', 1)->update([
            'header_image' => basename($path)
        ]);
    
        return back()->with('success', 'Header atualizado com sucesso!');
    }
    
    public function deleteHeader()
    {
        $filename = DB::table('attributes')->where('id', 1)->value('header_image');
    
        if ($filename && Storage::disk('public')->exists("uploads/{$filename}")) {
            Storage::disk('public')->delete("uploads/{$filename}");
        }
    
        DB::table('attributes')->where('id', 1)->update([
            'header_image' => null
        ]);
    
        return back()->with('success', 'Header removido com sucesso!');
    }    

    public function uploadNoimage(Request $request) { return $this->uploadImage($request, 'noimage', 'noimage.webp'); }
    public function deleteNoimage() { return $this->deleteImage('noimage'); }

    public function uploadBanner1(Request $request) { return $this->uploadImage($request, 'banner1', 'banner1.webp'); }
    public function deleteBanner1() { return $this->deleteImage('banner1'); }

    public function uploadBanner2(Request $request) { return $this->uploadImage($request, 'banner2', 'banner2.webp'); }
    public function deleteBanner2() { return $this->deleteImage('banner2'); }

    public function uploadBanner3(Request $request) { return $this->uploadImage($request, 'banner3', 'banner3.webp'); }
    public function deleteBanner3() { return $this->deleteImage('banner3'); }

    public function uploadBanner4(Request $request) { return $this->uploadImage($request, 'banner4', 'banner4.webp'); }
    public function deleteBanner4() { return $this->deleteImage('banner4'); }

    public function uploadBanner5(Request $request) { return $this->uploadImage($request, 'banner5', 'banner5.webp'); }
    public function deleteBanner5() { return $this->deleteImage('banner5'); }

    public function uploadBanner6(Request $request) { return $this->uploadImage($request, 'banner6', 'banner6.webp'); }
    public function deleteBanner6() { return $this->deleteImage('banner6'); }

    public function uploadBanner7(Request $request) { return $this->uploadImage($request, 'banner7', 'banner7.webp'); }
    public function deleteBanner7() { return $this->deleteImage('banner7'); }

    public function uploadBanner8(Request $request) { return $this->uploadImage($request, 'banner8', 'banner8.webp'); }
    public function deleteBanner8() { return $this->deleteImage('banner8'); }

    public function uploadBanner9(Request $request) { return $this->uploadImage($request, 'banner9', 'banner9.webp'); }
    public function deleteBanner9() { return $this->deleteImage('banner9'); }

    public function uploadBanner10(Request $request) { return $this->uploadImage($request, 'banner10', 'banner10.webp'); }
    public function deleteBanner10() { return $this->deleteImage('banner10'); }
}