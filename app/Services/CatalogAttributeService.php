<?php

namespace App\Services;

use App\Models\CatalogAttribute;
use App\Models\CatalogAttributeOption;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CatalogAttributeService
{
    public function available(): bool
    {
        return (bool) config('catalog-attributes.enabled') && Schema::hasTable('catalog_attributes');
    }

    public function enabledFor(Product $product): bool
    {
        return $this->available();
    }

    public function saveDefinition(array $input, ?CatalogAttribute $attribute = null): CatalogAttribute
    {
        $input['code'] = $attribute?->code ?: Str::slug((string) data_get($input, 'translate.es.name'), '_');
        $data = Validator::make($input, [
            'code' => ['required', 'max:80', 'regex:/^[a-z0-9_]+$/', Rule::unique('catalog_attributes')->ignore($attribute?->id)],
            'data_type' => 'required|in:single,multiple,boolean,number',
            'unit' => 'nullable|string|max:20',
            'is_active' => 'required|boolean', 'inherit_from_parent' => 'required|boolean',
            'translate.es.name' => 'required|string|max:255', 'translate.pt-br.name' => 'required|string|max:255', 'translate.en.name' => 'required|string|max:255',
        ])->validate();
        if ($attribute && ($attribute->data_type !== $data['data_type'] || $attribute->unit !== ($data['unit'] ?? null))
            && ($attribute->options()->exists() || ProductAttributeValue::where('attribute_id', $attribute->id)->exists())) {
            throw ValidationException::withMessages(['data_type' => 'No se puede cambiar el tipo ni la unidad de un atributo utilizado.']);
        }

        return DB::transaction(function () use ($attribute, $data) {
            $attribute ??= new CatalogAttribute;
            $language = $this->saveLanguage('catalog_attr_'.$data['code'], $data['translate']);
            $attribute->fill(collect($data)->only(['code', 'data_type', 'is_active', 'inherit_from_parent'])->all());
            $attribute->unit = $data['data_type'] === 'number' ? ($data['unit'] ?? null) : null;
            $attribute->language_id = $language->id;
            $attribute->revision = (string) Str::uuid();
            $attribute->save();

            return $attribute->fresh(['language', 'options.language']);
        });
    }

    public function saveOption(CatalogAttribute $attribute, array $input, ?CatalogAttributeOption $option = null): CatalogAttributeOption
    {
        abort_unless(in_array($attribute->data_type, ['single', 'multiple']), 422, 'Este atributo no admite opciones.');
        if ($option) {
            abort_unless($option->attribute_id === $attribute->id, 404);
        }
        $input['code'] = $option?->code ?: Str::slug((string) data_get($input, 'translate.es.name'), '_');
        $data = Validator::make($input, [
            'code' => ['required', 'max:80', 'regex:/^[a-z0-9_]+$/', Rule::unique('catalog_attribute_options')->where('attribute_id', $attribute->id)->ignore($option?->id)],
            'is_active' => 'required|boolean',
            'translate.es.name' => 'required|string|max:255', 'translate.pt-br.name' => 'required|string|max:255', 'translate.en.name' => 'required|string|max:255',
        ])->validate();

        return DB::transaction(function () use ($attribute, $option, $data) {
            $language = $this->saveLanguage('catalog_opt_'.substr(hash('sha256', $attribute->code.':'.$data['code']), 0, 32), $data['translate']);
            $option ??= new CatalogAttributeOption(['attribute_id' => $attribute->id]);
            $option->fill(['code' => $data['code'], 'language_id' => $language->id, 'is_active' => $data['is_active'], 'revision' => (string) Str::uuid()]);
            $option->save();

            return $option->fresh('language');
        });
    }

    private function saveLanguage(string $key, array $translations): Language
    {
        $language = Language::updateOrCreate(['key' => $key], [
            'pt' => trim($translations['pt-br']['name']), 'es' => trim($translations['es']['name']), 'en' => trim($translations['en']['name']),
        ]);
        DB::afterCommit(fn () => Cache::forget('all_translations_db'));

        return $language;
    }

    public function dictionary(): \Illuminate\Support\Collection
    {
        return CatalogAttribute::with(['language', 'options.language'])->orderBy('code')->get()->map(fn ($a) => [
            'id' => $a->id, 'code' => $a->code, 'name' => $a->label(), 'active' => $a->is_active,
            'translations' => $a->language->only(['pt', 'es', 'en']), 'type' => $a->data_type, 'unit' => $a->unit,
            'inherit' => $a->inherit_from_parent,
            'options' => $a->options->map(fn ($o) => ['id' => $o->id, 'code' => $o->code, 'name' => $o->label(), 'active' => $o->is_active, 'translations' => $o->language->only(['pt', 'es', 'en'])])->all(),
        ]);
    }

    /** Resolve only the explicit parent chain. Color families are intentionally absent. */
    public function effective(Product $product, array $visited = []): array
    {
        if (! $this->enabledFor($product) || isset($visited[$product->id]) || count($visited) > 10) {
            return [];
        }
        $visited[$product->id] = true;
        $effective = [];
        if ($product->parent_id && (int) $product->parent_id !== (int) $product->id) {
            $parent = Product::withoutGlobalScopes()->find($product->parent_id);
            if ($parent) {
                foreach ($this->effective($parent, $visited) as $id => $value) {
                    if ($value['inheritable'] && $value['mode'] === 'value') {
                        $value['inherited'] = true;
                        $effective[$id] = $value;
                    }
                }
            }
        }
        foreach (ProductAttributeValue::with(['attribute', 'options'])->where('product_id', $product->id)->get() as $value) {
            if ($value->mode === 'inherit') {
                continue;
            }
            $effective[$value->attribute_id] = [
                'mode' => $value->mode, 'boolean' => $value->boolean_value, 'number' => $value->number_value,
                'options' => $value->options->pluck('id')->all(), 'source' => $value->source,
                'inherited' => false, 'from_product_id' => $product->id,
                'inheritable' => $value->attribute->inherit_from_parent, 'revision' => $value->revision,
            ];
        }

        return $effective;
    }

    public function saveProduct(Product $product, array $rows, ?int $userId): void
    {
        abort_unless($this->enabledFor($product), 422, 'El diccionario de atributos no está disponible.');
        Validator::make(['catalog_values' => $rows], [
            'catalog_values' => 'array|max:150', 'catalog_values.*.attribute_id' => 'required|integer|distinct|exists:catalog_attributes,id',
            'catalog_values.*.mode' => 'required|in:value,inherit,block',
        ])->validate();
        DB::transaction(function () use ($product, $rows, $userId) {
            Product::withoutGlobalScopes()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            foreach ($rows as $index => $row) {
                $attribute = CatalogAttribute::findOrFail($row['attribute_id']);
                $existing = ProductAttributeValue::where('product_id', $product->id)->where('attribute_id', $attribute->id)->first();
                if (! $attribute->is_active && ! $existing && $row['mode'] === 'value') {
                    throw ValidationException::withMessages(["catalog_values.{$index}" => 'El atributo está desactivado.']);
                }
                try {
                    $data = $row['mode'] === 'value' ? $this->validatedValue($attribute, $row, $existing) : ['boolean_value' => null, 'number_value' => null, 'option_ids' => []];
                } catch (ValidationException $exception) {
                    throw ValidationException::withMessages(collect($exception->errors())->mapWithKeys(fn ($messages, $field) => ["catalog_values.{$index}.{$field}" => array_map(fn ($message) => $attribute->label().': '.$message, $messages)])->all());
                }
                $options = $data['option_ids'];
                unset($data['option_ids']);
                $value = ProductAttributeValue::updateOrCreate(['product_id' => $product->id, 'attribute_id' => $attribute->id], $data + [
                    'mode' => $row['mode'], 'source' => 'manual', 'evidence' => null, 'updated_by' => $userId, 'revision' => (string) Str::uuid(),
                ]);
                $value->options()->sync($options);
            }
            $this->enqueue($product->id);
        });
    }

    private function validatedValue(CatalogAttribute $attribute, array $row, ?ProductAttributeValue $existing = null): array
    {
        $rules = match ($attribute->data_type) {
            'boolean' => ['boolean' => 'required|boolean'],
            'number' => ['number' => ['required', 'numeric', 'between:-999999999,999999999', 'regex:/^-?\d+(\.\d{1,6})?$/']],
            default => ['options' => 'required|array|min:1|max:'.($attribute->data_type === 'single' ? 1 : 100), 'options.*' => 'required|integer|distinct'],
        };
        Validator::make($row, $rules)->validate();
        $options = in_array($attribute->data_type, ['single', 'multiple']) ? array_map('intval', $row['options'] ?? []) : [];
        $allowed = $attribute->options()->where(function ($q) use ($existing) {
            $q->where('is_active', true);
            if ($existing) {
                $q->orWhereIn('id', $existing->options()->pluck('catalog_attribute_options.id'));
            }
        })->pluck('id')->all();
        if (array_diff($options, $allowed)) {
            throw ValidationException::withMessages(['options' => 'Seleccioná opciones válidas del atributo.']);
        }

        return ['boolean_value' => $attribute->data_type === 'boolean' ? (bool) $row['boolean'] : null,
            'number_value' => $attribute->data_type === 'number' ? $row['number'] : null,
            'option_ids' => in_array($attribute->data_type, ['single', 'multiple']) ? $options : []];
    }

    public function missing(Product $product): array
    {
        if (! $this->enabledFor($product)) {
            return [];
        }
        $effective = $this->effective($product);
        $manual = ProductAttributeValue::where('product_id', $product->id)->where('source', 'manual')->pluck('attribute_id')->all();
        return CatalogAttribute::where('is_active', true)->pluck('id')
            ->reject(fn ($id) => isset($effective[$id]) || in_array($id, $manual))->values()->all();
    }

    public function aiContext(Product $product): array
    {
        if (! $this->enabledFor($product)) {
            return [];
        }

        return ['missing_attribute_ids' => $this->missing($product), 'dictionary' => $this->dictionary()->where('active', true)->values()->all(), 'effective' => $this->effective($product),
            'protected_ids' => ProductAttributeValue::where('product_id', $product->id)->where('source', 'manual')->pluck('attribute_id')->all()];
    }

    public function applyAi(Product $product, array $result, ?int $userId): int
    {
        if (! $this->enabledFor($product) || data_get($result, 'research.status') !== 'matched') {
            return 0;
        }
        $urls = collect(data_get($result, 'research.sources', []))->pluck('url')->all();

        return DB::transaction(function () use ($product, $result, $userId, $urls) {
            Product::withoutGlobalScopes()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $effective = $this->effective($product->fresh());
            $applied = 0;
            foreach ((array) data_get($result, 'proposal.catalog_attributes', []) as $row) {
                if (! is_array($row) || ($row['confidence'] ?? '') !== 'high' || trim((string) ($row['evidence'] ?? '')) === ''
                    || ($row['product_sku'] ?? '') !== (string) $product->sku || ($row['variant_match'] ?? false) !== true
                    || ! in_array($row['source_url'] ?? '', $urls, true)) {
                    continue;
                }
                $attribute = CatalogAttribute::where('is_active', true)->where('code', $row['code'] ?? '')->first();
                if (! $attribute || isset($effective[$attribute->id]) || ProductAttributeValue::where('product_id', $product->id)->where('attribute_id', $attribute->id)->exists()) {
                    continue;
                }
                // A proposal is for this exact SKU, never every target in a size batch.
                $optionIds = $attribute->options()->where('is_active', true)->whereIn('code', $row['option_codes'] ?? [])->pluck('id')->all();
                if (count($optionIds) !== count(array_unique($row['option_codes'] ?? []))) {
                    continue;
                }
                try {
                    $data = $this->validatedValue($attribute, ['options' => $optionIds, 'boolean' => $row['boolean_value'] ?? null, 'number' => $row['number_value'] ?? null]);
                } catch (ValidationException) {
                    continue;
                }
                $options = $data['option_ids'];
                unset($data['option_ids']);
                $value = ProductAttributeValue::create($data + ['product_id' => $product->id, 'attribute_id' => $attribute->id, 'mode' => 'value',
                    'source' => 'ai', 'evidence' => ['url' => $row['source_url'], 'text' => $row['evidence']], 'updated_by' => $userId, 'revision' => (string) Str::uuid()]);
                $value->options()->sync($options);
                $effective[$attribute->id] = true;
                $applied++;
            }
            $this->enqueue($product->id);

            return $applied;
        });
    }

    private function enqueue(int $productId): void
    {
        DB::afterCommit(function () use ($productId) {
            if (config('catalog-sync.enabled')) {
                app(OpticalProductSyncQueueService::class)->enqueue($productId);
            }
        });
    }
}
