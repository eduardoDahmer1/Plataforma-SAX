<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductTranslation;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;
use App\Services\ImageConverterService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductControllerAdmin extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $brandId = $request->get('brand_id');
        $categoryId = $request->get('category_id');
        $statusFilter = $request->get('status_filter');
        $highlightFilter = $request->get('highlight_filter');
        $stockFilter = $request->get('stock_filter');
        $sortBy = $request->get('sort_by');
        $productType = $request->get('product_type');
        $outletFilter = $request->get('outlet_filter');
        $perPage = $request->get('per_page', 20);

        $productColumns = ['id', 'sku', 'name', 'external_name', 'slug', 'price', 'stock', 'photo', 'gallery', 'brand_id', 'category_id', 'subcategory_id', 'childcategory_id', 'status', 'is_outlet', 'product_role', 'highlights', 'parent_id'];

        $products = Product::select($productColumns)
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('external_name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%");
            }))
            ->when($brandId, fn($q) => $q->where('brand_id', $brandId))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->when($productType, function ($q) use ($productType) {
                if ($productType === 'parent') {
                    $q->whereNull('parent_id');
                } elseif ($productType === 'child') {
                    $q->whereNotNull('parent_id');
                } elseif ($productType === 'unrelated_parent') {
                    $q->whereNull('parent_id')
                        ->whereNotExists(function ($children) {
                            $children->selectRaw('1')
                                ->from('products as size_children')
                                ->whereColumn('size_children.parent_id', 'products.id');
                        })
                        ->where(function ($standaloneColor) {
                            $standaloneColor->whereNull('products.color_parent_id')
                                ->orWhereColumn('products.color_parent_id', 'products.id');
                        })
                        ->whereNotExists(function ($colorMembers) {
                            $colorMembers->selectRaw('1')
                                ->from('products as color_members')
                                ->whereColumn('color_members.color_parent_id', 'products.id')
                                ->whereColumn('color_members.id', '!=', 'products.id');
                        });
                }
            })
            ->when($outletFilter !== null && $outletFilter !== '', fn ($q) => $q->where('is_outlet', $outletFilter === 'outlet'))
            ->when($statusFilter, function ($q) use ($statusFilter) {
                switch ($statusFilter) {
                    case 'active':
                        $q->where('status', 1);
                        break;
                    case 'inactive':
                        $q->where('status', 0);
                        break;
                    case 'without_image':
                        $q->where(function ($q2) {
                            $q2->whereNull('photo')->orWhere('photo', '');
                        });
                        break;
                    case 'with_image':
                        $q->whereNotNull('photo');
                        break;
                    case 'out_of_stock':
                        $q->where('stock', 0);
                        break;
                    case 'in_stock':
                        $q->where('stock', '>', 0);
                        break;
                }
            })
            ->when($highlightFilter, fn($q) => $q->whereJsonContains('highlights', [$highlightFilter => '1']))
            ->when($stockFilter, fn($q) => $stockFilter === 'in_stock' ? $q->where('stock', '>', 0) : ($stockFilter === 'out_of_stock' ? $q->where('stock', 0) : null))
            ->when(
                $sortBy,
                function ($q) use ($sortBy) {
                    switch ($sortBy) {
                        case 'latest':
                            $q->orderBy('created_at', 'desc');
                            break;
                        case 'oldest':
                            $q->orderBy('created_at', 'asc');
                            break;
                        case 'last_edit':
                            $q->orderBy('updated_at', 'desc');
                            break;
                        case 'old_edit':
                            $q->orderBy('updated_at', 'asc');
                            break;
                        case 'price_low':
                            $q->orderBy('price', 'asc');
                            break;
                        case 'price_high':
                            $q->orderBy('price', 'desc');
                            break;
                        case 'name_az':
                            $q->orderByRaw("COALESCE(NULLIF(name, ''), external_name) asc");
                            break;
                        case 'name_za':
                            $q->orderByRaw("COALESCE(NULLIF(name, ''), external_name) desc");
                            break;
                        default:
                            $q->orderBy('id', 'desc');
                            break;
                    }
                },
                function ($q) {
                    $q->orderBy('id', 'desc');
                },
            )
            ->paginate($perPage)
            ->appends($request->query());

        $brands = Brand::where('status', 1)->orderBy('name')->get();
        $categories = Category::where('status', 1)->orderBy('name')->get();

        $highlights = [
            'destaque' => 'Destaques',
            'lancamentos' => 'Lançamentos',
        ];

        $products->getCollection()->transform(function ($product) {
            $product->imageUrl = $product->photo_url;
            return $product;
        });

        return view('admin.products.index', compact('products', 'brands', 'categories', 'search', 'brandId', 'categoryId', 'statusFilter', 'highlightFilter', 'stockFilter', 'sortBy', 'productType', 'outletFilter', 'perPage', 'highlights'));
    }

    public function outletForm()
    {
        $outletCount = DB::table('products')->where('is_outlet', true)->count();

        return view('admin.products.outlet', compact('outletCount'));
    }

    public function updateOutlet(Request $request)
    {
        $validated = $request->validate([
            'skus' => ['required', 'string', 'max:100000'],
            'action' => ['required', 'in:outlet,restore'],
        ], [
            'skus.required' => 'Informe pelo menos um SKU.',
            'action.in' => 'Selecione uma ação válida.',
        ]);

        $skus = collect(preg_split('/[\s,;]+/u', $validated['skus'], -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($sku) => trim($sku))
            ->filter()
            ->unique(fn ($sku) => mb_strtolower($sku))
            ->values();

        if ($skus->count() > 5000) {
            throw ValidationException::withMessages([
                'skus' => 'Envie no máximo 5.000 SKUs por operação.',
            ]);
        }

        $foundProducts = DB::table('products')->whereIn('sku', $skus)->get(['id', 'sku']);
        $foundSkus = $foundProducts->pluck('sku');
        $foundProductIds = $foundProducts->pluck('id');
        $foundLookup = $foundSkus->mapWithKeys(fn ($sku) => [mb_strtolower($sku) => true]);
        $missingSkus = $skus
            ->reject(fn ($sku) => isset($foundLookup[mb_strtolower($sku)]))
            ->values();
        $updated = 0;

        DB::transaction(function () use ($foundSkus, $foundProductIds, $validated, &$updated) {
            foreach ($foundSkus->chunk(500) as $chunk) {
                $query = DB::table('products')->whereIn('sku', $chunk)->lockForUpdate();

                if ($validated['action'] === 'outlet') {
                    $updated += $query->update([
                        'status_before_outlet' => DB::raw('CASE WHEN is_outlet = 0 THEN status ELSE status_before_outlet END'),
                        'is_outlet' => true,
                        'status' => 0,
                        'updated_by' => auth()->id(),
                        'admin_edited_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $updated += $query->update([
                        'status' => DB::raw('COALESCE(status_before_outlet, 1)'),
                        'is_outlet' => false,
                        'status_before_outlet' => null,
                        'updated_by' => auth()->id(),
                        'admin_edited_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($validated['action'] === 'outlet' && $foundProductIds->isNotEmpty()) {
                DB::table('carts')->whereIn('product_id', $foundProductIds)->delete();
            }
        });

        Cache::flush();

        $actionLabel = $validated['action'] === 'outlet' ? 'enviados para outlet' : 'restaurados ao estado normal';

        return redirect()
            ->route('admin.products.outlet.form')
            ->with('success', "{$updated} produto(s) {$actionLabel}.")
            ->with('missing_skus', $missingSkus->all());
    }

    public function create()
    {
        $brands = Brand::all();
        $categories = Category::all();
        $subcategories = collect();
        $categoriasfilhas = collect();
        $products = Product::all();

        return view('admin.products.create', compact('brands', 'categories', 'subcategories', 'categoriasfilhas', 'products'));
    }

    public function search(Request $request)
    {
        $q = $request->get('q');
        $excludeId = $request->integer('exclude_id');
        $context = $request->get('context');
        $currentColorKey = (string) $request->get('current_color_key', '');
        $currentReferenceKey = (string) $request->get('current_reference_key', '');

        $products = Product::where(function ($query) use ($q) {
            $query
                ->where('name', 'like', "%{$q}%")
                ->orWhere('external_name', 'like', "%{$q}%")
                ->orWhere('sku', 'like', "%{$q}%");
        })
            ->when($excludeId, fn($query) => $query->where('id', '!=', $excludeId))
            ->orderBy('external_name')
            // Uma mesma cor pode possuir muitos tamanhos. Na busca de família de
            // cor precisamos de uma amostra maior antes de eliminar as repetições.
            ->limit($context === 'color' ? 250 : 80)
            ->get(['id', 'sku', 'name', 'external_name', 'photo', 'color', 'size', 'stock', 'parent_id', 'product_role']);

        if (in_array($context, ['size', 'color'], true)) {
            $products = $products
                ->when($currentReferenceKey !== '', fn ($items) => $items->filter(
                    fn (Product $product) => $product->relationshipReferenceKey() === $currentReferenceKey
                ));
        }

        if ($context === 'size' && $currentColorKey !== '') {
            $products = $products
                ->filter(fn (Product $product) => $product->relationshipColorKey() === $currentColorKey)
                ->values();
        }

        if ($context === 'color') {
            $products = $products
                ->filter(fn (Product $product) => $product->relationshipColorKey() !== '')
                ->when($currentColorKey !== '', fn ($items) => $items->reject(
                    fn (Product $product) => $product->relationshipColorKey() === $currentColorKey
                ))
                ->groupBy(fn (Product $product) => $product->relationshipColorKey())
                ->map(fn ($colorProducts) => $colorProducts
                    ->sortByDesc(fn (Product $candidate) => [
                        $candidate->product_role === 'P' ? 1 : 0,
                        filled($candidate->photo) ? 1 : 0,
                        (int) $candidate->stock,
                    ])
                    ->first())
                ->values();
        }

        $products->transform(function ($product) {
            $product->photo = $product->photo_url;
            $product->size = $product->inferredSize();
            $product->reference = $product->referenceLabel();
            $product->inferred_color = $product->inferredColorKey();
            $product->color_code = $product->inferredColorCode();

            return $product;
        });

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku',
            'external_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'childcategory_id' => 'nullable|exists:categorias-filhas,id',
            'photo' => 'nullable|image|max:10240',
            'gallery.*' => 'nullable|image|max:10240',
            'highlights' => 'nullable|array',
            'parent_id' => 'nullable|exists:products,id',
            'color' => 'nullable|string|max:7',
            'colors_values' => 'nullable|array|max:8',
            'colors_values.*' => ['string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_parent_id' => 'nullable|array',
            'color_parent_id.*' => 'nullable|exists:products,id',
            'size' => 'nullable|string|max:50',
        ]);

        $data = $request->only(['sku', 'external_name', 'description', 'price', 'stock', 'brand_id', 'category_id', 'subcategory_id', 'childcategory_id', 'parent_id', 'color', 'size']);

        $data['highlights'] = $request->input('highlights', []);
        $data['product_role'] = 'P';
        $data['updated_by'] = auth()->id();
        $data['admin_edited_at'] = now();

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        if ($request->has('colors_values')) {
            $colors = array_values(array_unique(array_map('strtoupper', (array) $request->input('colors_values'))));
            $data['color'] = $colors[0] ?? null;
            if (Product::supportsMultipleColors()) $data['colors'] = $colors;
        }

        if ($request->hasFile('gallery')) {
            $galleryPaths = [];
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $this->convertToWebp($image, 'gallery');
            }
            $data['gallery'] = json_encode($galleryPaths);
        }

        Product::create($data);
        return redirect()->route('admin.products.index')->with('success', 'Produto criado com sucesso!');
    }

    public function edit($id)
    {
        $item = Product::with([
            'brand:id,name,slug',
            'category:id,name,slug',
            'subcategory:id,name,slug,category_id',
            'categoriasfilhas:id,name,slug,subcategory_id',
            'translations:id,product_id,locale,name,details',
            'parent:id,name,external_name',
        ])->findOrFail($id);

        $brands = Brand::where('status', 1)->orWhere('id', $item->brand_id)->orderBy('name', 'asc')->get();

        $categories = Category::where('status', 1)->orWhere('id', $item->category_id)->orderBy('name', 'asc')->get();

        $subcategories = Subcategory::orderBy('name')->get();
        $categoriasfilhas = CategoriasFilhas::orderBy('name')->get();

        $itemReferenceKey = $item->relationshipReferenceKey();
        $itemColorKey = $item->relationshipColorKey();
        $item->selected_size_children = Product::where('parent_id', $item->id)
            ->where('product_role', 'F')
            ->get(['id', 'name', 'external_name', 'color', 'size'])
            ->filter(fn (Product $child) =>
                $child->relationshipReferenceKey() === $itemReferenceKey
                && $child->relationshipColorKey() === $itemColorKey
            )
            ->pluck('id')
            ->values()
            ->toArray();
        $familyRootId = !empty($item->color_parent_id) ? (int) $item->color_parent_id : (int) $item->id;
        $item->selected_color_family_members = Product::where('color_parent_id', $familyRootId)->where('product_role', 'P')->where('id', '!=', $item->id)->pluck('id')->toArray();

        $sizeChildrenProducts = Product::whereIn('id', $item->selected_size_children)
            ->get(['id', 'sku', 'name', 'external_name', 'photo', 'color', 'size'])
            ->each(fn (Product $child) => $child->size = $child->inferredSize())
            ->keyBy('id');

        $colorFamilyProducts = Product::whereIn('id', $item->selected_color_family_members)
            ->get(['id', 'sku', 'name', 'external_name', 'photo', 'color', 'size'])
            ->each(fn (Product $member) => $member->size = $member->inferredSize())
            ->keyBy('id');

        $translationsByLocale = $item->translations->keyBy('locale');
        [$suggestedColor, $suggestedColorSource] = $this->suggestColorForProduct($item);

        if (is_string($item->parent_id) && str_contains($item->parent_id, ',')) {
            $item->parent_id =
                collect(explode(',', $item->parent_id))
                    ->map(fn($parentId) => (int) trim($parentId))
                    ->first(fn($parentId) => $parentId > 0) ?:
                null;
        } elseif (is_array($item->parent_id)) {
            $item->parent_id = collect($item->parent_id)->map(fn($parentId) => (int) $parentId)->first(fn($parentId) => $parentId > 0) ?: null;
        }

        return view('admin.products.edit', compact('item', 'brands', 'categories', 'subcategories', 'categoriasfilhas', 'sizeChildrenProducts', 'colorFamilyProducts', 'translationsByLocale', 'suggestedColor', 'suggestedColorSource'));
    }

    private function suggestColorForProduct(Product $product): array
    {
        $currentColor = strtoupper(trim((string) $product->color));
        $hasValidCurrentColor = (bool) preg_match('/^#[0-9A-F]{6}$/', $currentColor);

        $colorFile = public_path('data/color.json');
        $availableColors = is_file($colorFile)
            ? (array) json_decode((string) file_get_contents($colorFile), true)
            : [];
        $searchableName = $this->normalizeColorText(implode(' ', array_filter([
            $product->name,
            $product->external_name,
        ])));

        $semanticVocabulary = collect($availableColors)
            ->map(fn ($colorName, $hex) => ['hex' => strtoupper((string) $hex), 'name' => (string) $colorName])
            ->concat([
                ['hex' => '#000080', 'name' => 'Marino'],
                ['hex' => '#000080', 'name' => 'Navy'],
                ['hex' => '#FF0000', 'name' => 'Rojo'],
                ['hex' => '#FFFFFF', 'name' => 'Blanco'],
                ['hex' => '#000000', 'name' => 'Negro'],
                ['hex' => '#FFFF00', 'name' => 'Amarillo'],
                ['hex' => '#808080', 'name' => 'Gris'],
                ['hex' => '#A52A2A', 'name' => 'Marrón'],
                ['hex' => '#800080', 'name' => 'Morado'],
                ['hex' => '#000080', 'name' => 'Azul Marino'],
                ['hex' => '#FF0000', 'name' => 'Red'],
                ['hex' => '#FFFFFF', 'name' => 'White'],
                ['hex' => '#000000', 'name' => 'Black'],
            ]);

        $semanticMatches = $semanticVocabulary
            ->map(function ($entry) use ($searchableName) {
                $colorName = $entry['name'];
                $normalizedName = $this->normalizeColorText((string) $colorName);

                return [
                    'hex' => $entry['hex'],
                    'name' => (string) $colorName,
                    'normalized' => $normalizedName,
                    'matches' => $normalizedName !== ''
                        && preg_match('/(?:^|\s)' . preg_quote($normalizedName, '/') . '(?:$|\s)/', $searchableName),
                ];
            })
            ->filter(fn ($entry) => $entry['matches'])
            ->sortByDesc(fn ($entry) => strlen($entry['normalized']))
            ->first();

        if ($semanticMatches) {
            // #000000 era o valor padrão histórico de muitos cadastros. Quando o
            // próprio nome informa outra cor, a informação explícita é mais segura.
            if ($hasValidCurrentColor
                && ($currentColor !== '#000000' || $semanticMatches['hex'] === '#000000')) {
                return [null, null];
            }

            return [$semanticMatches['hex'], 'nome comercial: ' . $semanticMatches['name']];
        }

        if ($hasValidCurrentColor) {
            return [null, null];
        }

        $colorCode = $product->inferredColorCode();
        $colorKey = $product->inferredColorKey();
        if ($colorCode === '' || $colorKey === '') {
            return [null, null];
        }

        $historicalColors = DB::table('products')
            ->where('id', '!=', $product->id)
            ->whereNotNull('color')
            ->where(function ($query) use ($colorCode) {
                $query->where('external_name', 'like', '%*' . $colorCode . '%')
                    ->orWhere('name', 'like', '%*' . $colorCode . '%');
            })
            ->limit(500)
            ->get(['name', 'external_name', 'color'])
            ->filter(function ($candidate) use ($colorKey) {
                $candidateKeys = collect([$candidate->external_name, $candidate->name])
                    ->filter()
                    ->map(fn ($name) => Product::referenceParts((string) $name)['color_key']);

                return $candidateKeys->contains($colorKey)
                    && preg_match('/^#[0-9A-F]{6}$/i', trim((string) $candidate->color));
            })
            ->map(fn ($candidate) => strtoupper(trim((string) $candidate->color)))
            ->countBy()
            ->sortDesc();

        $historicalHex = $historicalColors->keys()->first();

        return $historicalHex
            ? [$historicalHex, 'código comercial *' . $colorCode]
            : [null, null];
    }

    private function normalizeColorText(string $value): string
    {
        return trim(preg_replace('/[^A-Z0-9]+/', ' ', strtoupper(Str::ascii($value))) ?: '');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'childcategory_id' => 'nullable|exists:childcategories,id',
            'photo' => 'nullable|image|max:10240',
            'gallery.*' => 'nullable|image|max:10240',
            'highlights' => 'nullable|array',
            'parent_id' => 'nullable|array',
            'color_parent_id' => 'nullable|array',
            'force_as_parent' => 'nullable|boolean',
            'stores' => 'nullable|array',
            'size' => 'nullable|string|max:50',
            'colors_values' => 'nullable|array|max:8',
            'colors_values.*' => ['string', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            'translate' => 'nullable|array',
            'translate.*.name' => 'nullable|string|max:255',
            'translate.*.details' => 'nullable|string|max:5000',
        ]);

        try {
            return DB::transaction(function () use ($request, $product) {
                $isCurrentlySizeVariant = ($product->product_role ?? null) === 'F' || !empty($product->parent_id);
                $promoteToParent = $request->boolean('force_as_parent') && $isCurrentlySizeVariant;
                $originalFamilyRootId = !empty($product->color_parent_id) ? (int) $product->color_parent_id : (int) $product->id;
                $selectedSizeChildIds = array_values(array_filter(array_unique(array_filter((array) $request->input('parent_id', []))), fn($childId) => (int) $childId !== (int) $product->id));
                $selectedColorFamilyMemberIds = array_values(array_filter(array_unique(array_filter((array) $request->input('color_parent_id', []))), fn($memberId) => (int) $memberId !== (int) $product->id));

                if ($isCurrentlySizeVariant) {
                    $selectedSizeChildIds = [];
                    $selectedColorFamilyMemberIds = [];
                }

                if (!empty($selectedSizeChildIds)) {
                    $currentReferenceKey = $product->relationshipReferenceKey();
                    $currentColorKey = $product->relationshipColorKey();
                    $selectedSizeChildIds = Product::whereIn('id', $selectedSizeChildIds)
                        ->get(['id', 'name', 'external_name', 'color', 'size'])
                        ->filter(fn (Product $candidate) =>
                            $candidate->relationshipReferenceKey() === $currentReferenceKey
                            && $candidate->relationshipColorKey() === $currentColorKey
                        )
                        ->pluck('id')
                        ->map(fn ($childId) => (int) $childId)
                        ->values()
                        ->toArray();
                }

                // Uma cor que foi relacionada equivocadamente como variante de
                // tamanho pode voltar a ser a âncora da própria cor. Os demais
                // tamanhos com a mesma referência/cor acompanham essa nova âncora.
                $selectedColorCandidates = Product::whereIn('id', $selectedColorFamilyMemberIds)->get();
                foreach ($selectedColorCandidates as $candidate) {
                    if ($candidate->product_role !== 'F' && empty($candidate->parent_id)) {
                        continue;
                    }

                    $oldParentId = (int) $candidate->parent_id;
                    $candidateReferenceKey = $candidate->relationshipReferenceKey();
                    $candidateColorKey = $candidate->relationshipColorKey();
                    $sameColorSiblingIds = Product::where('parent_id', $oldParentId)
                        ->where('id', '!=', $candidate->id)
                        ->get(['id', 'name', 'external_name', 'color', 'size'])
                        ->filter(fn (Product $sibling) =>
                            $sibling->relationshipReferenceKey() === $candidateReferenceKey
                            && $sibling->relationshipColorKey() === $candidateColorKey
                        )
                        ->pluck('id');

                    Product::where('id', $candidate->id)->update([
                        'parent_id' => null,
                        'color_parent_id' => $candidate->id,
                        'product_role' => 'P',
                    ]);

                    if ($sameColorSiblingIds->isNotEmpty()) {
                        Product::whereIn('id', $sameColorSiblingIds)->update([
                            'parent_id' => $candidate->id,
                            'color_parent_id' => $candidate->id,
                            'product_role' => 'F',
                        ]);
                    }
                }

                $selectedColorFamilyMemberIds = Product::whereIn('id', $selectedColorFamilyMemberIds)->where('product_role', 'P')->pluck('id')->map(fn($memberId) => (int) $memberId)->unique()->values()->toArray();
                $selectedColorFamilyMembers = Product::whereIn('id', $selectedColorFamilyMemberIds)->get(['id', 'color_parent_id']);
                $resolveExistingFamilyRoot = function (Product $anchor): ?int {
                    $candidateRootId = !empty($anchor->color_parent_id) ? (int) $anchor->color_parent_id : (int) $anchor->id;
                    $hasOtherAnchorsInFamily = Product::where('product_role', 'P')
                        ->where('id', '!=', $anchor->id)
                        ->where(function ($query) use ($candidateRootId) {
                            $query->where('id', $candidateRootId)->orWhere('color_parent_id', $candidateRootId);
                        })
                        ->exists();

                    if ($candidateRootId !== (int) $anchor->id || $hasOtherAnchorsInFamily) {
                        return $candidateRootId;
                    }

                    return null;
                };
                $selectedExistingFamilyRoots = $selectedColorFamilyMembers->map(fn($member) => $resolveExistingFamilyRoot($member))->filter(fn($rootId) => !is_null($rootId))->map(fn($rootId) => (int) $rootId)->unique()->values()->toArray();

                if (count($selectedExistingFamilyRoots) > 1) {
                    throw ValidationException::withMessages([
                        'color_parent_id' => 'No puedes mezclar productos de familias de color distintas en la misma relación.',
                    ]);
                }

                $shouldSyncColorFamily = !$isCurrentlySizeVariant || $promoteToParent;
                $targetFamilyRootId = $promoteToParent ? (int) $product->id : ($shouldSyncColorFamily ? $selectedExistingFamilyRoots[0] ?? (int) $product->id : $originalFamilyRootId);
                $currentFamilyAnchorIds = $shouldSyncColorFamily
                    ? Product::where('product_role', 'P')
                        ->where(function ($query) use ($originalFamilyRootId) {
                            $query->where('id', $originalFamilyRootId)->orWhere('color_parent_id', $originalFamilyRootId);
                        })
                        ->pluck('id')
                        ->map(fn($anchorId) => (int) $anchorId)
                        ->unique()
                        ->values()
                        ->toArray()
                    : [];
                $desiredColorAnchorIds = $shouldSyncColorFamily ? array_values(array_unique(array_merge([(int) $product->id], $selectedColorFamilyMemberIds))) : [];

                $data = $request->only(['sku', 'brand_id', 'category_id', 'subcategory_id', 'childcategory_id', 'size', 'price', 'stock']);
                $data['size'] = filled($data['size'] ?? null) ? trim((string) $data['size']) : $product->inferredSize();

                if ($request->has('translate.pt-br')) {
                    $data['name'] = $request->input('translate.pt-br.name');
                    $data['description'] = $request->input('translate.pt-br.details');
                }

                if ($request->has('no_color')) {
                    $data['color'] = null;
                    if (Product::supportsMultipleColors()) $data['colors'] = [];
                } elseif ($request->has('colors_values')) {
                    $colors = array_values(array_unique(array_map('strtoupper', (array) $request->input('colors_values'))));
                    $data['color'] = $colors[0] ?? null;
                    if (Product::supportsMultipleColors()) $data['colors'] = $colors;
                }

                if ($request->hasFile('photo')) {
                    if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                        $usedElsewhere = Product::where('photo', $product->photo)->where('id', '!=', $product->id)->exists();

                        if (!$usedElsewhere) {
                            Storage::disk('public')->delete($product->photo);
                        }
                    }
                    $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
                }

                $currentGallery = is_string($product->gallery) ? json_decode($product->gallery, true) : $product->gallery ?? [];
                if (!is_array($currentGallery)) {
                    $currentGallery = [];
                }

                if ($request->hasFile('gallery')) {
                    foreach ($request->file('gallery') as $image) {
                        $newImagePath = $this->convertToWebp($image, 'gallery');
                        if ($newImagePath) {
                            $currentGallery[] = $newImagePath;
                        }
                    }
                }
                $data['gallery'] = json_encode(array_values($currentGallery));

                $photoForStatus = $data['photo'] ?? $product->photo;
                $galleryForStatus = $data['gallery'] ?? $product->gallery;
                $descriptionForStatus = $data['description'] ?? $product->description;
                $data['status'] = Product::hasUsableImage($photoForStatus, $galleryForStatus)
                    && (float) $data['price'] > 0
                    && (int) $data['stock'] > 0
                    && trim((string) $descriptionForStatus) !== ''
                    ? 1 : 0;

                $data['highlights'] = json_encode($request->input('highlights', []));
                $data['stores'] = json_encode($request->input('stores', []));
                $data['color_parent_id'] = $targetFamilyRootId;
                $data['product_role'] = $promoteToParent || !empty($selectedSizeChildIds) || !empty($selectedColorFamilyMemberIds) ? 'P' : ($product->product_role ?: 'P');
                if ($data['product_role'] === 'P') {
                    $data['parent_id'] = null;
                }

                if (auth()->check()) {
                    $data['updated_by'] = auth()->id();
                    $data['admin_edited_at'] = now();
                }

                $product->update($data);
                $resolvedParentColor = $data['color'] ?? $product->color;

                if ($request->has('translate')) {
                    foreach ($request->input('translate') as $locale => $translationData) {
                        if (empty($translationData['name']) && empty($translationData['details'])) {
                            continue;
                        }

                        ProductTranslation::updateOrCreate(
                            [
                                'product_id' => $product->id,
                                'locale'     => $locale,
                            ],
                            [
                                'name'    => $translationData['name'] ?? null,
                                'details' => $translationData['details'] ?? null,
                            ]
                        );
                    }
                }

                Product::where('parent_id', $product->id)
                    ->whereNotIn('id', $selectedSizeChildIds)
                    ->update([
                        'parent_id' => null,
                        'color_parent_id' => DB::raw('id'),
                        'product_role' => 'P',
                    ]);

                if (!empty($selectedSizeChildIds)) {
                    $children = Product::whereIn('id', $selectedSizeChildIds)
                        ->where('id', '!=', $product->id)
                        ->get();

                    foreach ($children as $child) {
                        $childData = [
                            'parent_id' => $product->id,
                            'color_parent_id' => $targetFamilyRootId,
                            'color' => $resolvedParentColor,
                            'size' => $child->inferredSize(),
                            'product_role' => 'F',
                            'name' => $product->name,
                            'description' => $product->description,
                            'price' => $product->price,
                            'previous_price' => $product->previous_price,
                            'promotion_price' => $product->promotion_price,
                            'brand_id' => $product->brand_id,
                            'category_id' => $product->category_id,
                            'subcategory_id' => $product->subcategory_id,
                            'childcategory_id' => $product->childcategory_id,
                            'photo' => $product->photo,
                            'gallery' => $data['gallery'],
                            'highlights' => $product->highlights,
                            'stores' => $data['stores'],
                        ];

                        if (Product::supportsMultipleColors()) {
                            $childData['colors'] = json_encode($product->product_colors);
                        }

                        if (!$child->is_outlet) {
                            $childData['status'] = $product->status;
                        }

                        Product::where('id', $child->id)->update($childData);

                        if ($request->has('translate')) {
                            foreach ($request->input('translate') as $locale => $translationData) {
                                ProductTranslation::updateOrCreate(
                                    [
                                        'product_id' => $child->id,
                                        'locale' => $locale,
                                    ],
                                    [
                                        'name' => $translationData['name'] ?? null,
                                        'details' => $translationData['details'] ?? null,
                                    ],
                                );
                            }
                        }
                    }
                }

                if ($shouldSyncColorFamily) {
                    $shouldDetachCurrentFamilyBranches = $targetFamilyRootId === $originalFamilyRootId || (int) $product->id === $originalFamilyRootId;
                    $colorAnchorsToDetach = $shouldDetachCurrentFamilyBranches ? array_values(array_diff($currentFamilyAnchorIds, $desiredColorAnchorIds)) : [];
                    foreach ($colorAnchorsToDetach as $anchorId) {
                        Product::where('id', $anchorId)
                            ->where('product_role', 'P')
                            ->update([
                                'color_parent_id' => $anchorId,
                            ]);

                        Product::where('parent_id', $anchorId)
                            ->where('product_role', 'F')
                            ->update([
                                'color_parent_id' => $anchorId,
                            ]);
                    }

                    $colorAnchorsToAttach = Product::whereIn('id', $desiredColorAnchorIds)->where('product_role', 'P')->pluck('id')->map(fn($anchorId) => (int) $anchorId)->unique()->values()->toArray();

                    foreach ($colorAnchorsToAttach as $anchorId) {
                        Product::where('id', $anchorId)
                            ->where('product_role', 'P')
                            ->update([
                                'color_parent_id' => $targetFamilyRootId,
                            ]);

                        Product::where('parent_id', $anchorId)
                            ->where('product_role', 'F')
                            ->update([
                                'color_parent_id' => $targetFamilyRootId,
                            ]);
                    }
                }

                $returnTo = $request->input('return_to');
                $redirectUrl = $returnTo && str_starts_with($returnTo, config('app.url')) ? $returnTo : route('admin.products.index');

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Produto e variações atualizados!',
                        'redirect' => $redirectUrl,
                    ]);
                }

                if ($returnTo && str_starts_with($returnTo, config('app.url'))) {
                    return redirect($returnTo)->with('success', 'Produto e variações atualizados!');
                }

                return redirect()->route('admin.products.index')->with('success', 'Produto actualizado con éxito!');
            });
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Erro no Update de Produto: ' . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao salvar: ' . $e->getMessage(),
                ], 500);
            }

            return back()
                ->with('error', 'Erro ao salvar: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function convertToWebp($image, $type)
    {
       
        try {
            return app(ImageConverterService::class)->toWebp($image, "products/{$type}", [
                'quality' => 85,
                'strict'  => true,
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro na conversão WebP: ' . $e->getMessage());
            return null;
        }
    }

    private function deleteProductImages($product)
    {
        if ($product->photo && Storage::disk('public')->exists($product->photo)) {
            $usedElsewhere = Product::where('photo', $product->photo)->where('id', '!=', $product->id)->exists();
            if (!$usedElsewhere) {
                Storage::disk('public')->delete($product->photo);
            }
        }

        if ($product->gallery) {
            $gallery = is_string($product->gallery) ? json_decode($product->gallery, true) : (array) $product->gallery;
            foreach ($gallery as $img) {
                if (Storage::disk('public')->exists($img)) {
                    $usedElsewhere = Product::where('gallery', 'like', "%{$img}%")
                        ->where('id', '!=', $product->id)
                        ->exists();
                    if (!$usedElsewhere) {
                        Storage::disk('public')->delete($img);
                    }
                }
            }
        }
    }

    public function deleteGalleryImage(Request $request, $productId, $imageName)
    {
        $product = Product::findOrFail($productId);

        if (!$product->gallery) {
            return redirect()->back()->with('error', 'Produto não possui galeria.');
        }

        $gallery = is_array($product->gallery) ? $product->gallery : json_decode($product->gallery, true);

        if (!is_array($gallery)) {
            $gallery = [];
        }

        $imagePath = null;
        foreach ($gallery as $key => $img) {
            if (basename($img) === $imageName) {
                $imagePath = $img;
                unset($gallery[$key]);
                break;
            }
        }

        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            $usedElsewhere = Product::where('gallery', 'like', "%{$imagePath}%")
                ->where('id', '!=', $product->id)
                ->exists();

            if (!$usedElsewhere) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $product->gallery = array_values($gallery);
        $product->updated_by = auth()->id();
        $product->admin_edited_at = now();
        $product->save();

        return redirect()->back()->with('success', 'Imagem da galeria removida com sucesso!');
    }

    public function multiDeleteGalleryImage(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $imagesToDelete = explode(',', $request->image_names);

        $gallery = is_array($product->gallery) ? $product->gallery : json_decode($product->gallery, true);
        if (!is_array($gallery)) {
            return redirect()->back();
        }

        $newGallery = [];
        foreach ($gallery as $img) {
            if (in_array(basename($img), $imagesToDelete)) {
                if (Storage::disk('public')->exists($img)) {
                    $usedElsewhere = Product::where('gallery', 'like', "%{$img}%")
                        ->where('id', '!=', $product->id)
                        ->exists();

                    if (!$usedElsewhere) {
                        Storage::disk('public')->delete($img);
                    }
                }
            } else {
                $newGallery[] = $img;
            }
        }

        $product->gallery = array_values($newGallery);
        $product->updated_by = auth()->id();
        $product->admin_edited_at = now();
        $product->save();

        return redirect()
            ->back()
            ->with('success', count($imagesToDelete) . ' imagens removidas.');
    }

    public function deletePhoto($productId)
    {
        $product = Product::findOrFail($productId);
        if ($product->photo && Storage::disk('public')->exists($product->photo)) {
            $usedElsewhere = Product::where('photo', $product->photo)->where('id', '!=', $product->id)->exists();

            if (!$usedElsewhere) {
                Storage::disk('public')->delete($product->photo);
            }

            $product->photo = null;
            $product->updated_by = auth()->id();
            $product->admin_edited_at = now();
            $product->save();
            return redirect()->back()->with('success', 'Foto principal removida com sucesso!');
        }
        return redirect()->back()->with('error', 'Produto não possui foto principal.');
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if ($product) {
            $this->deleteProductImages($product);
            $product->delete();
        }

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Produto excluído com sucesso!',
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produto excluído com sucesso!');
    }

    // Revalida em massa: ativa quem passa nas regras mínimas e desativa quem não passa.
    // Existe porque o status normalmente já é recalculado ao editar um produto (update()),
    // mas atualizações que não passam por lá (ex.: ajuste de estoque direto) podem deixá-lo desatualizado.
    public function revalidateStatus()
    {
        $products = Product::select(['id', 'photo', 'gallery', 'description', 'price', 'stock', 'status', 'is_outlet'])->get();

        $toActivate = [];
        $toDeactivate = [];

        foreach ($products as $product) {
            $shouldBeActive = $product->meetsActiveRequirements();

            if ($shouldBeActive && !$product->status) {
                $toActivate[] = $product->id;
            } elseif (!$shouldBeActive && $product->status) {
                $toDeactivate[] = $product->id;
            }
        }

        if (!empty($toActivate)) {
            Product::whereIn('id', $toActivate)->update(['status' => 1]);
        }

        if (!empty($toDeactivate)) {
            Product::whereIn('id', $toDeactivate)->update(['status' => 0]);
        }

        return response()->json([
            'success' => true,
            'activated_ids' => $toActivate,
            'deactivated_ids' => $toDeactivate,
            'message' => count($toActivate) . ' produto(s) ativado(s) e ' . count($toDeactivate) . ' desativado(s).',
        ]);
    }

    public function getSubcategories($categoryId)
    {
        return response()->json(
            Subcategory::where('category_id', $categoryId)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'category_id'])
        );
    }

    public function getChildcategories($subcategoryId)
    {
        return response()->json(
            CategoriasFilhas::where('subcategory_id', $subcategoryId)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'subcategory_id'])
        );
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);

        if ($product->is_outlet) {
            return response()->json([
                'success' => false,
                'message' => 'Produtos em outlet não podem ser ativados no e-commerce. Restaure-o primeiro na gestão de outlet.',
            ], 422);
        }

        $product->status = !$product->status;
        $product->updated_by = auth()->id();
        $product->admin_edited_at = now();
        $product->save();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'status' => (int) $product->status,
                'message' => 'Status do produto atualizado!',
            ]);
        }

        return redirect()->back()->with('success', 'Status do produto atualizado!');
    }

    public function updateHighlights(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $highlights = $request->input('highlights', []);
        $product->highlights = json_encode($highlights);
        $product->updated_by = auth()->id();
        $product->admin_edited_at = now();
        $product->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Destaques atualizados com sucesso!',
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Destaques atualizados com sucesso!');
    }

    public function review(Request $request)
    {
        $mesSelecionado = $request->get('mes', Carbon::now()->format('Y-m'));
        $dataInicio = Carbon::parse($mesSelecionado)->startOfMonth();
        $dataFim = Carbon::parse($mesSelecionado)->endOfMonth();

        $mesesDisponiveis = [];
        for ($i = 0; $i < 6; $i++) {
            $data = Carbon::now()->subMonths($i);
            $mesesDisponiveis[] = [
                'value' => $data->format('Y-m'),
                'label' => $data->translatedFormat('F Y')
            ];
        }

        $edicoesPorDia = Product::selectRaw('DATE(admin_edited_at) as dia, COUNT(*) as total')
            ->whereBetween('admin_edited_at', [$dataInicio, $dataFim])
            ->whereNotNull('updated_by')
            ->groupBy('dia')
            ->orderBy('dia', 'desc')
            ->get();

        $detalhesProdutos = Product::with('editor:id,name')
            ->whereBetween('admin_edited_at', [$dataInicio, $dataFim])
            ->selectRaw('id, DATE(admin_edited_at) as dia, COALESCE(external_name, name) as name, sku, ref_code, updated_by, admin_edited_at')
            ->whereNotNull('updated_by')
            ->orderByDesc('admin_edited_at')
            ->get()
            ->groupBy('dia');

        return view('admin.products.review', compact('edicoesPorDia', 'detalhesProdutos', 'mesesDisponiveis', 'mesSelecionado'));
    }
}
