<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\VisibleCatalogProductsService;
use Illuminate\Http\Request;

class BlogProductSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'ids' => ['nullable', 'array', 'max:20'],
            'ids.*' => ['required', 'integer', 'min:1'],
        ]);
        $ids = $data['ids'] ?? [];
        $visible = VisibleCatalogProductsService::builder();
        if ($ids) {
            $availableIds = $visible->whereIn('products.id', $ids)->pluck('products.id')->all();
            // Keep unavailable selections visible in the editor so they can be removed.
            $products = Product::withoutGlobalScope('minimum_visible_price')->whereIn('id', $ids)->get();
        } else {
            $terms = preg_split('/\s+/u', trim($data['q'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
            if (!$terms) {
                return response()->json(['products' => []]);
            }
            foreach ($terms as $term) {
                $visible->where(fn ($query) => $query->where('products.name', 'like', '%'.$term.'%')
                    ->orWhere('products.external_name', 'like', '%'.$term.'%')
                    ->orWhere('products.sku', 'like', '%'.$term.'%'));
            }
            $products = $visible->orderBy('products.name')->limit(30)->get();
            $availableIds = $products->pluck('id')->all();
        }

        return response()->json(['products' => $products->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name ?: $product->external_name,
            'sku' => $product->sku,
            'image' => $product->photo_url,
            'available' => in_array($product->id, $availableIds),
        ])->values()]);
    }
}
