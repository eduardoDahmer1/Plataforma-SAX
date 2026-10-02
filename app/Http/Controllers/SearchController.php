<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Currency;
use App\Models\Cupon;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;
use App\Services\ProductSearchService;
use App\Services\VisibleCatalogProductsService;
use App\Services\StoreTaxonomyService;
use App\Services\StorefrontLayoutService;
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
        'childcategory_id', 'slug', 'status', 'rating_average', 'rating_count',
    ];

    private static ?array $resolvedProductCols = null;

    private function productCols(): array
    {
        if (self::$resolvedProductCols !== null) {
            return self::$resolvedProductCols;
        }

        $table = (new Product())->getTable();
        $optional = ['size', 'color', 'colors', 'color_parent_id', 'stores', 'views'];

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
                'category:id,name',
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

    private function currencyContext(): array
    {
        $sessionCurrency = session('currency');
        $currencyId = is_object($sessionCurrency)
            ? ($sessionCurrency->id ?? null)
            : (is_array($sessionCurrency) ? ($sessionCurrency['id'] ?? $sessionCurrency[0] ?? null) : $sessionCurrency);

        $currency = $currencyId ? Currency::find($currencyId) : null;
        $currency ??= Currency::where('is_default', 1)->first() ?? Currency::first();
        $decimals = max(0, min(2, (int) ($currency?->decimal_digits ?? 2)));

        return [
            'sign' => trim((string) ($currency?->sign ?? 'US$')) ?: 'US$',
            'rate' => max(0.000001, (float) ($currency?->value ?? 1)),
            'decimals' => $decimals,
            'step' => $decimals === 0 ? 1 : (10 ** -$decimals),
        ];
    }

    private function requestedArray(Request $request, string $key): array
    {
        $value = $request->input($key, []);
        $value = is_array($value) ? $value : [$value];

        return collect($value)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->take(30)
            ->values()
            ->all();
    }

    private function activeCoupons()
    {
        return Cupon::vigentes()->get();
    }

    private function applyCouponScope(Builder $query, $coupons): void
    {
        if ($coupons->isEmpty()) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function (Builder $couponQuery) use ($coupons) {
            foreach ($coupons as $coupon) {
                $couponQuery->orWhere(function (Builder $candidate) use ($coupon) {
                    if ($coupon->preco_maximo_produto) {
                        $candidate->where('products.price', '<=', $coupon->preco_maximo_produto);
                    }

                    match ($coupon->modelo) {
                        'categoria' => $candidate->where('products.category_id', $coupon->categoria_id),
                        'marca' => $candidate->where('products.brand_id', $coupon->marca_id),
                        'produto' => $candidate->where('products.id', $coupon->produto_id),
                        'nome' => $candidate->whereRaw(
                            'LOWER(COALESCE(products.external_name, products.name)) LIKE ?',
                            ['%' . mb_strtolower((string) $coupon->nome_termo) . '%']
                        ),
                        default => $candidate->whereRaw('1 = 1'),
                    };
                });
            }
        });
    }

    private function applyFilters($query, Request $request, ?array $currency = null, bool $facetsOnly = false)
    {
        $currency ??= $this->currencyContext();
        $sizes = $this->requestedArray($request, 'sizes');
        $colors = collect($this->requestedArray($request, 'colors'))
            ->map(fn ($color) => strtoupper(ltrim((string) $color, '#')))
            ->filter(fn ($color) => preg_match('/^[0-9A-F]{6}$/', $color))
            ->values()->all();
        $hasMultipleColors = Product::supportsMultipleColors();

        $query
            ->when($request->brand, fn ($q) => $q->where('products.brand_id', $request->brand))
            ->when($request->category, fn ($q) => $q->where(function ($scope) use ($request) {
                $scope->where('products.category_id', $request->category)
                    ->orWhereHas('additionalCategories', fn ($assignment) => $assignment->where('category_id', $request->category));
            }))
            ->when($request->subcategory, fn ($q) => $q->where(function ($scope) use ($request) {
                $scope->where('products.subcategory_id', $request->subcategory)
                    ->orWhereHas('additionalCategories', fn ($assignment) => $assignment->where('subcategory_id', $request->subcategory));
            }))
            ->when($request->categoriasfilhas, fn ($q) => $q->where(function ($scope) use ($request) {
                $scope->where('products.childcategory_id', $request->categoriasfilhas)
                    ->orWhereHas('additionalCategories', fn ($assignment) => $assignment->where('childcategory_id', $request->categoriasfilhas));
            }));

        if ($facetsOnly) {
            return $query;
        }

        $query
            ->when($request->filled('min_price') && is_numeric($request->min_price), fn ($q) =>
                $q->where('products.price', '>=', max(0, (float) $request->min_price) / $currency['rate']))
            ->when($request->filled('max_price') && is_numeric($request->max_price), fn ($q) =>
                $q->where('products.price', '<=', max(0, (float) $request->max_price) / $currency['rate']))
            ->when($sizes, function ($q) use ($sizes) {
                $normalized = collect($sizes)->map(fn ($size) => strtoupper(str_replace(' ', '', (string) $size)))->all();
                $q->whereIn(\DB::raw("UPPER(REPLACE(TRIM(products.size), ' ', ''))"), $normalized);
            })
            ->when($colors, function ($q) use ($colors, $hasMultipleColors) {
                $q->where(function ($colorQuery) use ($colors, $hasMultipleColors) {
                    $colorQuery->whereIn(\DB::raw("UPPER(REPLACE(products.color, '#', ''))"), $colors);
                    if ($hasMultipleColors) {
                        foreach ($colors as $color) {
                            $colorQuery->orWhereJsonContains('products.colors', '#'.$color)
                                ->orWhereJsonContains('products.colors', $color);
                        }
                    }
                });
            });

        if ($request->boolean('coupon')) {
            $this->applyCouponScope($query, $this->activeCoupons());
        }

        return $query;
    }
    private function catalogFilterData(Builder $matchingProducts, Request $request, array $currency): array
    {
        $priceQuery = (clone $matchingProducts)->setEagerLoads([])->reorder();
        $baseMinimum = (float) ((clone $priceQuery)->min('products.price') ?? 0);
        $baseMaximum = (float) ((clone $priceQuery)->max('products.price') ?? 0);
        $minimum = floor(($baseMinimum * $currency['rate']) / $currency['step']) * $currency['step'];
        $maximum = ceil(($baseMaximum * $currency['rate']) / $currency['step']) * $currency['step'];

        $sizeOptions = (clone $matchingProducts)->setEagerLoads([])->reorder()
            ->select([])->selectRaw("UPPER(REPLACE(TRIM(products.size), ' ', '')) AS filter_key, COUNT(*) AS total")
            ->whereNotNull('products.size')->where('products.size', '<>', '')
            ->groupBy('filter_key')->orderByDesc('total')->limit(120)->get()
            ->filter(fn ($item) => $this->validSizeKey((string) $item->filter_key))
            ->map(fn ($item) => (object) [
                'key' => (string) $item->filter_key,
                'label' => $this->sizeLabel((string) $item->filter_key),
                'group' => $this->sizeGroup((string) $item->filter_key),
                'total' => (int) $item->total,
            ]);

        if ($this->contextSuggestsVolume($request)) {
            $sizeOptions = $sizeOptions->where('group', 'volume');
        } elseif ($this->contextSuggestsFootwear($request)) {
            $sizeOptions = $sizeOptions->whereIn('group', ['number', 'alphanumeric']);
        }

        $groupOrder = ['volume', 'letter', 'number', 'kids', 'alphanumeric', 'other'];
        $groupLabels = [
            'volume' => 'Volume',
            'letter' => 'Tamanho de roupa',
            'number' => 'Numeração',
            'kids' => 'Tamanho infantil',
            'alphanumeric' => 'Tamanho alfanumérico',
            'other' => 'Outros tamanhos',
        ];
        $sizeGroups = collect($groupOrder)->map(function ($group) use ($sizeOptions, $groupLabels) {
            $options = $sizeOptions->where('group', $group)->sortBy(function ($option) {
                return str_pad((string) preg_replace('/\D+/', '', $option->key), 5, '0', STR_PAD_LEFT).$option->key;
            })->values();

            return (object) ['key' => $group, 'label' => $groupLabels[$group], 'options' => $options];
        })->filter(fn ($group) => $group->options->isNotEmpty())->values();

        $colorCounts = [];
        $primaryColors = (clone $matchingProducts)->setEagerLoads([])->reorder()
            ->select([])->selectRaw("UPPER(REPLACE(products.color, '#', '')) AS filter_color, COUNT(*) AS total")
            ->whereNotNull('products.color')->where('products.color', '<>', '')
            ->groupBy('filter_color')->orderByDesc('total')->limit(80)->get();
        foreach ($primaryColors as $color) {
            $hex = strtoupper(ltrim((string) $color->filter_color, '#'));
            if (preg_match('/^[0-9A-F]{6}$/', $hex)) {
                $colorCounts[$hex] = ($colorCounts[$hex] ?? 0) + (int) $color->total;
            }
        }
        if (Schema::hasColumn((new Product())->getTable(), 'colors')) {
            $compositions = (clone $matchingProducts)->setEagerLoads([])->reorder()
                ->whereNotNull('products.colors')->where('products.colors', '<>', '')
                ->distinct()->limit(500)->pluck('products.colors');
            foreach ($compositions as $composition) {
                $values = is_array($composition) ? $composition : json_decode((string) $composition, true);
                foreach (is_array($values) ? $values : [] as $value) {
                    $hex = strtoupper(ltrim(trim((string) $value), '#'));
                    if (preg_match('/^[0-9A-F]{6}$/', $hex)) {
                        $colorCounts[$hex] = ($colorCounts[$hex] ?? 0) + 1;
                    }
                }
            }
        }
        arsort($colorCounts);
        $colors = collect($colorCounts)->take(32)->map(fn ($total, $hex) => (object) [
            'color' => '#'.$hex,
            'total' => $total,
        ])->values();

        $coupons = $this->activeCoupons();
        $couponProductCount = 0;
        if ($coupons->isNotEmpty()) {
            $couponQuery = (clone $matchingProducts)->setEagerLoads([])->reorder();
            $this->applyCouponScope($couponQuery, $coupons);
            $couponProductCount = $couponQuery->count();
        }

        $suggestedProducts = (clone $matchingProducts)->reorder()
            ->orderByDesc('products.views')->orderByDesc('products.id')->limit(6)->get();

        return [
            'currencyContext' => $currency,
            'priceBounds' => [
                'min' => $minimum,
                'max' => max($minimum, $maximum),
                'selected_min' => $request->filled('min_price') ? (float) $request->min_price : $minimum,
                'selected_max' => $request->filled('max_price') ? (float) $request->max_price : max($minimum, $maximum),
            ],
            'sizes' => $sizeOptions,
            'sizeGroups' => $sizeGroups,
            'colors' => $colors,
            'stores' => collect(),
            'couponProductCount' => $couponProductCount,
            'suggestedProducts' => $suggestedProducts,
        ];
    }

    private function validSizeKey(string $key): bool
    {
        return $key !== '' && $key !== '__MANUAL__' && mb_strlen($key) <= 20
            && preg_match('/^[0-9A-ZÀ-Ü.,+\/-]+$/u', $key);
    }

    private function sizeGroup(string $key): string
    {
        if (preg_match('/^\d+(?:[.,]\d+)?(?:ML|CL|L|OZ)$/', $key)) return 'volume';
        if (preg_match('/^(?:XXXS|XXS|XS|S|M|L|XL|XXL|XXXL|[2-6]XL|U|UNICO|UNICA)$/', $key)) return 'letter';
        if (preg_match('/^\d+(?:M|Y|A)$/', $key)) return 'kids';
        if (preg_match('/^\d+(?:[.,]\d+)?$/', $key)) return 'number';
        if (preg_match('/^[0-9]+[A-Z]+$/', $key)) return 'alphanumeric';
        return 'other';
    }

    private function sizeLabel(string $key): string
    {
        if (preg_match('/^(\d+(?:[.,]\d+)?)(ML|CL|L|OZ)$/', $key, $parts)) {
            return str_replace('.', ',', $parts[1]).' '.mb_strtolower($parts[2]);
        }
        return $key;
    }

    private function filterContextText(Request $request): string
    {
        $parts = collect([$request->search]);
        if ($request->category) $parts->push(Category::whereKey($request->category)->value('name'));
        if ($request->subcategory) $parts->push(Subcategory::whereKey($request->subcategory)->value('name'));
        if ($request->categoriasfilhas) $parts->push(CategoriasFilhas::whereKey($request->categoriasfilhas)->value('name'));

        return mb_strtolower($parts->filter()->implode(' '));
    }

    private function contextSuggestsVolume(Request $request): bool
    {
        return preg_match('/perfume|perfumer|fragr|bebida|vinho|vino|whisky|licor|cafe|café/', $this->filterContextText($request)) === 1;
    }

    private function contextSuggestsFootwear(Request $request): bool
    {
        return preg_match('/calçad|calcad|sapato|zapato|tenis|tênis|sandalia|sandália/', $this->filterContextText($request)) === 1;
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
                ->withCount(['products as matching_products_count' => $hasProducts])
                ->orderByDesc('matching_products_count')->orderBy('name')->get(['id', 'name']),

            'categories' => app(StorefrontLayoutService::class)->effective() === 'vista'
                ? collect()
                : app(StoreTaxonomyService::class)->categories(Category::query())
                    ->where('status', 1)
                    ->whereHas('products', $hasProducts)
                    ->withCount(['products as matching_products_count' => $hasProducts])
                    ->orderByDesc('matching_products_count')->orderBy('name')->get(['id', 'name', 'slug']),

            'subcategories' => app(StoreTaxonomyService::class)->subcategories(Subcategory::query())
                ->whereHas('products', $hasProducts)
                ->orderBy('name')->get(['id', 'name']),

            'categoriasfilhas' => app(StoreTaxonomyService::class)->childCategories(CategoriasFilhas::query())
                ->whereHas('products', $hasProducts)
                ->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function catalogResults(Request $request): array
    {
        $this->removeOpticalCategoryFilter($request);
        $currency = $this->currencyContext();
        $base = $this->baseQuery($request);
        $facetBase = $this->applyFilters(clone $base, $request, $currency, true);
        $sidebar = array_merge(
            $this->sidebarData(clone $facetBase),
            $this->catalogFilterData(clone $facetBase, $request, $currency)
        );
        $query = $this->applyFilters(clone $base, $request, $currency);
        $this->applySorting($query, $this->requestedSort($request), $request->search);

        $perPage = min(100, max(12, (int) $request->get('per_page', 36)));
        $paginated = $query->paginate($perPage)->withQueryString();
        $this->attachCardColors($paginated);

        return array_merge($sidebar, [
            'paginated' => $paginated,
            'request' => $request,
        ]);
    }

    public function index(Request $request)
    {
        $collection = self::COLLECTIONS[$request->string('collection')->toString()] ?? null;
        $results = $this->catalogResults($request);

        return view('search.search', array_merge($results, [
            'query' => $request->search,
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
        $this->removeOpticalCategoryFilter($request);
        $currency = $this->currencyContext();
        $base = $this->baseQuery($request);
        $facetBase = $this->applyFilters(clone $base, $request, $currency, true);
        $facetData = $this->catalogFilterData(clone $facetBase, $request, $currency);

        $query = $this->applyFilters(clone $base, $request, $currency);
        $this->applySorting($query, $this->requestedSort($request), $request->search);

        $perPage = min(100, max(12, (int) $request->get('per_page', 36)));
        $paginated = $query->paginate($perPage)->withQueryString();
        $this->attachCardColors($paginated);

        return response()->json([
            'html' => view('search.partials.grid', compact('paginated'))->render(),
            'pagination' => view('search.partials.pagination', compact('paginated'))->render(),
            'facets' => view('search.partials.dynamic-facets', array_merge($facetData, ['request' => $request]))->render(),
            'total' => $paginated->total(),
        ]);
    }
    private function removeOpticalCategoryFilter(Request $request): void
    {
        if (app(StorefrontLayoutService::class)->effective() === 'vista') {
            $request->query->remove('category');
        }
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
