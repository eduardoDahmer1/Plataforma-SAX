<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;

class SearchController extends Controller
{
    private const PRODUCT_COLS = [
        'id', 'name', 'external_name', 'sku', 'price', 'stock',
        'photo', 'brand_id', 'category_id', 'subcategory_id',
        'childcategory_id', 'slug', 'status',
    ];

    private function baseQuery(Request $request)
    {
        $query = Product::query()
            ->select(self::PRODUCT_COLS)
            ->with(['brand:id,name'])
            ->where('status', 1)
            ->where('product_role', 'P')
            ->where('stock', '>', 0)
            ->whereNotNull('photo')
            ->where('photo', '!=', '');

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(fn($q) => $q
                ->where('external_name', 'like', $term)
                ->orWhere('name', 'like', $term)
                ->orWhere('sku', 'like', $term)
            );
        }

        return $query;
    }

    private function applyFilters($query, Request $request)
    {
        return $query
            ->when($request->brand,           fn($q) => $q->where('brand_id',       $request->brand))
            ->when($request->category,        fn($q) => $q->where('category_id',    $request->category))
            ->when($request->subcategory,     fn($q) => $q->where('subcategory_id', $request->subcategory))
            ->when($request->categoriasfilhas,fn($q) => $q->where('childcategory_id',$request->categoriasfilhas))
            ->when($request->min_price,       fn($q) => $q->where('price', '>=',    $request->min_price))
            ->when($request->max_price,       fn($q) => $q->where('price', '<=',    $request->max_price));
    }

    private function applySorting($query, ?string $sortBy)
    {
        match ($sortBy) {
            'latest'     => $query->orderBy('created_at', 'desc'),
            'oldest'     => $query->orderBy('created_at', 'asc'),
            'name_az'    => $query->orderBy('external_name', 'asc'),
            'name_za'    => $query->orderBy('external_name', 'desc'),
            'price_low'  => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            default      => $query->orderBy('id', 'desc'),
        };
    }

    private function sidebarData($productIds): array
    {
        $hasProducts = fn($q) => $q->whereIn('id', $productIds);

        return [
            'brands' => Brand::where('status', 1)
                ->whereHas('products', $hasProducts)
                ->orderBy('name')->get(['id', 'name']),

            'categories' => Category::where('status', 1)
                ->whereHas('products', $hasProducts)
                ->orderBy('name')->get(['id', 'name', 'slug']),

            'subcategories' => Subcategory::whereHas('products', $hasProducts)
                ->orderBy('name')->get(['id', 'name']),

            'categoriasfilhas' => CategoriasFilhas::whereHas('products', $hasProducts)
                ->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function index(Request $request)
    {
        $base      = $this->baseQuery($request);
        $sidebar   = $this->sidebarData((clone $base)->pluck('id'));
        $query     = $this->applyFilters(clone $base, $request);
        $this->applySorting($query, $request->sort_by);

        return view('search.search', array_merge($sidebar, [
            'paginated' => $query->paginate($request->get('per_page', 36))->withQueryString(),
            'request'   => $request,
            'query'     => $request->search,
        ]));
    }

    public function ajaxSearch(Request $request)
    {
        $query = $this->applyFilters($this->baseQuery($request), $request);
        $this->applySorting($query, $request->sort_by);

        $paginated = $query->paginate((int) $request->get('per_page', 36))->withQueryString();

        return response()->json([
            'html'       => view('search.partials.grid',       compact('paginated'))->render(),
            'pagination' => view('search.partials.pagination', compact('paginated'))->render(),
            'total'      => $paginated->total(),
        ]);
    }

    public function autocomplete(Request $request)
    {
        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $term = '%' . $search . '%';

        $products = Product::query()
            ->select(['id', 'name', 'external_name', 'sku', 'price', 'photo', 'slug', 'brand_id', 'category_id'])
            ->with(['brand:id,name', 'category:id,name'])
            ->where('status', 1)
            ->where('product_role', 'P')
            ->where('stock', '>', 0)
            ->whereNotNull('photo')
            ->where('photo', '!=', '')
            ->where(fn($q) => $q
                ->where('name', 'like', $term)
                ->orWhere('external_name', 'like', $term)
                ->orWhere('sku', 'like', $term)
            )
            ->orderByRaw('CASE WHEN name = ? THEN 1 WHEN name LIKE ? THEN 2 ELSE 3 END', [$search, $search . '%'])
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json($products->map(fn($p) => [
            'name'     => $p->name ?? $p->external_name,
            'sku'      => $p->sku,
            'price'    => number_format($p->price, 2, '.', ','),
            'photo'    => str_contains($p->photo, 'http') ? $p->photo : asset('storage/' . $p->photo),
            'brand'    => $p->brand->name    ?? 'SAX',
            'category' => $p->category->name ?? '',
            'url'      => route('produto.show', $p->slug),
        ]));
    }
}
