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
        $logoPalace = $attribute?->logo_palace;
        $logoBridal = $attribute?->logo_bridal;
        $logoCafeBistro = $attribute?->logo_cafe_bistro;
        $bannerHorizontal = $attribute?->banner_horizontal;
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

        // Passando $attribute para que os novos ícones funcionem no seu array do Blade
        return view('admin.admin', compact('webpImage', 'logoPalace', 'logoBridal', 'logoCafeBistro', 'bannerHorizontal', 'noimage', 'banners', 'attribute'));
    }

    private function processImageUpload($file, $filename)
    {
        $tempPath = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        // Se for webp ou svg, salva o arquivo original para manter a transparência/vetor
        if ($extension === 'webp' || $extension === 'svg') {
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
        imagewebp($imageResource, null, 90);
        $webpData = ob_get_clean();
        imagedestroy($imageResource);

        Storage::disk('public')->delete("uploads/{$filename}");
        Storage::disk('public')->put("uploads/{$filename}", $webpData);

        return $filename;
    }

    private function uploadImage(Request $request, $field, $filename)
    {
        // Ajustado para aceitar SVG também, já que são ícones
        $request->validate([
            $field => 'required|mimes:jpeg,jpg,png,gif,webp,svg|max:10240',
        ]);

        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            $file = $request->file($field);
            
            // Manter a extensão original se for SVG para o filename não ficar .webp
            $extension = strtolower($file->getClientOriginalExtension());
            if($extension === 'svg') {
                $filename = str_replace('.webp', '.svg', $filename);
            }

            $processed = $this->processImageUpload($file, $filename);

            if (!$processed) {
                return back()->with('error', 'Formato de imagem não suportado.');
            }

            DB::table('attributes')->where('id', 1)->update([$field => $filename]);
            return back()->with('success', ucfirst(str_replace('_', ' ', $field)) . ' enviada com sucesso!');
        }

        return back()->with('error', 'Nenhuma imagem válida enviada.');
    }

        public function updateTextTopo(Request $request)
    {
        $request->validate([
            'text_topo' => 'nullable|string|max:255',
        ]);

        // Busca o registro único da tabela attributes (geralmente id 1)
        $attribute = \App\Models\Attribute::first(); 
        
        if ($attribute) {
            $attribute->update(['text_topo' => $request->text_topo]);
            return redirect()->back()->with('success', 'Texto do topo atualizado com sucesso!');
        }

        return redirect()->back()->withErrors('Erro ao encontrar as configurações.');
    }

    private function deleteImage($field)
    {
        $filename = DB::table('attributes')->where('id', 1)->value($field);

        if ($filename && Storage::disk('public')->exists("uploads/{$filename}")) {
            Storage::disk('public')->delete("uploads/{$filename}");
            DB::table('attributes')->where('id', 1)->update([$field => null]);
            return back()->with('success', ucfirst(str_replace('_', ' ', $field)) . ' excluída com sucesso!');
        }

        return back()->with('error', 'Nenhuma imagem para excluir.');
    }

    // --- Métodos Header ---
    public function uploadHeader(Request $request) { return $this->uploadImage($request, 'header_image', 'header_image.webp'); }
    public function deleteHeader() { return $this->deleteImage('header_image'); }    

    // --- Métodos Logo Palace ---
    public function uploadLogoPalace(Request $request) { return $this->uploadImage($request, 'logo_palace', 'logo_palace.webp'); }
    public function deleteLogoPalace() { return $this->deleteImage('logo_palace'); }

    // --- Métodos Logo Bridal ---
    public function uploadLogoBridal(Request $request) { return $this->uploadImage($request, 'logo_bridal', 'logo_bridal.webp'); }
    public function deleteLogoBridal() { return $this->deleteImage('logo_bridal'); }

    // --- Métodos Logo Café & Bistrô ---
    public function uploadLogoCafeBistro(Request $request) { return $this->uploadImage($request, 'logo_cafe_bistro', 'logo_cafe_bistro.webp'); }
    public function deleteLogoCafeBistro() { return $this->deleteImage('logo_cafe_bistro'); }

    public function uploadBannerHorizontal(Request $request) { return $this->uploadImage($request, 'banner_horizontal', 'banner_horizontal.webp'); }
    public function deleteBannerHorizontal() { return $this->deleteImage('banner_horizontal'); }

    // --- MÉTODOS PARA OS NOVOS ÍCONES ---
    public function uploadIconInfo(Request $request) { return $this->uploadImage($request, 'icon_info', 'icon_info.webp'); }
    public function deleteIconInfo() { return $this->deleteImage('icon_info'); }

    public function uploadIconCabide(Request $request) { return $this->uploadImage($request, 'icon_cabide', 'icon_cabide.webp'); }
    public function deleteIconCabide() { return $this->deleteImage('icon_cabide'); }

    public function uploadIconHelp(Request $request) { return $this->uploadImage($request, 'icon_help', 'icon_help.webp'); }
    public function deleteIconHelp() { return $this->deleteImage('icon_help'); }

    // --- Outros Métodos ---
    public function uploadNoimage(Request $request) { return $this->uploadImage($request, 'noimage', 'noimage.webp'); }
    public function deleteNoimage() { return $this->deleteImage('noimage'); }

    // Banners 1 a 10
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