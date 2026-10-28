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
use App\Services\Dhl\DhlProductMeasurementEstimator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $reviewProfiles = ['perfume', 'wine', 'spirits', 'aerosol', 'battery', 'tobacco', 'furniture', 'restricted'];

        $products = null;
        $rules = null;
        $productCounts = [];

        if ($view === 'products') {
            $query = Product::withoutGlobalScopes()
                ->select([
                    'id', 'sku', 'name', 'external_name', 'photo', 'brand_id', 'category_id',
                    'subcategory_id', 'childcategory_id', 'status', 'shipping_profile',
                    'shipping_weight_kg', 'shipping_length_cm', 'shipping_width_cm',
                    'shipping_height_cm', 'shipping_is_dangerous_goods', 'updated_at',
                ])
                ->with([
                    'brand:id,name',
                    'category:id,name,slug',
                    'subcategory:id,name,slug,category_id',
                    'categoriasFilhas:id,name,slug,category_id,subcategory_id',
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
            if ($measurement === 'exact') {
                $this->whereHasExactMeasurements($query);
            } elseif ($measurement === 'profile') {
                $this->whereMissingExactMeasurements($query)
                    ->whereNotNull('shipping_profile')->where('shipping_profile', '!=', '');
            } elseif ($measurement === 'automatic') {
                $this->whereMissingExactMeasurements($query)
                    ->where(fn (Builder $automatic) => $automatic
                        ->whereNull('shipping_profile')->orWhere('shipping_profile', ''));
            } elseif ($measurement === 'review') {
                $query->where(fn (Builder $review) => $review
                    ->where('shipping_is_dangerous_goods', true)
                    ->orWhereIn('shipping_profile', $reviewProfiles));
            } elseif ($measurement === 'incomplete') {
                $this->whereHasAnyMeasurement($query);
                $this->whereMissingExactMeasurements($query);
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
                'review_first' => $query->orderByRaw(
                    "(shipping_is_dangerous_goods = 1 OR shipping_profile IN ('perfume','wine','spirits','aerosol','battery','tobacco','furniture','restricted')) DESC"
                )->orderByRaw("COALESCE(NULLIF(name, ''), external_name) ASC"),
                default => $query->orderByRaw("COALESCE(NULLIF(name, ''), external_name) ASC")->orderBy('id'),
            };

            $perPage = in_array($request->integer('per_page'), [25, 50, 100], true)
                ? $request->integer('per_page')
                : 50;
            $products = $query->paginate($perPage)->withQueryString();
            $products->getCollection()->transform(function (Product $product) use ($estimator): Product {
                $product->setAttribute('dhl_shipping_measurement', $estimator->forProduct($product, true));
                $product->setAttribute('dhl_suggested_profile', $estimator->suggestedProfile($product));
                $product->setAttribute('dhl_image_url', $product->photo_url);

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
            SUM(CASE WHEN shipping_is_dangerous_goods = 1 OR shipping_profile IN ('perfume','wine','spirits','aerosol','battery','tobacco','furniture','restricted') THEN 1 ELSE 0 END) AS review_count
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
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'subcategories' => Subcategory::query()->orderBy('name')->get(['id', 'name', 'category_id']),
            'childCategories' => CategoriasFilhas::query()->orderBy('name')->get(['id', 'name', 'category_id', 'subcategory_id']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'productStats' => [
                'total' => (int) ($productStats->total ?? 0),
                'exact' => (int) ($productStats->exact_count ?? 0),
                'profile' => (int) ($productStats->profile_count ?? 0),
                'automatic' => (int) ($productStats->automatic_count ?? 0),
                'review' => (int) ($productStats->review_count ?? 0),
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
        DhlProductMeasurementEstimator $estimator,
        DhlMeasurementRuleResolver $resolver,
    ): RedirectResponse {
        $this->ensureMasterAdmin();

        $profileCodes = array_keys(DhlProductMeasurementEstimator::profiles());
        $data = $request->validate([
            'product_ids' => ['required', 'array', 'min:1', 'max:100'],
            'product_ids.*' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'bulk_action' => ['required', Rule::in([
                'apply_profile', 'apply_suggestion', 'set_exact', 'clear_exact',
                'clear_profile', 'mark_review', 'clear_review',
            ])],
            'profile' => ['nullable', Rule::in($profileCodes)],
            'weight_kg' => ['nullable', 'numeric', 'gt:0', 'max:1000'],
            'length_cm' => ['nullable', 'numeric', 'gt:0', 'max:1000'],
            'width_cm' => ['nullable', 'numeric', 'gt:0', 'max:1000'],
            'height_cm' => ['nullable', 'numeric', 'gt:0', 'max:1000'],
        ]);

        if ($data['bulk_action'] === 'apply_profile' && empty($data['profile'])) {
            throw ValidationException::withMessages(['profile' => 'Escolha o perfil que será aplicado.']);
        }
        if ($data['bulk_action'] === 'set_exact') {
            foreach (['weight_kg', 'length_cm', 'width_cm', 'height_cm'] as $field) {
                if (! isset($data[$field])) {
                    throw ValidationException::withMessages([$field => 'Preencha as quatro medidas reais para salvar em lote.']);
                }
            }
        }

        $products = Product::withoutGlobalScopes()
            ->with(['category:id,name,slug', 'subcategory:id,name,slug,category_id', 'categoriasFilhas:id,name,slug,category_id,subcategory_id'])
            ->whereIn('id', $data['product_ids'])
            ->get();

        DB::transaction(function () use ($products, $data, $estimator): void {
            foreach ($products as $product) {
                $changes = match ($data['bulk_action']) {
                    'apply_profile' => ['shipping_profile' => $data['profile']],
                    'apply_suggestion' => ['shipping_profile' => $estimator->suggestedProfile($product)],
                    'set_exact' => [
                        'shipping_weight_kg' => $data['weight_kg'],
                        'shipping_length_cm' => $data['length_cm'],
                        'shipping_width_cm' => $data['width_cm'],
                        'shipping_height_cm' => $data['height_cm'],
                    ],
                    'clear_exact' => [
                        'shipping_weight_kg' => null,
                        'shipping_length_cm' => null,
                        'shipping_width_cm' => null,
                        'shipping_height_cm' => null,
                    ],
                    'clear_profile' => ['shipping_profile' => null],
                    'mark_review' => ['shipping_profile' => 'restricted', 'shipping_is_dangerous_goods' => true],
                    'clear_review' => [
                        'shipping_is_dangerous_goods' => false,
                        'shipping_profile' => $product->shipping_profile === 'restricted' ? null : $product->shipping_profile,
                    ],
                };

                $product->forceFill([
                    ...$changes,
                    'updated_by' => auth()->id(),
                    'admin_edited_at' => now(),
                ])->save();
            }
        });

        $resolver->clearCache();
        $count = $products->count();

        return back()->with('success', "{$count} produto(s) atualizado(s) em lote para a DHL.");
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
            ['calcado', 'tenis', 'sapato', 'sandalia', 'zapatilla', 'sneaker'],
            ['perfume', 'perfumaria', 'perfumeria', 'colonia'],
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
            str_contains($joined, 'perfume') => ['perfume'],
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
