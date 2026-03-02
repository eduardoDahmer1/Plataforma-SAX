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
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.activate.index', compact('brands', 'categories'));
    }

    /**
     * Alterna o status de um item individual (via link/botão direto se houver)
     */
    public function toggleStatus(Request $request, $type, $id)
    {
        $model = ($type === 'brand') ? Brand::findOrFail($id) : Category::findOrFail($id);

        // Alterna entre 1 (Ativo) e 2 (Inativo)
        $model->status = ($model->status == 1) ? 2 : 1;
        $model->save();

        // Limpa o cache para as mudanças refletirem no site imediatamente
        Cache::flush();

        $label = ($type === 'brand') ? 'Marca' : 'Categoria';
        $statusTexto = ($model->status == 1) ? 'ativada' : 'desativada';

        return back()->with('success', "{$label} {$statusTexto} com sucesso!");
    }

    /**
     * Atualiza todos os itens enviados pelo formulário em lote
     */
    public function updateAll(Request $request)
    {
        // Recebe os arrays de status (ID => STATUS)
        $categoriesInput = $request->input('categories', []);
        $brandsInput = $request->input('brands', []);

        // Atualiza Categorias
        foreach ($categoriesInput as $id => $status) {
            Category::where('id', $id)->update(['status' => (int)$status]);
        }

        // Atualiza Marcas
        foreach ($brandsInput as $id => $status) {
            Brand::where('id', $id)->update(['status' => (int)$status]);
        }

        // Limpa o cache uma única vez após todas as atualizações
        Cache::flush();

        return back()->with('success', "Alterações aplicadas com sucesso e cache limpo!");
    }
}