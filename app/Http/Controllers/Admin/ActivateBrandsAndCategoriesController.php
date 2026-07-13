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
        $model = ($type === 'brand') ? Brand::findOrFail($id) : Category::findOrFail($id);

        // Alterna entre 1 (Ativo) e 2 (Inativo)
        $model->status = ($model->status == 1) ? 2 : 1;
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