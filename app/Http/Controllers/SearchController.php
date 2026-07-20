<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    private const BASE_PRODUCT_COLS = [
        'id', 'name', 'external_name', 'sku', 'price', 'stock',
        'photo', 'gallery', 'brand_id', 'category_id', 'subcategory_id',
        'childcategory_id', 'slug', 'status',
    ];

    private static ?array $resolvedProductCols = null;

    private function productCols(): array
    {
        if (self::$resolvedProductCols !== null) {
            return self::$resolvedProductCols;
        }

        $table = (new Product())->getTable();
        $optional = ['size', 'color', 'colors', 'color_parent_id'];

        $existingOptional = array_values(array_filter($optional, fn($column) => Schema::hasColumn($table, $column)));

        self::$resolvedProductCols = array_merge(self::BASE_PRODUCT_COLS, $existingOptional);

        return self::$resolvedProductCols;
    }

    private function baseQuery(Request $request)
    {
        $query = Product::query()
            ->select($this->productCols())
            ->with([
                'brand:id,name',
                'translations' => fn($query) => $query->where('locale', translation_locale()),
            ])
            ->where('is_outlet', false)
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

    private function attachCardColors($paginated): void
    {
        if (!Schema::hasColumn((new Product())->getTable(), 'color_parent_id') || !Schema::hasColumn((new Product())->getTable(), 'color')) {
            return;
        }

        $items = $paginated->getCollection();
        if ($items->isEmpty()) {
            return;
        }

        $familyIds = $items
            ->map(fn($item) => (int) ($item->color_parent_id ?: $item->id))
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        if ($familyIds->isEmpty()) {
            return;
        }

        $variants = Product::query()
            ->select(['id', 'slug', 'color', 'color_parent_id'])
            ->where('is_outlet', false)
            ->where('status', 1)
            ->where('stock', '>', 0)
            ->where('product_role', 'P')
            ->where(function ($q) use ($familyIds) {
                $q->whereIn('id', $familyIds)
                    ->orWhereIn('color_parent_id', $familyIds);
            })
            ->get();

        $familyColors = [];
        foreach ($variants as $variant) {
            $familyId = (int) ($variant->color_parent_id ?: $variant->id);
            $color = strtoupper(trim((string) $variant->color));
            if ($color === '') {
                continue;
            }

            if (!isset($familyColors[$familyId])) {
                $familyColors[$familyId] = [];
            }

            $normalizedColor = str_starts_with($color, '#') ? $color : '#' . $color;
            if (!preg_match('/^#[0-9A-F]{6}$/', $normalizedColor)) {
                continue;
            }

            $familyColors[$familyId][$normalizedColor] ??= [
                'id' => (int) $variant->id,
                'slug' => $variant->slug,
                'color' => $normalizedColor,
            ];
        }

        $items->transform(function ($item) use ($familyColors) {
            $familyId = (int) ($item->color_parent_id ?: $item->id);
            $item->card_color_variants = array_values($familyColors[$familyId] ?? []);
            return $item;
        });

        $paginated->setCollection($items);
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

        $paginated = $query->paginate($request->get('per_page', 36))->withQueryString();
        $this->attachCardColors($paginated);

        return view('search.search', array_merge($sidebar, [
            'paginated' => $paginated,
            'request'   => $request,
            'query'     => $request->search,
        ]));
    }

    public function ajaxSearch(Request $request)
    {
        $query = $this->applyFilters($this->baseQuery($request), $request);
        $this->applySorting($query, $request->sort_by);

        $paginated = $query->paginate((int) $request->get('per_page', 36))->withQueryString();
        $this->attachCardColors($paginated);

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
            ->where('is_outlet', false)
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
