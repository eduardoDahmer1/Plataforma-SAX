<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand; 
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class ActivateBrandsAndCategoriesController extends Controller
{
    /**
     * Exibe a tela de gerenciamento de status
     */
    public function index()
    {
        // Só as colunas usadas na tela: são milhares de marcas.
        $brands = Brand::orderBy('name')->get(['id', 'name', 'slug', 'status']);
        $categories = Category::orderBy('name')->get(['id', 'name', 'slug', 'status']);

        return view('admin.activate.index', compact('brands', 'categories'));
    }

    /**
     * Alterna o status de um item. Responde JSON para a tela atualizar sem recarregar.
     */
    public function toggleStatus(Request $request, $type, $id)
    {
        abort_unless(in_array($type, ['brand', 'category'], true), 404);

        $data = $request->validate([
            'active' => ['required', 'boolean'],
        ]);

        $model = ($type === 'brand') ? Brand::findOrFail($id) : Category::findOrFail($id);

        // Define explicitamente o estado desejado. Repetir a mesma requisição
        // não pode inverter novamente o status.
        $model->status = $data['active'] ? 1 : 2;
        $model->save();

        // Limpa o cache para as mudanças refletirem no site imediatamente
        Cache::flush();

        $label = ($type === 'brand') ? __('messages.marca') : __('messages.categoria');
        $ativo = $model->status == 1;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status'  => $model->status,
                'ativo'   => $ativo,
                'message' => $label . ' ' . ($ativo ? __('messages.ativada_sucesso') : __('messages.desativada_sucesso')),
            ]);
        }

        return back()->with('success', $label . ' ' . ($ativo ? __('messages.ativada_sucesso') : __('messages.desativada_sucesso')));
    }

}
