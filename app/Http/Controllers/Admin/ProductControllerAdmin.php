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
use Illuminate\Support\Facades\Storage;

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
        $perPage = $request->get('per_page', 20);

        $productColumns = ['id', 'sku', 'name', 'external_name', 'slug', 'price', 'stock', 'photo', 'gallery', 'brand_id', 'category_id', 'subcategory_id', 'childcategory_id', 'status', 'product_role', 'highlights', 'parent_id'];

        $products = Product::select($productColumns)
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('external_name', 'LIKE', "%{$search}%")
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
                }
            })
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
                            $q->orderBy('external_name', 'asc');
                            break;
                        case 'name_za':
                            $q->orderBy('external_name', 'desc');
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

        return view('admin.products.index', compact('products', 'brands', 'categories', 'search', 'brandId', 'categoryId', 'statusFilter', 'highlightFilter', 'stockFilter', 'sortBy', 'productType', 'perPage', 'highlights'));
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

        $products = Product::where(function ($query) use ($q) {
            $query
                ->where('name', 'like', "%{$q}%")
                ->orWhere('external_name', 'like', "%{$q}%")
                ->orWhere('sku', 'like', "%{$q}%");
        })
            ->when($context === 'color', fn($query) => $query->where('product_role', 'P'))
            ->when($excludeId, fn($query) => $query->where('id', '!=', $excludeId))
            ->orderBy('external_name')
            ->limit(50)
            ->get(['id', 'name', 'external_name', 'photo', 'color', 'size']);

        $products->transform(function ($product) {
            $product->photo = $product->photo_url;

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
            'color_parent_id' => 'nullable|array',
            'color_parent_id.*' => 'nullable|exists:products,id',
            'size' => 'nullable|string|max:50',
        ]);

        $data = $request->only(['sku', 'external_name', 'description', 'price', 'stock', 'brand_id', 'category_id', 'subcategory_id', 'childcategory_id', 'parent_id', 'color', 'size']);

        $data['highlights'] = $request->input('highlights', []);
        $data['product_role'] = 'P';

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->convertToWebp($request->file('photo'), 'photo');
        }

        if ($request->has('colors_values')) {
            $data['color'] = $request->input('colors_values')[0];
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

        $products = Product::select('id', 'name', 'sku')->where('id', '!=', $id)->orderBy('name', 'asc')->get();

        $item->selected_size_children = Product::where('parent_id', $item->id)->where('product_role', 'F')->pluck('id')->toArray();
        $familyRootId = !empty($item->color_parent_id) ? (int) $item->color_parent_id : (int) $item->id;
        $item->selected_color_family_members = Product::where('color_parent_id', $familyRootId)->where('product_role', 'P')->where('id', '!=', $item->id)->pluck('id')->toArray();

        $sizeChildrenProducts = Product::whereIn('id', $item->selected_size_children)
            ->get(['id', 'name', 'external_name', 'photo', 'color', 'size'])
            ->keyBy('id');

        $colorFamilyProducts = Product::whereIn('id', $item->selected_color_family_members)
            ->get(['id', 'name', 'external_name', 'photo', 'color', 'size'])
            ->keyBy('id');

        $translationsByLocale = $item->translations->keyBy('locale');

        if (is_string($item->parent_id) && str_contains($item->parent_id, ',')) {
            $item->parent_id =
                collect(explode(',', $item->parent_id))
                    ->map(fn($parentId) => (int) trim($parentId))
                    ->first(fn($parentId) => $parentId > 0) ?:
                null;
        } elseif (is_array($item->parent_id)) {
            $item->parent_id = collect($item->parent_id)->map(fn($parentId) => (int) $parentId)->first(fn($parentId) => $parentId > 0) ?: null;
        }

        return view('admin.products.edit', compact('item', 'brands', 'categories', 'subcategories', 'categoriasfilhas', 'products', 'sizeChildrenProducts', 'colorFamilyProducts', 'translationsByLocale'));
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

            'translate' => 'nullable|array',
            'translate.*.name' => 'nullable|string|max:255',
            'translate.*.details' => 'nullable|string|max:5000',
        ]);

        try {
            return DB::transaction(function () use ($request, $product) {
                $previousDescription = $product->description;
                $previousPhoto = $product->photo;
                $previousGallery = is_string($product->gallery) ? json_encode(array_values(json_decode($product->gallery, true) ?: [])) : json_encode(array_values($product->gallery ?: []));
                $isCurrentlySizeVariant = ($product->product_role ?? null) === 'F' || !empty($product->parent_id);
                $promoteToParent = $request->boolean('force_as_parent') && $isCurrentlySizeVariant;
                $originalFamilyRootId = !empty($product->color_parent_id) ? (int) $product->color_parent_id : (int) $product->id;
                $existingSizeChildIds = Product::where('parent_id', $product->id)->where('product_role', 'F')->pluck('id')->map(fn($childId) => (int) $childId)->toArray();
                $selectedSizeChildIds = array_values(array_filter(array_unique(array_filter((array) $request->input('parent_id', []))), fn($childId) => (int) $childId !== (int) $product->id));
                $selectedColorFamilyMemberIds = array_values(array_filter(array_unique(array_filter((array) $request->input('color_parent_id', []))), fn($memberId) => (int) $memberId !== (int) $product->id));

                if ($isCurrentlySizeVariant) {
                    $selectedSizeChildIds = [];
                    $selectedColorFamilyMemberIds = [];
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

                if ($request->has('translate.pt-br')) {
                    $data['name'] = $request->input('translate.pt-br.name');
                    $data['description'] = $request->input('translate.pt-br.details');
                }

                if ($request->has('colors_values')) {
                    $colors = (array) $request->input('colors_values');
                    $data['color'] = reset($colors);
                }

                if ($request->has('no_color')) {
                    $data['color'] = null;
                } elseif ($request->has('colors_values')) {
                    $colors = (array) $request->input('colors_values');
                    $data['color'] = reset($colors);
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
                        'color_parent_id' => null,
                        'product_role' => 'P',
                        'description' => null,
                        'photo' => null,
                        'gallery' => null,
                    ]);

                if (!empty($selectedSizeChildIds)) {
                    $children = Product::whereIn('id', $selectedSizeChildIds)
                        ->where('id', '!=', $product->id)
                        ->get();

                    foreach ($children as $child) {
                        $isNewChild = !in_array((int) $child->id, $existingSizeChildIds, true);
                        $childGallery = is_string($child->gallery) ? json_encode(array_values(json_decode($child->gallery, true) ?: [])) : json_encode(array_values($child->gallery ?: []));

                        $childData = [
                            'parent_id' => $product->id,
                            'color_parent_id' => $targetFamilyRootId,
                            'color' => $data['color'] ?? null,
                            'product_role' => 'F',
                            'brand_id' => $product->brand_id,
                            'category_id' => $product->category_id,
                            'subcategory_id' => $product->subcategory_id,
                            'childcategory_id' => $product->childcategory_id,
                            'status' => $product->status,
                            'stores' => $data['stores'],
                        ];

                        if ($isNewChild || empty($child->description) || $child->description === $previousDescription) {
                            $childData['description'] = $product->description;
                        }

                        if ($isNewChild || empty($child->photo) || $child->photo === $previousPhoto) {
                            $childData['photo'] = $product->photo;
                        }

                        if ($isNewChild || $childGallery === '[]' || $childGallery === $previousGallery) {
                            $childData['gallery'] = $data['gallery'];
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
        $products = Product::select(['id', 'photo', 'gallery', 'description', 'price', 'stock', 'status'])->get();

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
        $product->status = !$product->status;
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

        $edicoesPorDia = Product::selectRaw('DATE(updated_at) as dia, COUNT(*) as total')
            ->whereBetween('updated_at', [$dataInicio, $dataFim])
            ->whereNotNull('updated_by')
            ->groupBy('dia')
            ->orderBy('dia', 'desc')
            ->get();

        $detalhesProdutos = Product::whereBetween('updated_at', [$dataInicio, $dataFim])
            ->selectRaw('DATE(updated_at) as dia, name, sku, ref_code, updated_by')
            ->whereNotNull('updated_by')
            ->get()
            ->groupBy('dia');

        return view('admin.products.review', compact('edicoesPorDia', 'detalhesProdutos', 'mesesDisponiveis', 'mesSelecionado'));
    }
}
