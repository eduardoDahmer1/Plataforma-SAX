<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Palace;
use Illuminate\Http\Request;

class PalaceAdminController extends Controller
{
    public function index()
    {
        $palaces = Palace::latest()->get();
        return view('admin.palace.index', compact('palaces'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required',
            'detalhes' => 'nullable',
            'slider_principal' => 'image|mimes:jpg,png,jpeg|max:2048',
            // Adicione validações para os outros campos de imagem aqui
        ]);

        // Lógica de upload de imagem simplificada
        if ($request->hasFile('slider_principal')) {
            $data['slider_principal'] = $request->file('slider_principal')->store('palace', 'public');
        }

        Palace::create($data);

        return redirect()->route('admin.palace.index')->with('success', 'Conteúdo atualizado com sucesso!');
    }

    // Outros métodos: create, edit, update, destroy seguem o padrão CRUD
}