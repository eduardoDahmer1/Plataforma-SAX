<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;
use App\Services\ProductSearchService;
use App\Services\VisibleCatalogProductsService;
use App\Services\StoreTaxonomyService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    private const COLLECTIONS = [
        'new-arrivals' => [
            'sort_by' => 'latest',
            'titles' => [
                'pt-br' => 'Recém-chegados',
                'es' => 'Recién llegados',
                'en' => 'New arrivals',
            ],
            'descriptions' => [
                'pt-br' => 'Os últimos produtos adicionados ao catálogo.',
                'es' => 'Los últimos productos agregados al catálogo.',
                'en' => 'The latest products added to the catalog.',
            ],
        ],
        'trending' => [
            'sort_by' => 'trending',
            'titles' => [
                'pt-br' => 'Tendências',
                'es' => 'Tendencias',
                'en' => 'Trending',
            ],
            'descriptions' => [
                'pt-br' => 'Os produtos mais vistos da loja.',
                'es' => 'Los productos más vistos de la tienda.',
                'en' => 'The most viewed products in the store.',
            ],
        ],
    ];

    public function __construct(private readonly ProductSearchService $productSearch)
    {
    }

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

        $existingOptional = Cache::remember('schema.search_product_optional_columns', now()->addHours(24), function () use ($optional, $table) {
            return array_values(array_filter($optional, fn($column) => Schema::hasColumn($table, $column)));
        });

        self::$resolvedProductCols = array_merge(self::BASE_PRODUCT_COLS, $existingOptional);

        return self::$resolvedProductCols;
    }

    private function qualifiedProductCols(): array
    {
        return array_map(fn (string $column) => 'products.' . $column, $this->productCols());
    }

    private function baseQuery(Request $request)
    {
        $query = VisibleCatalogProductsService::builder()
            ->select($this->qualifiedProductCols())
            ->with([
                'brand:id,name',
                'translations' => fn($query) => $query->where('locale', translation_locale()),
            ]);

        if ($request->filled('search')) {
            $this->productSearch->apply($query, $request->string('search')->toString());
        }

        return $query;
    }

    private function attachCardColors($paginated): void
    {
        $hasColorColumns = Cache::remember('schema.search_product_color_columns', now()->addHours(24), fn () =>
            Schema::hasColumn((new Product())->getTable(), 'color_parent_id')
            && Schema::hasColumn((new Product())->getTable(), 'color')
        );

        if (! $hasColorColumns) {
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

        $variantColumns = ['id', 'slug', 'color', 'color_parent_id'];
        if (Product::supportsMultipleColors()) {
            $variantColumns[] = 'colors';
        }

        $variants = VisibleCatalogProductsService::builder()
            ->select($variantColumns)
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

            $compositionKey = implode(',', $variant->product_colors);
            $familyColors[$familyId][$compositionKey] ??= [
                'id' => (int) $variant->id,
                'slug' => $variant->slug,
                'color' => $normalizedColor,
                'colors' => $variant->product_colors,
                'swatch_style' => $variant->color_swatch_style,
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
            ->when($request->brand,           fn($q) => $q->where('products.brand_id',       $request->brand))
            ->when($request->category,        fn($q) => $q->where('products.category_id',    $request->category))
            ->when($request->subcategory,     fn($q) => $q->where('products.subcategory_id', $request->subcategory))
            ->when($request->categoriasfilhas,fn($q) => $q->where('products.childcategory_id',$request->categoriasfilhas))
            ->when($request->min_price,       fn($q) => $q->where('products.price', '>=',    $request->min_price))
            ->when($request->max_price,       fn($q) => $q->where('products.price', '<=',    $request->max_price));
    }

    private function applySorting($query, ?string $sortBy, ?string $search = null)
    {
        if (!$sortBy && filled($search)) {
            $this->productSearch->applyRelevance($query, $search);
            $query->orderBy('products.id', 'desc');

            return;
        }

        match ($sortBy) {
            'latest'     => $query->orderBy('products.created_at', 'desc'),
            'trending'   => $query->orderByDesc('products.views')->orderByDesc('products.id'),
            'oldest'     => $query->orderBy('products.created_at', 'asc'),
            'name_az'    => $query->orderBy('products.external_name', 'asc'),
            'name_za'    => $query->orderBy('products.external_name', 'desc'),
            'price_low'  => $query->orderBy('products.price', 'asc'),
            'price_high' => $query->orderBy('products.price', 'desc'),
            default      => $query->orderBy('products.id', 'desc'),
        };
    }

    private function requestedSort(Request $request): ?string
    {
        if ($request->filled('sort_by')) {
            return $request->string('sort_by')->toString();
        }

        $collection = self::COLLECTIONS[$request->string('collection')->toString()] ?? null;

        return $collection['sort_by'] ?? null;
    }

    private function sidebarData(Builder $matchingProducts): array
    {
        // Mantém o mesmo conjunto de filtros, mas deixa o banco resolver os IDs
        // em subconsulta. Evita trazer milhares de IDs para a memória do PHP e
        // reenviá-los quatro vezes em cláusulas IN.
        $matchingProductIds = (clone $matchingProducts)
            ->setEagerLoads([])
            ->select('products.id');
        $hasProducts = fn($q) => $q->whereIn('products.id', clone $matchingProductIds);

        return [
            'brands' => Brand::where('status', 1)
                ->whereHas('products', $hasProducts)
                ->orderBy('name')->get(['id', 'name']),

            'categories' => app(StoreTaxonomyService::class)->categories(Category::query())
                ->where('status', 1)
                ->whereHas('products', $hasProducts)
                ->orderBy('name')->get(['id', 'name', 'slug']),

            'subcategories' => app(StoreTaxonomyService::class)->subcategories(Subcategory::query())
                ->whereHas('products', $hasProducts)
                ->orderBy('name')->get(['id', 'name']),

            'categoriasfilhas' => app(StoreTaxonomyService::class)->childCategories(CategoriasFilhas::query())
                ->whereHas('products', $hasProducts)
                ->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function index(Request $request)
    {
        $collection = self::COLLECTIONS[$request->string('collection')->toString()] ?? null;
        $base      = $this->baseQuery($request);
        $sidebar   = $this->sidebarData(clone $base);
        $query     = $this->applyFilters(clone $base, $request);
        $this->applySorting($query, $this->requestedSort($request), $request->search);

        $paginated = $query->paginate($request->get('per_page', 36))->withQueryString();
        $this->attachCardColors($paginated);

        return view('search.search', array_merge($sidebar, [
            'paginated' => $paginated,
            'request'   => $request,
            'query'     => $request->search,
            'collectionTitle' => $collection ? $this->localizedCollectionText($collection, 'titles') : null,
            'collectionDescription' => $collection ? $this->localizedCollectionText($collection, 'descriptions') : null,
        ]));
    }

    public function collection(Request $request, string $collection)
    {
        abort_unless(isset(self::COLLECTIONS[$collection]), 404);

        $request->merge([
            'collection' => $collection,
            'sort_by' => $request->filled('sort_by')
                ? $request->string('sort_by')->toString()
                : self::COLLECTIONS[$collection]['sort_by'],
        ]);

        return $this->index($request);
    }

    private function localizedCollectionText(array $collection, string $field): string
    {
        $locale = translation_locale();

        return $collection[$field][$locale]
            ?? $collection[$field][strtolower(str_replace('-', '_', app()->getLocale()))]
            ?? $collection[$field]['es'];
    }

    public function ajaxSearch(Request $request)
    {
        $query = $this->applyFilters($this->baseQuery($request), $request);
        $this->applySorting($query, $this->requestedSort($request), $request->search);

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

        $products = VisibleCatalogProductsService::builder()
            ->select([
                'products.id', 'products.name', 'products.external_name', 'products.sku',
                'products.price', 'products.photo', 'products.slug', 'products.brand_id',
                'products.category_id',
            ])
            ->with(['brand:id,name', 'category:id,name'])
            ->tap(fn (Builder $query) => $this->productSearch->apply($query, $search))
            ->tap(fn (Builder $query) => $this->productSearch->applyRelevance($query, $search))
            ->orderBy('products.name')
            ->limit(60)
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
