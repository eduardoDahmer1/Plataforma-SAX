<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Attribute;
use App\Models\HomeBanner;
use App\Services\ImageConverterService;

class ImageUploadController extends Controller
{
    public function index()
    {
        $attribute = Attribute::first();
        $webpImage = $attribute?->header_image;
        $logoPalace = $attribute?->logo_palace;
        $logoBridal = $attribute?->logo_bridal;
        $logoCafeBistro = $attribute?->logo_cafe_bistro;
        $logoCafeBistroAsuncion = $attribute?->logo_cafe_bistro_asuncion;
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
            'whatsapp_banner' => $attribute?->whatsapp_banner,
        ];

        $bannerLinks = [
            'banner1_link' => $attribute?->banner1_link,
            'banner2_link' => $attribute?->banner2_link,
            'banner3_link' => $attribute?->banner3_link,
            'banner4_link' => $attribute?->banner4_link,
            'banner5_link' => $attribute?->banner5_link,
            'banner6_link' => $attribute?->banner6_link,
            'banner7_link' => $attribute?->banner7_link,
            'banner8_link' => $attribute?->banner8_link,
            'banner9_link' => $attribute?->banner9_link,
            'banner10_link' => $attribute?->banner10_link,
            'banner_horizontal_link' => $attribute?->banner_horizontal_link,
        ];

        $homeBannerGroups = HomeBanner::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('group');

        return view('admin.admin', compact('webpImage', 'logoPalace', 'logoBridal', 'logoCafeBistro', 'logoCafeBistroAsuncion', 'bannerHorizontal', 'noimage', 'banners', 'bannerLinks', 'attribute', 'homeBannerGroups'));
    }

    private function processImageUpload($file, $filename)
    {
        Storage::disk('public')->delete("uploads/{$filename}");

        $path = app(ImageConverterService::class)->toWebp($file, 'uploads', [
            'filename' => $filename,
            'quality' => 90,
            'strict' => true,
        ]);

        return basename($path);
    }

    private function uploadImage(Request $request, $field, $filename)
    {
        $request->validate([
            $field => 'required|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg,avif|max:10240',
        ]);

        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            $file = $request->file($field);
            $processed = $this->processImageUpload($file, $filename);

            if (!$processed) {
                return response()->json(['success' => false, 'message' => 'Formato de imagem não suportado.'], 422);
            }

            DB::table('attributes')->where('id', 1)->update([$field => $filename]);
            
            \App\Services\CacheService::clearAll();

            return response()->json([
                'success' => true,
                'message' => 'Enviada com sucesso!',
                'url' => asset('storage/uploads/' . $filename) . '?v=' . time()
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Nenhuma imagem válida enviada.'], 400);
    }

    private function deleteImage($field)
    {
        $filename = DB::table('attributes')->where('id', 1)->value($field);

        if ($filename && Storage::disk('public')->exists("uploads/{$filename}")) {
            Storage::disk('public')->delete("uploads/{$filename}");
            DB::table('attributes')->where('id', 1)->update([$field => null]);
            
            \App\Services\CacheService::clearAll();
            
            return response()->json(['success' => true, 'message' => 'Excluída com sucesso!']);
        }

        return response()->json(['success' => false, 'message' => 'Nenhuma imagem para excluir.'], 404);
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
            Cache::forget('global_attributes_model');
            Cache::forget('global_attributes_db');
            Cache::forget('global_attributes');
            Cache::forget('system_attributes');
            return redirect()->back()->with('success', 'Texto do topo atualizado com sucesso!');
        }

        return redirect()->back()->withErrors('Erro ao encontrar as configurações.');
    }

    public function updateBannerLinks(Request $request)
    {
        $linkRules = ['nullable', 'string', 'max:2048', function ($attribute, $value, $fail) {
            if (filled($value) && ! preg_match('#^(https?://|/)#i', $value)) {
                $fail('Use uma URL completa (https://) ou um caminho interno iniciado por /.');
            }
        }];
        $rules = [];
        for ($i = 1; $i <= 10; $i++) {
            $rules["banner{$i}_link"] = $linkRules;
        }
        $rules['banner_horizontal_link'] = $linkRules;

        $validated = $request->validate($rules);

        Attribute::query()->updateOrCreate(
            ['id' => 1],
            $validated
        );

        \App\Services\CacheService::clearAll();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Link do banner atualizado com sucesso!',
            ]);
        }

        return redirect()->back()->with('success', 'Links dos banners atualizados com sucesso!');
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

    // --- Logo exclusiva Café & Bistrô Asunción ---
    public function uploadLogoCafeBistroAsuncion(Request $request) { return $this->uploadImage($request, 'logo_cafe_bistro_asuncion', 'logo_cafe_bistro_asuncion.webp'); }
    public function deleteLogoCafeBistroAsuncion() { return $this->deleteImage('logo_cafe_bistro_asuncion'); }

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
    public function uploadWhatsappBanner(Request $request) { return $this->uploadImage($request, 'whatsapp_banner', 'whatsapp_banner.webp'); }
    public function deleteWhatsappBanner() { return $this->deleteImage('whatsapp_banner'); }
}
