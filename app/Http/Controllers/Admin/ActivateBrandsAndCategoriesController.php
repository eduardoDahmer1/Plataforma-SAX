<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;    // Certifique-se que o Model existe
use App\Models\Category; // Certifique-se que o Model existe

class ActivateBrandsAndCategoriesController extends Controller
{
    public function index()
    {
        $brands = Brand::all();
        $categories = Category::all();

        return view('admin.activate.index', compact('brands', 'categories'));
    }

    public function toggleStatus(Request $request, $type, $id)
    {
        $model = ($type === 'brand') ? \App\Models\Brand::findOrFail($id) : \App\Models\Category::findOrFail($id);

        $model->status = ($model->status == 1) ? 2 : 1;
        $model->save();

        // Isso limpa TODO o cache do sistema (Front e Admin)
        \Illuminate\Support\Facades\Cache::flush();

        $label = ($type === 'brand') ? 'Marca' : 'Categoria';
        $statusTexto = ($model->status == 1) ? 'ativada' : 'desativada';

        return back()->with('success', "{$label} {$statusTexto} com sucesso!");
    }

    public function updateAll(Request $request)
    {
        // Recebe os arrays de status (id => status)
        $categories = $request->input('categories', []);
        $brands = $request->input('brands', []);

        // Atualiza Categorias
        foreach ($categories as $id => $status) {
            \App\Models\Category::where('id', $id)->update(['status' => $status]);
        }

        // Atualiza Marcas
        foreach ($brands as $id => $status) {
            \App\Models\Brand::where('id', $id)->update(['status' => $status]);
        }

        // Limpa o cache uma única vez ao final
        \Illuminate\Support\Facades\Cache::flush();

        return back()->with('success', "Alterações aplicadas com sucesso!");
    }
}
