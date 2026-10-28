<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductAiEditorContext
{
    /** Use current editor values for research without saving the product. */
    public function fromRequest(Product $product, Request $request): Product
    {
        if (! $request->has('catalog_context')) {
            return $product;
        }

        $validated = $request->validate([
            'catalog_context' => 'present|array:brand_id,category_id,subcategory_id,childcategory_id,size,color,name,description',
            'catalog_context.brand_id' => 'sometimes|nullable|integer|exists:brands,id',
            'catalog_context.category_id' => 'sometimes|nullable|integer|exists:categories,id',
            'catalog_context.subcategory_id' => 'sometimes|nullable|integer|exists:subcategories,id',
            'catalog_context.childcategory_id' => 'sometimes|nullable|integer|exists:childcategories,id',
            'catalog_context.size' => 'sometimes|nullable|string|max:50',
            'catalog_context.color' => ['sometimes', 'nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'catalog_context.name' => 'sometimes|nullable|string|max:255',
            'catalog_context.description' => 'sometimes|nullable|string|max:15000',
        ])['catalog_context'];

        if (isset($validated['color'])) {
            $validated['color'] = strtoupper($validated['color']);
        }

        $context = clone $product;
        $context->fill($validated);
        // Relations loaded by route binding or the controller must match the new IDs.
        foreach (['brand', 'category', 'subcategory', 'categoriasFilhas'] as $relation) {
            $context->unsetRelation($relation);
        }

        return $context;
    }
}
