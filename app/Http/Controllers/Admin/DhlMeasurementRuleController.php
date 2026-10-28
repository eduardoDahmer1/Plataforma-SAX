<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CategoriasFilhas;
use App\Models\Category;
use App\Models\DhlMeasurementRule;
use App\Models\Product;
use App\Models\Subcategory;
use App\Services\Dhl\DhlMeasurementRuleDefaults;
use App\Services\Dhl\DhlMeasurementRuleResolver;
use App\Services\Dhl\DhlCustomsProductData;
use App\Services\Dhl\DhlProductMeasurementEstimator;
use App\Services\Dhl\DhlSettingsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DhlMeasurementRuleController extends Controller
{
    public function index(Request $request, DhlProductMeasurementEstimator $estimator): View
    {
        $this->ensureMasterAdmin();

        $view = in_array($request->query('view'), ['products', 'rules'], true)
            ? (string) $request->query('view')
            : 'products';
        $profiles = DhlProductMeasurementEstimator::profiles();
        $customsData = app(DhlCustomsProductData::class);
        $storeOrigin = strtoupper(trim((string) data_get(
            app(DhlSettingsService::class)->resolved(),
            'origin.country_code',
            'PY',
        )));
        $products = null;
        $rules = null;
        $productCounts = [];

        if ($view === 'products') {
            $query = Product::withoutGlobalScopes()
                ->select([
                    'id', 'sku', 'name', 'external_name', 'size', 'photo', 'brand_id', 'category_id',
                    'subcategory_id', 'childcategory_id', 'status', 'shipping_profile',
                    'shipping_weight_kg', 'shipping_length_cm', 'shipping_width_cm',
                    'shipping_height_cm', 'shipping_country_of_origin',
                    'shipping_customs_description', 'shipping_is_dangerous_goods', 'updated_at',
                    'shipping_reviewed_at', 'shipping_reviewed_by',
                ])
                ->with([
                    'brand:id,name',
                    'category:id,name,slug',
                    'subcategory:id,name,slug,category_id',
                    'categoriasFilhas:id,name,slug,category_id,subcategory_id',
                    'translations' => fn ($translations) => $translations
                        ->where('locale', 'en')
                        ->select(['id', 'product_id', 'locale', 'details']),
                ]);

            if ($search = trim((string) $request->query('q'))) {
                $this->applyProductSearch($query, $search);
            }

            foreach (['category_id', 'subcategory_id', 'childcategory_id', 'brand_id'] as $filter) {
                if ($request->integer($filter) > 0) {
                    $query->where($filter, $request->integer($filter));
                }
            }

            if ($profile = trim((string) $request->query('profile'))) {
                if ($profile === 'automatic') {
                    $query->where(fn (Builder $automatic) => $automatic
                        ->whereNull('shipping_profile')->orWhere('shipping_profile', ''));
                } elseif (array_key_exists($profile, $profiles) || $profile === 'restricted') {
                    $query->where('shipping_profile', $profile);
                }
            }

            $measurement = (string) $request->query('measurement');
            if ($measurement === 'pending_review') {
                $query->whereNull('shipping_reviewed_at');
            } elseif ($measurement === 'reviewed') {
                $query->whereNotNull('shipping_reviewed_at');
            }

            if ($request->query('catalog_status') === 'active') {
                $query->where('status', true);
            } elseif ($request->query('catalog_status') === 'inactive') {
                $query->where('status', false);
            }

            match ((string) $request->query('sort')) {
                'updated' => $query->orderByDesc('updated_at')->orderByDesc('id'),
                'exact_first' => $query->orderByRaw(
                    '(shipping_weight_kg > 0 AND shipping_length_cm > 0 AND shipping_width_cm > 0 AND shipping_height_cm > 0) DESC'
                )->orderByRaw("COALESCE(NULLIF(name, ''), external_name) ASC"),
                'review_first' => $query->orderByRaw('shipping_reviewed_at IS NULL DESC')
                    ->orderByRaw("COALESCE(NULLIF(name, ''), external_name) ASC"),
                default => $query->orderByRaw("COALESCE(NULLIF(name, ''), external_name) ASC")->orderBy('id'),
            };

            $perPage = in_array($request->integer('per_page'), [25, 50, 100], true)
                ? $request->integer('per_page')
                : 50;
            $products = $query->paginate($perPage)->withQueryString();
            $products->getCollection()->transform(function (Product $product) use ($estimator, $customsData, $storeOrigin): Product {
                $product->setAttribute('dhl_shipping_measurement', $estimator->forProduct($product, true));
                $product->setAttribute('dhl_suggested_profile', $estimator->suggestedProfile($product));
                $product->setAttribute('dhl_image_url', $product->photo_url);
                $product->setAttribute('dhl_country_of_origin', $customsData->countryOfOrigin($product, $storeOrigin));
                $product->setAttribute('dhl_customs_description', $customsData->englishDescription($product));

                return $product;
            });
        } else {
            $query = DhlMeasurementRule::query()
                ->orderByRaw("FIELD(level, 'category', 'subcategory', 'childcategory')")
                ->orderBy('scope_name');
            if ($search = trim((string) $request->query('q'))) {
                $query->where('scope_name', 'like', '%'.$search.'%');
            }
            if (in_array($request->query('level'), ['category', 'subcategory', 'childcategory'], true)) {
                $query->where('level', $request->query('level'));
            }
            if ($request->query('status') === 'active') {
                $query->where('active', true);
            } elseif ($request->query('status') === 'inactive') {
                $query->where('active', false);
            } elseif ($request->query('status') === 'manual') {
                $query->where('requires_manual_review', true);
            }
            if ($ruleProfile = trim((string) $request->query('rule_profile'))) {
                $query->where('profile_code', $ruleProfile);
            }

            $rules = $query->paginate(40)->withQueryString();
            $productCounts = $this->productCounts($rules->items());
        }

        $productStats = DB::table('products')->selectRaw(<<<'SQL'
            COUNT(*) AS total,
            SUM(CASE WHEN shipping_weight_kg > 0 AND shipping_length_cm > 0 AND shipping_width_cm > 0 AND shipping_height_cm > 0 THEN 1 ELSE 0 END) AS exact_count,
            SUM(CASE WHEN shipping_profile IS NOT NULL AND shipping_profile != '' THEN 1 ELSE 0 END) AS profile_count,
            SUM(CASE WHEN (shipping_profile IS NULL OR shipping_profile = '') AND NOT (COALESCE(shipping_weight_kg, 0) > 0 AND COALESCE(shipping_length_cm, 0) > 0 AND COALESCE(shipping_width_cm, 0) > 0 AND COALESCE(shipping_height_cm, 0) > 0) THEN 1 ELSE 0 END) AS automatic_count,
            SUM(CASE WHEN shipping_reviewed_at IS NULL THEN 1 ELSE 0 END) AS pending_review_count,
            SUM(CASE WHEN shipping_reviewed_at IS NOT NULL THEN 1 ELSE 0 END) AS reviewed_count
        SQL)->first();
        $ruleStats = DhlMeasurementRule::query()->selectRaw(
            'COUNT(*) AS total, SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) AS active_count, SUM(CASE WHEN requires_manual_review = 1 THEN 1 ELSE 0 END) AS manual_count'
        )->first();

        return view('admin.dhl.measurements', [
            'view' => $view,
            'products' => $products,
            'rules' => $rules,
            'productCounts' => $productCounts,
            'profiles' => $profiles,
            'measurementOptions' => DhlProductMeasurementEstimator::measurementOptions(),
            'storeOrigin' => $storeOrigin,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'subcategories' => Subcategory::query()->orderBy('name')->get(['id', 'name', 'category_id']),
            'childCategories' => CategoriasFilhas::query()->orderBy('name')->get(['id', 'name', 'category_id', 'subcategory_id']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'productStats' => [
                'total' => (int) ($productStats->total ?? 0),
                'exact' => (int) ($productStats->exact_count ?? 0),
                'profile' => (int) ($productStats->profile_count ?? 0),
                'automatic' => (int) ($productStats->automatic_count ?? 0),
                'pending_review' => (int) ($productStats->pending_review_count ?? 0),
                'reviewed' => (int) ($productStats->reviewed_count ?? 0),
            ],
            'ruleStats' => [
                'total' => (int) ($ruleStats->total ?? 0),
                'active' => (int) ($ruleStats->active_count ?? 0),
                'manual' => (int) ($ruleStats->manual_count ?? 0),
                'exact' => (int) ($productStats->exact_count ?? 0),
            ],
        ]);
    }

    public function bulkUpdateProducts(
        Request $request,
        DhlMeasurementRuleResolver $resolver,
    ): RedirectResponse {
        $this->ensureMasterAdmin();

        $profileCodes = [...array_keys(DhlProductMeasurementEstimator::profiles()), 'restricted'];
        $selection = $request->validate([
            'product_ids' => ['required', 'array', 'min:1', 'max:100'],
            'product_ids.*' => ['required', 'integer', 'distinct', 'exists:products,id'],
        ], [
            'product_ids.required' => 'Selecione pelo menos um produto.',
            'product_ids.max' => 'Selecione no máximo 100 produtos por vez.',
            'product_ids.*.exists' => 'Um dos produtos selecionados não existe mais. Atualize a página.',
        ]);

        $selectedIds = collect($selection['product_ids'])->map(static fn ($id): int => (int) $id)->unique()->values();
        $allProducts = (array) $request->input('products', []);
        $selectedProducts = [];
        foreach ($selectedIds as $productId) {
            if (! array_key_exists($productId, $allProducts)) {
                throw ValidationException::withMessages([
                    'products' => "As medidas do produto {$productId} não foram enviadas. Atualize a página e tente novamente.",
                ]);
            }
            $selectedProducts[$productId] = $allProducts[$productId];
        }

        $data = Validator::make(['products' => $selectedProducts], [
            'products' => ['required', 'array', 'min:1', 'max:100'],
            'products.*.shipping_profile' => ['nullable', Rule::in($profileCodes)],
            'products.*.shipping_weight_kg' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'products.*.shipping_length_cm' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'products.*.shipping_width_cm' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'products.*.shipping_height_cm' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'products.*.shipping_country_of_origin' => ['required', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
            'products.*.shipping_customs_description' => ['nullable', 'string', 'max:255'],
            'products.*.shipping_is_dangerous_goods' => ['required', 'boolean'],
        ], [
            'products.*.shipping_country_of_origin.required' => 'Confirme o país de fabricação dos produtos selecionados.',
            'products.*.shipping_country_of_origin.size' => 'O país de fabricação deve usar exatamente duas letras.',
            'products.*.shipping_country_of_origin.regex' => 'O país de fabricação deve usar um código ISO-2 válido.',
            'products.*.shipping_weight_kg.required' => 'Informe o peso dos produtos selecionados.',
            'products.*.shipping_length_cm.required' => 'Informe o comprimento dos produtos selecionados.',
            'products.*.shipping_width_cm.required' => 'Informe a largura dos produtos selecionados.',
            'products.*.shipping_height_cm.required' => 'Informe a altura dos produtos selecionados.',
        ])->stopOnFirstFailure()->validate();

        $customsData = app(DhlCustomsProductData::class);
        $catalogProducts = Product::withoutGlobalScopes()
            ->with(['translations' => fn ($translations) => $translations
                ->where('locale', 'en')
                ->select(['id', 'product_id', 'locale', 'details'])])
            ->whereIn('id', $selectedIds)
            ->get(['id', 'sku', 'name', 'external_name'])
            ->keyBy('id');
        $customsDescriptions = $catalogProducts->mapWithKeys(
            fn (Product $product): array => [$product->id => $customsData->englishDescription($product)],
        );
        $missingDescription = $catalogProducts->first(
            fn (Product $product): bool => blank($customsDescriptions->get($product->id)),
        );
        if ($missingDescription) {
            $label = $missingDescription->sku ?: ($missingDescription->name ?: $missingDescription->external_name);
            throw ValidationException::withMessages([
                'products' => "O produto {$label} não possui descrição EN. Edite o produto, salve a tradução inglesa e tente novamente.",
            ]);
        }

        DB::transaction(function () use ($selectedIds, $data, $customsDescriptions): void {
            foreach ($selectedIds as $productId) {
                $measurements = $data['products'][$productId];
                DB::table('products')->where('id', $productId)->update([
                    'shipping_profile' => filled($measurements['shipping_profile'] ?? null)
                        ? (string) $measurements['shipping_profile']
                        : null,
                    'shipping_weight_kg' => $measurements['shipping_weight_kg'],
                    'shipping_length_cm' => $measurements['shipping_length_cm'],
                    'shipping_width_cm' => $measurements['shipping_width_cm'],
                    'shipping_height_cm' => $measurements['shipping_height_cm'],
                    'shipping_country_of_origin' => filled($measurements['shipping_country_of_origin'] ?? null)
                        ? strtoupper(trim((string) $measurements['shipping_country_of_origin']))
                        : null,
                    'shipping_customs_description' => $customsDescriptions->get($productId),
                    'shipping_is_dangerous_goods' => (bool) $measurements['shipping_is_dangerous_goods'],
                    'shipping_reviewed_at' => now(),
                    'shipping_reviewed_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                    'admin_edited_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $resolver->clearCache();
        $count = $selectedIds->count();

        return back()->with('success', "Medidas DHL de {$count} produto(s) revisadas e salvas. Nenhum dado comercial do produto foi alterado.");
    }

    public function update(Request $request, DhlMeasurementRule $measurementRule, DhlMeasurementRuleResolver $resolver): RedirectResponse
    {
        $this->ensureMasterAdmin();
        $data = $request->validate([
            'active' => ['nullable', 'boolean'],
            'requires_manual_review' => ['nullable', 'boolean'],
            'weight_kg' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'length_cm' => ['required', 'numeric', 'gt:0', 'max:300'],
            'width_cm' => ['required', 'numeric', 'gt:0', 'max:300'],
            'height_cm' => ['required', 'numeric', 'gt:0', 'max:300'],
        ]);
        $data['active'] = $request->boolean('active');
        $data['requires_manual_review'] = $request->boolean('requires_manual_review');

        $measurementRule->update($data);
        $resolver->clearCache();

        return back()->with('success', 'Medidas médias atualizadas para '.$measurementRule->scope_name.'.');
    }

    public function sync(DhlMeasurementRuleDefaults $defaults, DhlMeasurementRuleResolver $resolver): RedirectResponse
    {
        $this->ensureMasterAdmin();
        $created = $defaults->sync();
        $resolver->clearCache();

        return back()->with('success', $created > 0
            ? "{$created} nova(s) regra(s) criada(s) para a hierarquia atual."
            : 'Todas as categorias que possuem produtos já estão cadastradas.');
    }

    private function applyProductSearch(Builder $query, string $search): void
    {
        $aliases = $this->searchAliases($search);
        if ($aliases !== []) {
            $categoryIds = $this->taxonomyIdsMatching('categories', $aliases);
            $subcategoryIds = $this->taxonomyIdsMatching('subcategories', $aliases);
            $childCategoryIds = $this->taxonomyIdsMatching('childcategories', $aliases);
            $profileCodes = $this->profileCodesForAliases($aliases);

            $query->where(function (Builder $matches) use ($aliases, $categoryIds, $subcategoryIds, $childCategoryIds, $profileCodes): void {
                foreach ($aliases as $alias) {
                    $like = '%'.$alias.'%';
                    $matches->orWhere('name', 'like', $like)
                        ->orWhere('external_name', 'like', $like);
                }
                if ($categoryIds !== []) {
                    $matches->orWhereIn('category_id', $categoryIds);
                }
                if ($subcategoryIds !== []) {
                    $matches->orWhereIn('subcategory_id', $subcategoryIds);
                }
                if ($childCategoryIds !== []) {
                    $matches->orWhereIn('childcategory_id', $childCategoryIds);
                }
                if ($profileCodes !== []) {
                    $matches->orWhereIn('shipping_profile', $profileCodes);
                }
            });

            return;
        }

        $terms = array_slice(preg_split('/\s+/', trim($search)) ?: [], 0, 6);
        foreach ($terms as $term) {
            $like = '%'.$term.'%';
            $query->where(fn (Builder $matches) => $matches
                ->where('name', 'like', $like)
                ->orWhere('external_name', 'like', $like)
                ->orWhere('sku', 'like', $like)
                ->orWhereHas('brand', fn (Builder $brand) => $brand->where('name', 'like', $like))
                ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', $like))
                ->orWhereHas('subcategory', fn (Builder $subcategory) => $subcategory->where('name', 'like', $like))
                ->orWhereHas('categoriasFilhas', fn (Builder $child) => $child->where('name', 'like', $like)));
        }
    }

    private function taxonomyIdsMatching(string $table, array $aliases): array
    {
        return DB::table($table)
            ->where(function ($query) use ($aliases): void {
                foreach ($aliases as $alias) {
                    $query->orWhere('name', 'like', '%'.$alias.'%');
                }
            })
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    private function searchAliases(string $search): array
    {
        $normalized = mb_strtolower(Str::ascii(trim($search)));
        $groups = [
            ['movel', 'moveis', 'mueble', 'muebles', 'cadeira', 'silla', 'chair', 'sofa', 'poltrona', 'mesa', 'armario', 'estante', 'rack', 'comoda', 'escrivaninha'],
            ['vinho', 'vino', 'wine', 'champagne', 'espumante'],
            ['camiseta', 'tshirt', 't-shirt', 'remera'],
            ['calcado', 'tenis', 'sapato', 'sandalia', 'zapatilla', 'sneaker', 'bota', 'boots'],
            ['perfume', 'perfumaria', 'perfumeria', 'colonia'],
            ['calca', 'pantalon', 'jeans', 'legging'],
            ['casaco', 'jaqueta', 'chaqueta', 'blazer', 'sueter', 'sweater', 'moletom'],
            ['camisa', 'shirt', 'polo'],
            ['cueca', 'calcinha', 'meia', 'calcetin', 'roupa intima'],
            ['vestido', 'noiva', 'novia', 'bridal'],
            ['casa', 'prato', 'plato', 'copa', 'vaso', 'taza', 'decoracao', 'cozinha'],
            ['tabaco', 'tobacco', 'habano', 'charuto', 'xaruto', 'cigarro'],
            ['oculo', 'oculos', 'anteojo', 'gafa', 'eyewear'],
        ];

        foreach ($groups as $aliases) {
            foreach ($aliases as $alias) {
                if (str_contains($normalized, $alias)) {
                    return $aliases;
                }
            }
        }

        return [];
    }

    private function profileCodesForAliases(array $aliases): array
    {
        $joined = implode(' ', $aliases);

        return match (true) {
            str_contains($joined, 'cadeira') => ['furniture'],
            str_contains($joined, 'vinho') => ['wine'],
            str_contains($joined, 'camiseta') => ['tshirt', 'kids_tshirt', 'apparel_light'],
            str_contains($joined, 'calcado') => ['footwear', 'kids_footwear'],
            str_contains($joined, 'perfume') => array_values(array_filter(
                array_keys(DhlProductMeasurementEstimator::profiles()),
                static fn (string $code): bool => str_starts_with($code, 'perfume'),
            )),
            str_contains($joined, 'calca') => ['pants', 'apparel_heavy'],
            str_contains($joined, 'casaco') => ['jacket', 'sweater', 'apparel_heavy'],
            str_contains($joined, 'camisa') => ['shirt', 'polo', 'apparel_light'],
            str_contains($joined, 'cueca') => ['small_apparel'],
            str_contains($joined, 'vestido') => ['dress', 'apparel_light'],
            str_contains($joined, 'prato') => ['home'],
            str_contains($joined, 'tabaco') => ['tobacco'],
            str_contains($joined, 'oculo') => ['eyewear'],
            default => [],
        };
    }

    private function whereHasExactMeasurements(Builder $query): Builder
    {
        return $query->where('shipping_weight_kg', '>', 0)
            ->where('shipping_length_cm', '>', 0)
            ->where('shipping_width_cm', '>', 0)
            ->where('shipping_height_cm', '>', 0);
    }

    private function whereMissingExactMeasurements(Builder $query): Builder
    {
        return $query->where(fn (Builder $missing) => $missing
            ->whereNull('shipping_weight_kg')->orWhere('shipping_weight_kg', '<=', 0)
            ->orWhereNull('shipping_length_cm')->orWhere('shipping_length_cm', '<=', 0)
            ->orWhereNull('shipping_width_cm')->orWhere('shipping_width_cm', '<=', 0)
            ->orWhereNull('shipping_height_cm')->orWhere('shipping_height_cm', '<=', 0));
    }

    private function whereHasAnyMeasurement(Builder $query): Builder
    {
        return $query->where(fn (Builder $measurement) => $measurement
            ->where('shipping_weight_kg', '>', 0)
            ->orWhere('shipping_length_cm', '>', 0)
            ->orWhere('shipping_width_cm', '>', 0)
            ->orWhere('shipping_height_cm', '>', 0));
    }

    private function productCounts(array $rules): array
    {
        $collection = collect($rules);
        $counts = [];
        foreach ([
            'category' => 'category_id',
            'subcategory' => 'subcategory_id',
            'childcategory' => 'childcategory_id',
        ] as $level => $column) {
            $ids = $collection->where('level', $level)->pluck($column)->filter()->unique()->values();
            if ($ids->isEmpty()) {
                continue;
            }
            DB::table('products')->whereIn($column, $ids)->select($column, DB::raw('COUNT(*) as aggregate'))
                ->groupBy($column)->get()->each(function ($row) use (&$counts, $level, $column): void {
                    $counts[$level.':'.$row->{$column}] = (int) $row->aggregate;
                });
        }

        return $counts;
    }

    private function ensureMasterAdmin(): void
    {
        abort_unless(auth()->user()?->isMasterAdmin(), 403);
    }
}
