<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogAttribute;
use App\Models\CatalogAttributeOption;
use App\Models\Product;
use App\Services\CatalogAttributeService;
use App\Services\OpenAICatalogService;
use Illuminate\Http\Request;

class CatalogAttributeController extends Controller
{
    public function index(Request $request, CatalogAttributeService $catalog)
    {
        abort_unless($catalog->available(), 404);
        if ($request->expectsJson()) {
            return response()->json(['items' => $catalog->dictionary()]);
        }

        return view('admin.catalog-attributes.index', ['dictionary' => $catalog->dictionary()]);
    }

    public function store(Request $request, CatalogAttributeService $catalog)
    {
        abort_unless($catalog->available(), 404);
        $item = $catalog->saveDefinition($request->all());

        return $this->respond($request, $item->id, $catalog);
    }

    public function update(Request $request, CatalogAttribute $catalogAttribute, CatalogAttributeService $catalog)
    {
        abort_unless($catalog->available(), 404);
        $catalog->saveDefinition($request->all(), $catalogAttribute);

        return $this->respond($request, $catalogAttribute->id, $catalog);
    }

    public function option(Request $request, CatalogAttribute $catalogAttribute, CatalogAttributeService $catalog, ?CatalogAttributeOption $catalogOption = null)
    {
        abort_unless($catalog->available(), 404);
        $option = $catalog->saveOption($catalogAttribute, $request->all(), $catalogOption);

        return $request->expectsJson() ? response()->json(['item' => $catalog->dictionary()->firstWhere('id', $catalogAttribute->id), 'option_id' => $option->id])
            : back()->with('success', 'Opción guardada.');
    }

    public function values(Product $product, CatalogAttributeService $catalog)
    {
        abort_unless($catalog->enabledFor($product), 404);

        return response()->json(['items' => $catalog->dictionary(), 'effective' => $catalog->effective($product)]);
    }

    public function complete(Product $product, CatalogAttributeService $catalog, OpenAICatalogService $ai)
    {
        abort_unless($catalog->enabledFor($product), 404);
        if (! $catalog->missing($product)) {
            return response()->json(['proposal' => ['catalog_attributes' => []], 'message' => 'No hay atributos activos faltantes.']);
        }

        // Return suggestions to the existing editor; assignments persist only on its normal save.
        return response()->json($ai->generateAttributesOnly($product));
    }

    public function translate(Request $request, OpenAICatalogService $ai)
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'context' => 'nullable|string|max:255']);

        return response()->json($ai->translateCatalogLabel($data['name'], $data['context'] ?? ''));
    }

    private function respond(Request $request, int $id, CatalogAttributeService $catalog)
    {
        return $request->expectsJson() ? response()->json(['item' => $catalog->dictionary()->firstWhere('id', $id)])
            : redirect()->route('admin.catalog-attributes.index')->with('success', 'Atributo guardado.');
    }
}
