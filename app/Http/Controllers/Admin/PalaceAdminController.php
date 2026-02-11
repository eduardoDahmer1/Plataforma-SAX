<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Palace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PalaceAdminController extends Controller
{
    /**
     * Exibe a visão geral dos dados atuais.
     */
    public function index()
    {
        $palace = Palace::first() ?? Palace::create(['hero_titulo' => 'SAX Palace']);
        return view('admin.palace.index', compact('palace'));
    }

    /**
     * Exibe o formulário de edição (usa o ID 1 por padrão).
     */
    public function edit($id)
    {
        $palace = Palace::findOrFail($id);
        return view('admin.palace.edit', compact('palace'));
    }

    /**
     * Processa a atualização de todos os campos e imagens.
     */
    public function update(Request $request, $id)
    {
        $palace = Palace::findOrFail($id);

        $data = $request->validate([
            // Textos e Títulos
            'hero_titulo'             => 'nullable|string|max:255',
            'hero_descricao'          => 'nullable|string',
            'bar_titulo'              => 'nullable|string|max:255',
            'bar_descricao'           => 'nullable|string',
            'eventos_titulo'          => 'nullable|string|max:255',
            'eventos_descricao'       => 'nullable|string',
            'tematica_tag'            => 'nullable|string|max:255',
            'tematica_titulo'         => 'nullable|string|max:255',
            'tematica_descricao'      => 'nullable|string',
            'tematica_preco'          => 'nullable|string|max:255',
            'gastronomia_titulo'      => 'nullable|string|max:255',
            'gastronomia_cafe_desc'   => 'nullable|string',
            'gastronomia_almoco_desc' => 'nullable|string',
            'gastronomia_jantar_desc' => 'nullable|string',
            'contato_endereco'        => 'nullable|string',
            'contato_horario_segunda' => 'nullable|string',
            'contato_horario_sabado'  => 'nullable|string',
            'contato_horario_domingo' => 'nullable|string',
            'contato_whatsapp'        => 'nullable|string',
            'contato_mapa_iframe'     => 'nullable|string',

            // Validação de Imagens Individuais
            'hero_imagem'     => 'nullable|image|mimes:jpg,png,jpeg|max:4096',
            'bar_imagem_1'    => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'bar_imagem_2'    => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'bar_imagem_3'    => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'tematica_imagem' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            
            // Galeria de Eventos
            'eventos_galeria'   => 'nullable|array',
            'eventos_galeria.*' => 'image|mimes:jpg,png,jpeg|max:2048',
        ]);

        // 1. Processar Imagens Simples (Substituição)
        $fileFields = ['hero_imagem', 'bar_imagem_1', 'bar_imagem_2', 'bar_imagem_3', 'tematica_imagem'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                if ($palace->$field) {
                    Storage::disk('public')->delete($palace->$field);
                }
                $data[$field] = $request->file($field)->store('palace', 'public');
            }
        }

        // 2. Processar Galeria de Eventos (Se enviar novas, substitui o array anterior)
        if ($request->hasFile('eventos_galeria')) {
            // Opcional: deletar fotos antigas da galeria antes de subir novas
            if ($palace->eventos_galeria) {
                foreach ($palace->eventos_galeria as $oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $galleryPaths = [];
            foreach ($request->file('eventos_galeria') as $image) {
                $galleryPaths[] = $image->store('palace/galeria', 'public');
            }
            $data['eventos_galeria'] = $galleryPaths;
        }

        $palace->update($data);

        return redirect()->route('admin.palace.index')->with('success', 'Conteúdo do SAX Palace atualizado com sucesso!');
    }
}