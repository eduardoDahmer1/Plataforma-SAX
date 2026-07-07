<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucional;
use App\Services\ImageConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class InstitucionalAdminController extends Controller
{
    /**
     * Exibe a visão geral dos dados institucionais.
     */
    public function index()
    {
        $institucional = Institucional::with('translations')->first() ?? Institucional::create(['section_one_title' => 'SAX Institutional']);
        return view('admin.institucional.index', compact('institucional'));
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit($id)
    {
        // Carrega o registro com as traduções para preencher as abas do form
        $institucional = Institucional::with('translations')->findOrFail($id);
        return view('admin.institucional.edit', compact('institucional'));
    }
    
    public function update(Request $request, $id)
    {
        $institucional = Institucional::findOrFail($id);

        $data = $request->validate([
            'stat_brands_count'         => 'nullable|integer',
            'stat_sqm_count'            => 'nullable|integer',
            'stat_employees_count'      => 'nullable|integer',
            'iframe_tour_360'           => 'nullable|string',
            'iframe_ponte_amizade'      => 'nullable|string',
            'iframe_centro_cde'         => 'nullable|string',
            
            'section_one_image'         => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:8192',
            'top_sliders'               => 'nullable|array',
            'top_sliders.*'             => 'image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'brand_logos'               => 'nullable|array',
            'brand_logos.*'             => 'image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'gallery_images'            => 'nullable|array',
            'gallery_images.*'          => 'image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',

            'translate'                 => 'required|array',
            'translate.pt-br'           => 'nullable|array',
            'translate.es'              => 'nullable|array',
            'translate.en'              => 'nullable|array',
        ]);

        if ($request->hasFile('section_one_image')) {
            if ($institucional->section_one_image) {
                Storage::disk('public')->delete($institucional->section_one_image);
            }
            $data['section_one_image'] = $this->convertToWebp($request->file('section_one_image'), 'institucional');
        }

        $arrayFields = ['top_sliders', 'brand_logos', 'gallery_images'];
        foreach ($arrayFields as $field) {
            if ($request->hasFile($field)) {
                if ($institucional->$field) {
                    foreach ($institucional->$field as $oldPath) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $newPaths = [];
                foreach ($request->file($field) as $image) {
                    $newPaths[] = $this->convertToWebp($image, "institucional/$field");
                }
                $data[$field] = $newPaths;
            }
        }
        
        $translationsInput = $request->input('translate', []);
        $data['section_one_title'] = $translationsInput['pt-br']['inst_section_one_title'] ?? 'SAX Institutional';
        $data['section_one_content'] = $translationsInput['pt-br']['inst_section_one_content'] ?? null;

        $institucional->update($data);

        $localesMapeados = ['pt-br' => 'pt-br', 'es' => 'es', 'en' => 'en'];

        foreach ($localesMapeados as $formLocale => $dbLocale) {
            $localeFields = $translationsInput[$formLocale] ?? [];

            $institucional->translations()->updateOrCreate(
                [
                    'locale'    => $dbLocale,
                    'page_type' => $institucional->getMorphClass(), 
                ],
                [
                    'inst_section_one_title'       => $localeFields['inst_section_one_title'] ?? null,
                    'inst_section_one_content'     => $localeFields['inst_section_one_content'] ?? null,
                    'inst_text_section_one_title'  => $localeFields['inst_text_section_one_title'] ?? null,
                    'inst_text_section_one_body'   => $localeFields['inst_text_section_one_body'] ?? null,
                    'inst_text_section_two_title'  => $localeFields['inst_text_section_two_title'] ?? null,
                    'inst_text_section_two_body'   => $localeFields['inst_text_section_two_body'] ?? null,
                    'inst_text_section_three_title'=> $localeFields['inst_text_section_three_title'] ?? null,
                    'inst_text_section_three_body' => $localeFields['inst_text_section_three_body'] ?? null,
                ]
            );
        }

        Cache::forget('institucional_page_data');

        return redirect()->route('admin.institucional.index')->with('success', 'Dados atualizados com sucesso!');
    }

    /**
     * Conversor a WebP: el service centraliza la lógica; aquí solo se pasa la ruta.
     */
    private function convertToWebp($image, $type)
    {
        return app(ImageConverterService::class)->toWebp($image, $type);
    }
}
