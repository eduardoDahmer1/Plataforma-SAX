<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Services\StoreTaxonomyService;
use Illuminate\Http\Request;
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
        $categories = app(StoreTaxonomyService::class)->categories(Category::query())
            ->orderBy('name')->get(['id', 'name', 'slug', 'status']);

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

        // Invalida somente os caches que dependem de marcas/categorias. Um flush
        // global deixava todas as páginas lentas logo após cada alternância.
        foreach ([
            'header_categories_tree',
            'header_main_categories',
            'all_categories_tree_active',
            'all_categories_tree_visible_catalog_v2',
            'filter_full_tree_active',
            'filter_brands_list_active',
            'filter_full_tree_visible_catalog_v2',
            'filter_brands_list_visible_catalog_v2',
            'categories_home_strip_random_15min',
            'home_brands_3d_random_15min',
            'home_brands_visible_catalog_v2_3d_random_15min',
            'home_brands_visible_catalog_v3_banner_priority_15min',
            'home_brands_visible_catalog_v4_image_15min',
            'home_brands_visible_catalog_v5_carousel_15min',
            'categories_all',
            'admin.dashboard.metrics',
            'bridal_active_brands',
            'bridal_active_products',
            'bridal_visible_catalog_v2_products',
            "brand_{$model->slug}",
            "brand_visible_catalog_v2_{$model->slug}",
        ] as $key) {
            Cache::forget($key);
        }

        $label = ($type === 'brand') ? __('messages.marca') : __('messages.categoria');
        $ativo = $model->status == 1;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => $model->status,
                'ativo' => $ativo,
                'message' => $label.' '.($ativo ? __('messages.ativada_sucesso') : __('messages.desativada_sucesso')),
            ]);
        }

        return back()->with('success', $label.' '.($ativo ? __('messages.ativada_sucesso') : __('messages.desativada_sucesso')));
    }
}
