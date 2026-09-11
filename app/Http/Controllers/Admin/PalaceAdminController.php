<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Palace;
use App\Services\ImageConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PalaceAdminController extends Controller
{
    public function index()
    {
        $palace = Palace::with('translations')->first() ?? Palace::create(['hero_titulo' => 'SAX Palace']);

        return view('admin.palace.index', compact('palace'));
    }

    public function edit($id)
    {
        $palace = Palace::with('translations')->findOrFail($id);

        return view('admin.palace.edit', compact('palace'));
    }

    public function update(Request $request, $id)
    {
        $palace = Palace::findOrFail($id);

        $validated = $request->validate([
            'translate' => 'required|array',
            'translate.pt-br' => 'required|array',
            'translate.es' => 'required|array',
            'translate.en' => 'required|array',
            'translate.*.palace_hero_titulo' => 'nullable|string|max:255',
            'translate.*.palace_hero_descricao' => 'nullable|string',
            'translate.*.palace_bar_titulo' => 'nullable|string|max:255',
            'translate.*.palace_bar_descricao' => 'nullable|string',
            'translate.*.palace_eventos_titulo' => 'nullable|string|max:255',
            'translate.*.palace_eventos_descricao' => 'nullable|string',
            'translate.*.palace_tematica_tag' => 'nullable|string|max:255',
            'translate.*.palace_tematica_titulo' => 'nullable|string|max:255',
            'translate.*.palace_tematica_descricao' => 'nullable|string',
            'translate.*.palace_tematica_preco' => 'nullable|string|max:255',
            'translate.*.palace_gastronomia_titulo' => 'nullable|string|max:255',
            'translate.*.palace_gastronomia_cafe_desc' => 'nullable|string',
            'translate.*.palace_gastronomia_almoco_desc' => 'nullable|string',
            'translate.*.palace_gastronomia_jantar_desc' => 'nullable|string',
            'translate.*.palace_contato_endereco' => 'nullable|string|max:255',
            'translate.*.palace_contato_horario_segunda' => 'nullable|string|max:255',
            'translate.*.palace_contato_horario_sabado' => 'nullable|string|max:255',
            'translate.*.palace_contato_horario_domingo' => 'nullable|string|max:255',
            'contato_whatsapp' => 'nullable|string|max:255',
            'contato_mapa_iframe' => 'nullable|string',
            'hero_imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:8192',
            'bar_imagem_1' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'bar_imagem_2' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'bar_imagem_3' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'tematica_imagem' => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'eventos_galeria' => 'nullable|array|max:12',
            'eventos_galeria.*' => 'image|mimes:jpg,jpeg,png,webp,avif,gif,bmp,tiff,jfif,heic,heif|max:4096',
            'eventos_galeria_managed' => 'nullable|boolean',
            'eventos_galeria_actual' => 'nullable|array',
            'eventos_galeria_actual.*' => 'string|max:2048',
            'gastronomia_menu_pdf' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $translationFields = [
            'palace_hero_titulo',
            'palace_hero_descricao',
            'palace_bar_titulo',
            'palace_bar_descricao',
            'palace_eventos_titulo',
            'palace_eventos_descricao',
            'palace_tematica_tag',
            'palace_tematica_titulo',
            'palace_tematica_descricao',
            'palace_tematica_preco',
            'palace_gastronomia_titulo',
            'palace_gastronomia_cafe_desc',
            'palace_gastronomia_almoco_desc',
            'palace_gastronomia_jantar_desc',
            'palace_contato_endereco',
            'palace_contato_horario_segunda',
            'palace_contato_horario_sabado',
            'palace_contato_horario_domingo',
        ];

        $baseFieldMap = [
            'palace_hero_titulo' => 'hero_titulo',
            'palace_hero_descricao' => 'hero_descricao',
            'palace_bar_titulo' => 'bar_titulo',
            'palace_bar_descricao' => 'bar_descricao',
            'palace_eventos_titulo' => 'eventos_titulo',
            'palace_eventos_descricao' => 'eventos_descricao',
            'palace_tematica_tag' => 'tematica_tag',
            'palace_tematica_titulo' => 'tematica_titulo',
            'palace_tematica_descricao' => 'tematica_descricao',
            'palace_tematica_preco' => 'tematica_preco',
            'palace_gastronomia_titulo' => 'gastronomia_titulo',
            'palace_gastronomia_cafe_desc' => 'gastronomia_cafe_desc',
            'palace_gastronomia_almoco_desc' => 'gastronomia_almoco_desc',
            'palace_gastronomia_jantar_desc' => 'gastronomia_jantar_desc',
            'palace_contato_endereco' => 'contato_endereco',
            'palace_contato_horario_segunda' => 'contato_horario_segunda',
            'palace_contato_horario_sabado' => 'contato_horario_sabado',
            'palace_contato_horario_domingo' => 'contato_horario_domingo',
        ];

        // A tabela principal é o fallback histórico da página. Mantê-la igual ao PT
        // impede que páginas sem tradução completa exibam valores antigos.
        $portuguese = $validated['translate']['pt-br'];
        foreach ($baseFieldMap as $translationField => $baseField) {
            $palace->{$baseField} = $portuguese[$translationField] ?? null;
        }
        $palace->contato_whatsapp = $validated['contato_whatsapp'] ?? null;
        $palace->contato_mapa_iframe = $validated['contato_mapa_iframe'] ?? null;

        $filesToDelete = [];
        $fileFields = ['hero_imagem', 'bar_imagem_1', 'bar_imagem_2', 'bar_imagem_3', 'tematica_imagem'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $oldPath = $palace->{$field};
                $palace->{$field} = $this->convertToWebp($request->file($field), 'palace');
                if ($oldPath && $oldPath !== $palace->{$field}) {
                    $filesToDelete[] = $oldPath;
                }
            }
        }

        $oldGallery = is_array($palace->eventos_galeria) ? $palace->eventos_galeria : [];
        $requestedExisting = isset($validated['eventos_galeria_managed'])
            ? ($validated['eventos_galeria_actual'] ?? [])
            : $oldGallery;
        $galleryPaths = array_values(array_intersect($oldGallery, $requestedExisting));

        $newGalleryFiles = $request->file('eventos_galeria', []);
        if (count($galleryPaths) + count($newGalleryFiles) > 12) {
            throw ValidationException::withMessages([
                'eventos_galeria' => 'A galeria pode ter no máximo 12 imagens.',
            ]);
        }

        if ($newGalleryFiles) {
            foreach ($newGalleryFiles as $image) {
                $galleryPaths[] = $this->convertToWebp($image, 'palace/galeria');
            }
        }

        $filesToDelete = array_merge($filesToDelete, array_diff($oldGallery, $galleryPaths));
        $palace->eventos_galeria = $galleryPaths;

        if ($request->hasFile('gastronomia_menu_pdf')) {
            $oldPdf = $palace->gastronomia_menu_pdf;
            $palace->gastronomia_menu_pdf = $request->file('gastronomia_menu_pdf')->store('menus', 'public');
            if ($oldPdf && $oldPdf !== $palace->gastronomia_menu_pdf) {
                $filesToDelete[] = $oldPdf;
            }
        }

        DB::transaction(function () use ($palace, $validated, $translationFields): void {
            $palace->save();

            foreach (['pt-br', 'es', 'en'] as $locale) {
                $fields = $validated['translate'][$locale];
                $translationData = [];
                foreach ($translationFields as $field) {
                    $translationData[$field] = $fields[$field] ?? null;
                }

                $palace->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $translationData
                );
            }
        });

        if ($filesToDelete) {
            Storage::disk('public')->delete(array_values(array_unique($filesToDelete)));
        }

        Cache::forget('palace_data');

        return redirect()->route('admin.palace.index')->with('success', 'SAX Palace atualizado com sucesso em todos os idiomas!');
    }

    private function convertToWebp($image, $type)
    {
        return app(ImageConverterService::class)->toWebp($image, $type);
    }
}
