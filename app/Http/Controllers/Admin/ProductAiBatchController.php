<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductAiSkuImport;
use App\Jobs\GenerateProductAiPreparation;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAiBatch;
use App\Models\ProductAiBatchItem;
use App\Models\ProductAiPreparation;
use App\Models\Subcategory;
use App\Services\ProductAiBatchDraftBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProductAiBatchController extends Controller
{
    public function index()
    {
        $batches = ProductAiBatch::with('creator:id,name')
            ->latest()
            ->paginate(15);
        $brands = Brand::where('status', 1)->orderBy('name')->get(['id', 'name']);
        $categories = Category::where('status', 1)->orderBy('name')->get(['id', 'name']);
        $subcategories = Subcategory::whereIn('category_id', $categories->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'category_id', 'name']);

        return view('admin.products.ai-batches.index', compact(
            'batches',
            'brands',
            'categories',
            'subcategories',
        ));
    }

    public function catalogProducts(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'brand_id' => ['nullable', 'integer', 'min:1'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'subcategory_id' => ['nullable', 'integer', 'min:1'],
            'ai_preparation_filter' => ['nullable', 'in:pending,prepared,missing_photo,review'],
            'per_page' => ['nullable', 'integer', 'in:20,30,50,100'],
        ] + \App\Services\ProductAiCatalogFilters::rules($request->all()));

        $search = trim((string) ($validated['search'] ?? ''));
        $aiPreparationFilter = $validated['ai_preparation_filter'] ?? null;
        $perPage = (int) ($validated['per_page'] ?? 20);

        // These two states depend on whether an image really exists in storage,
        // so keep the same semantics used by the admin product listing.
        $productsWithUsableImages = null;
        if (in_array($aiPreparationFilter, ['prepared', 'missing_photo'], true)) {
            $productsWithUsableImages = Product::query()
                ->whereHas('aiPreparation', fn ($query) => $query->where('status', ProductAiPreparation::STATUS_COMPLETED))
                ->get(['id', 'photo', 'gallery'])
                ->filter(fn (Product $product) => Product::hasUsableImage($product->photo, $product->gallery))
                ->pluck('id')
                ->all();
        }

        $products = Product::query()
            ->select([
                'id',
                'sku',
                'name',
                'external_name',
                'ref_code',
                'photo',
                'gallery',
                'brand_id',
                'category_id',
                'subcategory_id',
                'status',
            ])
            ->with([
                'brand:id,name',
                'category:id,name',
                'subcategory:id,name',
                'aiPreparation:id,product_id,status',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('sku', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('external_name', 'like', "%{$search}%")
                        ->orWhere('ref_code', 'like', "%{$search}%");
                });
            })
            ->when(
                filled($validated['brand_id'] ?? null),
                fn ($query) => $query->where('brand_id', $validated['brand_id'])
            )
            ->when(
                filled($validated['category_id'] ?? null),
                fn ($query) => $query->where('category_id', $validated['category_id'])
            )
            ->when(
                filled($validated['subcategory_id'] ?? null),
                fn ($query) => $query->where('subcategory_id', $validated['subcategory_id'])
            )
            ->when($aiPreparationFilter === 'pending', function ($query) {
                $query->where(function ($pending) {
                    $pending
                        ->whereDoesntHave('aiPreparation')
                        ->orWhereHas('aiPreparation', fn ($preparation) =>
                            $preparation->where('status', ProductAiPreparation::STATUS_GENERATED)
                        );
                });
            })
            ->when($aiPreparationFilter === 'prepared', function ($query) use ($productsWithUsableImages) {
                $query
                    ->whereHas('aiPreparation', fn ($preparation) =>
                        $preparation->where('status', ProductAiPreparation::STATUS_COMPLETED)
                    )
                    ->whereIn('products.id', $productsWithUsableImages ?? []);
            })
            ->when($aiPreparationFilter === 'missing_photo', function ($query) use ($productsWithUsableImages) {
                $query->whereHas('aiPreparation', fn ($preparation) =>
                    $preparation->where('status', ProductAiPreparation::STATUS_COMPLETED)
                );

                if ($productsWithUsableImages !== []) {
                    $query->whereNotIn('products.id', $productsWithUsableImages);
                }
            })
            ->when($aiPreparationFilter === 'review', fn ($query) =>
                $query->whereHas('aiPreparation', fn ($preparation) =>
                    $preparation->whereIn('status', [
                        ProductAiPreparation::STATUS_NOT_FOUND,
                        ProductAiPreparation::STATUS_FAILED,
                    ])
                )
            )
            ->tap(fn ($query) => app(\App\Services\ProductAiCatalogFilters::class)->apply($query, $validated))
            ->paginate($perPage);

        return response()->json([
            'data' => $products->getCollection()->map(fn (Product $product) => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name ?: $product->external_name,
                'external_name' => $product->external_name,
                'ref_code' => $product->ref_code,
                'image_url' => $product->photo_url,
                'brand' => $product->brand?->name,
                'category' => $product->category?->name,
                'subcategory' => $product->subcategory?->name,
                'ai_status' => $product->productAiDisplayStatus(),
            ])->values(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function preview(Request $request, ProductAiBatchDraftBuilder $draftBuilder)
    {
        $validated = $request->validate([
            'file' => ['nullable', 'required_without:skus', 'file', 'mimes:xlsx', 'max:5120'],
            'skus' => ['nullable', 'required_without:file', 'string', 'max:100000'],
        ], [
            'file.mimes' => 'El archivo debe estar en formato Excel .xlsx.',
            'file.required_without' => 'Subí un Excel o pegá los códigos.',
            'skus.required_without' => 'Subí un Excel o pegá los códigos.',
        ]);

        $rawSkus = collect();
        $sourceType = $request->hasFile('file') ? 'xlsx' : 'paste';
        $originalFilename = null;

        if ($request->hasFile('file')) {
            try {
                $rows = collect(Excel::toArray(new ProductAiSkuImport, $request->file('file'))[0] ?? []);
            } catch (Throwable $exception) {
                report($exception);
                throw ValidationException::withMessages([
                    'file' => 'No pudimos leer el archivo. Verificá que sea un Excel .xlsx válido.',
                ]);
            }
            $header = collect(['codigo', 'sku', 'referencia'])->first(fn ($candidate) => $rows->contains(fn ($row) => array_key_exists($candidate, $row)));
            if (! $header) {
                throw ValidationException::withMessages([
                    'file' => 'No encontramos una columna llamada código, codigo, sku o referencia.',
                ]);
            }
            $rawSkus = $rawSkus->concat(
                $rows->pluck($header)->map(fn ($value) => $this->normalizeCellValue($value))->filter()
            );
            $originalFilename = $request->file('file')->getClientOriginalName();
        }

        if (filled($validated['skus'] ?? null)) {
            $rawSkus = $rawSkus->concat(
                collect(preg_split('/[\s,;]+/u', (string) $validated['skus'], -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
            );
        }

        $rawSkus = $rawSkus->values();
        $inputField = filled($validated['skus'] ?? null) ? 'skus' : 'file';

        $batch = DB::transaction(function () use ($rawSkus, $sourceType, $originalFilename, $inputField, $draftBuilder) {
            $batch = ProductAiBatch::create([
                'created_by' => auth()->id(),
                'source_type' => $sourceType,
                'original_filename' => $originalFilename,
                'status' => ProductAiBatch::STATUS_DRAFT,
            ]);
            $draftBuilder->rebuild($batch, $rawSkus, $inputField);

            return $batch;
        });

        return redirect()->route('admin.products.ai-batches.show', $batch);
    }

    public function show(ProductAiBatch $batch)
    {
        $batch->load(['items.product:id,sku,name,external_name,photo,gallery', 'creator:id,name']);
        $counts = $batch->items->countBy('status');
        $groupedCount = $batch->items->sum(fn ($item) => max(0, count($item->source_skus ?: []) - 1));
        $batchSkus = $batch->items
            ->flatMap(fn ($item) => $item->source_skus ?: [$item->submitted_sku])
            ->filter()
            ->unique(fn ($sku) => mb_strtolower((string) $sku))
            ->values();

        $brands = collect();
        $categories = collect();
        $subcategories = collect();
        if ($batch->status === ProductAiBatch::STATUS_DRAFT) {
            $brands = Brand::where('status', 1)->orderBy('name')->get(['id', 'name']);
            $categories = Category::where('status', 1)->orderBy('name')->get(['id', 'name']);
            $subcategories = Subcategory::whereIn('category_id', $categories->pluck('id'))
                ->orderBy('name')
                ->get(['id', 'category_id', 'name']);
        }

        return view('admin.products.ai-batches.show', compact(
            'batch',
            'counts',
            'groupedCount',
            'batchSkus',
            'brands',
            'categories',
            'subcategories',
        ));
    }

    public function addProducts(Request $request, ProductAiBatch $batch, ProductAiBatchDraftBuilder $draftBuilder)
    {
        $validated = $request->validate([
            'skus' => ['required', 'string', 'max:100000'],
        ]);
        $submittedSkus = collect(preg_split('/[\s,;]+/u', $validated['skus'], -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($sku) => trim((string) $sku))
            ->filter()
            ->values();

        $result = DB::transaction(function () use ($batch, $submittedSkus, $draftBuilder) {
            $lockedBatch = ProductAiBatch::lockForUpdate()->findOrFail($batch->id);
            if ($lockedBatch->status !== ProductAiBatch::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'batch' => 'Solo se pueden agregar productos mientras el lote esté en borrador.',
                ]);
            }

            $existingSkus = $lockedBatch->items()
                ->get(['source_skus', 'submitted_sku'])
                ->flatMap(fn ($item) => $item->source_skus ?: [$item->submitted_sku])
                ->filter()
                ->values();
            $existingUnique = $existingSkus->unique(fn ($sku) => mb_strtolower((string) $sku))->values();
            $combined = $existingUnique
                ->concat($submittedSkus)
                ->unique(fn ($sku) => mb_strtolower((string) $sku))
                ->values();
            $addedCount = $combined->count() - $existingUnique->count();
            $newDuplicateCount = $existingUnique->count() + $submittedSkus->count() - $combined->count();

            if ($addedCount === 0) {
                return ['added' => 0, 'duplicates' => $submittedSkus->count()];
            }

            $draftBuilder->rebuild(
                $lockedBatch,
                $combined,
                'skus',
                (int) $lockedBatch->duplicate_count + max(0, $newDuplicateCount),
            );

            return ['added' => $addedCount, 'duplicates' => max(0, $newDuplicateCount)];
        });

        $message = $result['added'] === 1
            ? 'Se agregó 1 producto al lote.'
            : "Se agregaron {$result['added']} productos al lote.";
        if ($result['duplicates'] > 0) {
            $message .= " Se omitieron {$result['duplicates']} códigos repetidos.";
        }

        return redirect()->route('admin.products.ai-batches.show', $batch)->with('success', $message);
    }

    public function dispatch(ProductAiBatch $batch)
    {
        app(\App\Services\ProductAiSettingsService::class)->ensureAvailable();
        $itemsToDispatch = DB::transaction(function () use ($batch) {
            $lockedBatch = ProductAiBatch::lockForUpdate()->findOrFail($batch->id);
            if ($lockedBatch->status !== ProductAiBatch::STATUS_DRAFT) {
                throw ValidationException::withMessages(['batch' => 'Este lote ya fue confirmado.']);
            }
            $items = $lockedBatch->items()
                ->where('status', ProductAiBatchItem::STATUS_READY)
                ->get(['id', 'product_id']);
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['batch' => 'No hay productos válidos para procesar.']);
            }
            $lockedBatch->items()->whereIn('id', $items->pluck('id'))->update(['status' => ProductAiBatchItem::STATUS_QUEUED]);
            $lockedBatch->update([
                'status' => ProductAiBatch::STATUS_QUEUED,
                'started_at' => now(),
            ]);

            return $items;
        });

        foreach ($itemsToDispatch as $item) {
            GenerateProductAiPreparation::dispatch((int) $item->id, (int) $item->product_id);
        }

        return redirect()->route('admin.products.ai-batches.show', $batch)
            ->with('success', "Se enviaron {$itemsToDispatch->count()} productos a la cola de IA.");
    }

    public function status(ProductAiBatch $batch)
    {
        $batch->load(['items.product:id,sku,name,external_name,photo,gallery']);
        $counts = $batch->items->countBy('status');
        $terminal = collect([
            ProductAiBatchItem::STATUS_COMPLETED,
            ProductAiBatchItem::STATUS_NOT_FOUND,
            ProductAiBatchItem::STATUS_FAILED,
        ])->sum(fn ($status) => (int) ($counts[$status] ?? 0));
        $total = (int) $batch->eligible_count;

        return response()->json([
            'status' => $batch->status,
            'counts' => $counts,
            'progress' => $total ? (int) round(($terminal / $total) * 100) : 100,
            'items' => $batch->items->map(fn ($item) => [
                'id' => $item->id,
                'status' => $item->status,
                'message' => $item->message,
                'has_image' => $item->product
                    ? Product::hasUsableImage($item->product->photo, $item->product->gallery)
                    : false,
            ])->values(),
        ]);
    }

    private function normalizeCellValue($value): string
    {
        if (is_float($value) && floor($value) === $value) {
            return (string) (int) $value;
        }

        return trim((string) $value);
    }
}
