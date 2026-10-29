<?php

namespace App\Services;

use App\Models\CatalogAttribute;
use App\Models\CatalogAttributeOption;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
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
        DB::transaction(function () use ($product, $rows, $userId) {
            Product::withoutGlobalScopes()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            if (count($rows) > 150) throw ValidationException::withMessages(['catalog_values' => 'Demasiados atributos.']);
            foreach ($rows as &$row) {
                if (filled($row['pending_definition'] ?? null)) $row = $this->materializeDefinition($product, $row);
            }
            unset($row);
            Validator::make(['catalog_values' => $rows], [
                'catalog_values' => 'array|max:150', 'catalog_values.*.attribute_id' => 'required|integer|distinct|exists:catalog_attributes,id',
                'catalog_values.*.mode' => 'required|in:value,inherit,block',
            ])->validate();
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
                $provenance = ['source' => 'manual', 'evidence' => null];
                if (filled($row['ai_proposal'] ?? null)) {
                    $provenance = $this->verifiedEditorProvenance($product, $attribute, $row, $data, $options, $existing);
                }
                $value = ProductAttributeValue::updateOrCreate(['product_id' => $product->id, 'attribute_id' => $attribute->id], $data + [
                    'mode' => $row['mode'], 'updated_by' => $userId, 'revision' => (string) Str::uuid(),
                ] + $provenance);
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

        return ['existing_attribute_codes' => $this->dictionary()->map(fn ($attribute) => collect($attribute)->only(['code', 'translations'])->all())->values()->all(), 'missing_attribute_ids' => $this->missing($product), 'dictionary' => $this->dictionary()->where('active', true)->values()->all(), 'effective' => $this->effective($product),
            'protected_ids' => ProductAttributeValue::where('product_id', $product->id)->where('source', 'manual')->pluck('attribute_id')->all()];
    }

    /** Validate once for both the editor and batch application; never trust provider fields alone. */
    public function reviewAi(Product $product, array $result, bool $allowNewAttributes = false): array
    {
        $rows = (array) data_get($result, 'proposal.catalog_attributes', []);
        $urls = collect(data_get($result, 'research.validation_sources', data_get($result, 'research.sources', [])))->pluck('url')->all();
        $available = $this->enabledFor($product);
        $effective = $available ? $this->effective($product) : [];
        $items = [];
        $seen = [];
        foreach ($rows as $row) {
            $row = is_array($row) ? $row : [];
            $item = ['code' => (string) ($row['code'] ?? ''), 'status' => 'rejected'];
            $reason = null;
            $attribute = null;
            if (! $available) $reason = 'El diccionario de atributos no está disponible.';
            elseif (data_get($result, 'research.status') !== 'matched') $reason = 'No hay una coincidencia web confiable.';
            elseif (($row['confidence'] ?? '') !== 'high') $reason = 'La confianza no es alta.';
            elseif (! is_string($row['evidence'] ?? null) || trim($row['evidence']) === '') $reason = 'Falta evidencia textual.';
            elseif (($row['product_sku'] ?? '') !== (string) $product->sku || ($row['variant_match'] ?? false) !== true) $reason = 'No coincide el SKU o la variante exacta.';
            elseif (! in_array($row['source_url'] ?? '', $urls, true)) $reason = 'La URL de evidencia no pertenece a las fuentes consultadas.';
            else {
                $attribute = CatalogAttribute::where('is_active', true)->where('code', $item['code'])->first();
                if (! $attribute && $allowNewAttributes && is_array($row['new_definition'] ?? null)) {
                    try {
                        $item = $this->reviewNewDefinition($row) + $item;
                    } catch (ValidationException $exception) {
                        $reason = collect($exception->errors())->flatten()->implode(' ');
                    }
                }
                elseif (! $attribute) $reason = 'El atributo no existe o está desactivado.';
                elseif (isset($seen[$attribute->id])) $reason = 'La propuesta repite el atributo.';
                elseif (isset($effective[$attribute->id]) || ProductAttributeValue::where('product_id', $product->id)->where('attribute_id', $attribute->id)->exists()) $reason = 'Existe un valor propio, heredado o una decisión protegida.';
                else {
                    $codes = $row['option_codes'] ?? [];
                    $options = is_array($codes) ? $attribute->options()->where('is_active', true)->whereIn('code', $codes)->pluck('id')->all() : [];
                    if (! is_array($codes) || count($options) !== count($codes)) $reason = 'Las opciones no existen, están desactivadas o se repiten.';
                    else {
                        try {
                            $value = $this->validatedValue($attribute, ['options' => $options, 'boolean' => $row['boolean_value'] ?? null, 'number' => $row['number_value'] ?? null]);
                            $item['value'] = ['attribute_id' => $attribute->id, 'mode' => 'value', 'options' => $value['option_ids'], 'boolean' => $value['boolean_value'], 'number' => $value['number_value']];
                            $item['evidence'] = ['url' => $row['source_url'], 'text' => $row['evidence']];
                            $item['status'] = 'accepted';
                            $seen[$attribute->id] = true;
                        } catch (ValidationException) {
                            $reason = 'El valor no cumple el tipo, unidad u opciones del diccionario.';
                        }
                    }
                }
            }
            if ($item['status'] === 'accepted' && isset($item['dictionary_item'])) {
                if (isset($seen['new:'.$item['code']])) { $item['status'] = 'rejected'; $reason = 'La propuesta repite el atributo.'; }
                $seen['new:'.$item['code']] = true;
            }
            $item['reason'] = $reason;
            $items[] = $item;
        }
        $accepted = count(array_filter($items, fn ($item) => $item['status'] === 'accepted'));
        return ['suggested' => count($items), 'accepted' => $accepted, 'rejected' => count($items) - $accepted, 'items' => $items];
    }

    /** Only encrypted, product-bound proposals may retain AI provenance on a normal form save. */
    public function prepareForEditor(Product $product, array $result): array
    {
        $review = $this->reviewAi($product, $result, true);
        $newCount = 0;
        foreach ($review['items'] as &$item) {
            if ($item['status'] !== 'accepted') continue;
            if (isset($item['dictionary_item']) && ++$newCount > 8) {
                $item['status'] = 'rejected'; $item['reason'] = 'Se permiten hasta ocho atributos nuevos por propuesta.';
                $review['accepted']--; $review['rejected']++; continue;
            }
            $item['value']['ai_proposal'] = Crypt::encryptString(json_encode([
                'product_id' => (int) $product->id, 'identity' => $this->proposalIdentity($product),
                'expires_at' => now()->addDay()->timestamp, 'value' => $item['value'], 'evidence' => $item['evidence'],
                'definition_revision' => isset($item['dictionary_item']) ? null : CatalogAttribute::findOrFail($item['value']['attribute_id'])->revision,
                'pending_definition' => $item['dictionary_item'] ?? null,
            ], JSON_THROW_ON_ERROR));
            if (isset($item['dictionary_item'])) $item['value']['pending_definition'] = $item['value']['ai_proposal'];
        }
        unset($item);
        $result['attribute_review'] = $review;
        // Keep the complete validation set on the server; only three sources are displayed.
        unset($result['research']['validation_sources']);
        return $result;
    }

    private function reviewNewDefinition(array $row): array
    {
        $definition = $row['new_definition'];
        Validator::make($row, [
            'code' => ['required','string','max:80','regex:/^[a-z0-9_]+$/'],
            'new_definition.type' => 'required|in:single,multiple,boolean,number',
            'new_definition.unit' => 'nullable|string|max:20',
            'new_definition.labels' => 'required|array:es,pt,en',
            'new_definition.labels.es' => 'required|string|max:255',
            'new_definition.labels.pt' => 'required|string|max:255',
            'new_definition.labels.en' => 'required|string|max:255',
            'new_definition.options' => 'present|array|max:30',
            'new_definition.options.*.code' => ['required','string','max:80','distinct','regex:/^[a-z0-9_]+$/'],
            'new_definition.options.*.labels.es' => 'required|string|max:255',
            'new_definition.options.*.labels.pt' => 'required|string|max:255',
            'new_definition.options.*.labels.en' => 'required|string|max:255',
            'option_codes' => 'present|array|max:30', 'option_codes.*' => 'required|string|distinct',
        ])->validate();
        if ($row['code'] !== Str::slug($definition['labels']['es'], '_')) throw ValidationException::withMessages(['catalog_values'=>'El código debe corresponder al nombre en español.']);
        foreach ($definition['options'] as $option) {
            if ($option['code'] !== Str::slug($option['labels']['es'], '_')) throw ValidationException::withMessages(['catalog_values'=>'El código de opción debe corresponder a su nombre en español.']);
        }
        foreach ($this->dictionary() as $existing) {
            $sameLabel = collect($definition['labels'])->contains(fn ($label, $locale) => Str::slug($label) === Str::slug($existing['translations'][$locale] ?? ''));
            if ($existing['code'] === $row['code'] || $sameLabel) {
                throw ValidationException::withMessages(['catalog_values' => 'Ya existe un atributo con ese código o nombre. Usá el diccionario existente.']);
            }
        }
        $select = in_array($definition['type'], ['single','multiple']);
        $codes = array_column($definition['options'], 'code');
        if (($definition['type'] !== 'number' && filled($definition['unit']))
            || (! $select && ($codes || $row['option_codes']))
            || ($select && (! $row['option_codes'] || array_diff($row['option_codes'], $codes)))
            || ($definition['type'] === 'single' && count($row['option_codes']) !== 1)) {
            throw ValidationException::withMessages(['catalog_values' => 'El tipo, unidad u opciones del atributo nuevo no son válidos.']);
        }
        Validator::make($row, match ($definition['type']) {
            'boolean' => ['boolean_value' => 'required|boolean'],
            'number' => ['number_value' => ['required','numeric','between:-999999999,999999999','regex:/^-?\d+(\.\d{1,6})?$/']],
            default => [],
        })->validate();
        $id = -hexdec(substr(hash('sha256', $row['code']), 0, 10));
        $options = collect($definition['options'])->values()->map(fn ($option, $index) => [
            'id' => -($index + 1), 'code' => $option['code'], 'name' => $option['labels']['es'], 'translations' => $option['labels'], 'active' => true,
        ])->all();
        $dictionary = ['id'=>$id, 'code'=>$row['code'], 'name'=>$definition['labels']['es'], 'translations'=>$definition['labels'],
            'type'=>$definition['type'], 'unit'=>$definition['unit'], 'active'=>true, 'inherit'=>false, 'options'=>$options, 'pending'=>true];
        return ['status'=>'accepted', 'dictionary_item'=>$dictionary,
            'value'=>['attribute_id'=>$id, 'mode'=>'value', 'options'=>array_column(array_filter($options, fn ($option) => in_array($option['code'], $row['option_codes'], true)), 'id'),
                'boolean'=>$definition['type'] === 'boolean' ? (bool) $row['boolean_value'] : null,
                'number'=>$definition['type'] === 'number' ? $row['number_value'] : null],
            'evidence'=>['url'=>$row['source_url'], 'text'=>$row['evidence']]];
    }

    /** Called inside the product transaction; a failed product save rolls back definitions too. */
    private function materializeDefinition(Product $product, array $row): array
    {
        try {
            $signed = json_decode(Crypt::decryptString($row['pending_definition']), true, 512, JSON_THROW_ON_ERROR);
            $definition = $signed['pending_definition'];
            if (! is_array($definition) || $signed['product_id'] !== (int) $product->id
                || $signed['identity'] !== $this->proposalIdentity($product) || $signed['expires_at'] < now()->timestamp
                || (string) $row['attribute_id'] !== (string) $definition['id'] || $row['mode'] !== 'value') {
                throw new \RuntimeException;
            }
        } catch (\Throwable) {
            throw ValidationException::withMessages(['catalog_values' => 'El atributo nuevo cambió, expiró o pertenece a otro producto. Volvé a generar la propuesta.']);
        }
        $attribute = CatalogAttribute::where('code', $definition['code'])->lockForUpdate()->first();
        if ($attribute) {
            if (! $attribute->is_active || $attribute->data_type !== $definition['type'] || $attribute->unit !== $definition['unit']) {
                throw ValidationException::withMessages(['catalog_values' => 'El atributo ya existe con otra definición. Volvé a generar la propuesta.']);
            }
        } else {
            // Recheck names in case another editor created the definition since generation.
            foreach ($this->dictionary() as $existing) {
                foreach ($definition['translations'] as $locale => $label) {
                    if (Str::slug($label) === Str::slug($existing['translations'][$locale] ?? '')) {
                        throw ValidationException::withMessages(['catalog_values' => 'Ya existe un atributo con ese nombre. Volvé a generar la propuesta.']);
                    }
                }
            }
            $attribute = $this->saveDefinition(['data_type'=>$definition['type'], 'unit'=>$definition['unit'], 'is_active'=>true, 'inherit_from_parent'=>false,
                'translate'=>$this->definitionTranslations($definition['translations'])]);

        }
        if (isset($this->effective($product)[$attribute->id]) || ProductAttributeValue::where('product_id', $product->id)->where('attribute_id', $attribute->id)->exists()) {
            throw ValidationException::withMessages(['catalog_values'=>'El atributo ya tiene un valor o una decisión protegida. Volvé a generar la propuesta.']);
        }
        $map = [];
        foreach ($definition['options'] as $option) {
            $saved = $attribute->options()->where('code', $option['code'])->first();
            if (! $saved) {
                $saved = $this->saveOption($attribute, ['is_active'=>true, 'translate'=>$this->definitionTranslations($option['translations'])]);
            }
            if (! $saved->is_active) throw ValidationException::withMessages(['catalog_values'=>'Una opción está desactivada. Volvé a generar la propuesta.']);
            $map[(string) $option['id']] = $saved->id;
        }
        $selected = array_map('strval', $row['options'] ?? []);
        if (array_diff($selected, array_keys($map))) throw ValidationException::withMessages(['catalog_values'=>'La opción del atributo nuevo no es válida.']);
        $original = $signed['value'];
        $unchanged = ($row['ai_proposal'] ?? null) === $row['pending_definition']
            && array_map('strval', $original['options']) === $selected
            && ($definition['type'] !== 'boolean' || (in_array($row['boolean'] ?? null, [true, false, 0, 1, '0', '1'], true) && $original['boolean'] === (bool) $row['boolean']))
            && (string) $original['number'] === (string) ($row['number'] ?? null);
        $row['attribute_id'] = $attribute->id;
        $row['options'] = array_map(fn ($id) => $map[$id], $selected);
        unset($row['ai_proposal'], $row['pending_definition']);
        if ($unchanged) {
            $signed['value'] = ['attribute_id'=>$attribute->id, 'mode'=>'value', 'options'=>$row['options'], 'boolean'=>$original['boolean'], 'number'=>$original['number']];
            $signed['definition_revision'] = $attribute->revision;
            unset($signed['pending_definition']);
            $row['ai_proposal'] = Crypt::encryptString(json_encode($signed, JSON_THROW_ON_ERROR));
        }
        return $row;
    }

    public function editorDictionary(Product $product, array $oldRows = []): \Illuminate\Support\Collection
    {
        $dictionary = $this->dictionary();
        foreach ($oldRows as $row) {
            if (empty($row['pending_definition'])) continue;
            try {
                $signed = json_decode(Crypt::decryptString($row['pending_definition']), true, 512, JSON_THROW_ON_ERROR);
                if ($signed['product_id'] === (int) $product->id && $signed['expires_at'] >= now()->timestamp && is_array($signed['pending_definition'] ?? null)) {
                    $dictionary->push($signed['pending_definition']);
                }
            } catch (\Throwable) { /* Invalid proposals cannot create definitions. */ }
        }
        return $dictionary->unique('id')->values();
    }

    private function definitionTranslations(array $labels): array
    {
        return ['es'=>['name'=>$labels['es']], 'pt-br'=>['name'=>$labels['pt']], 'en'=>['name'=>$labels['en']]];
    }

    private function proposalIdentity(Product $product): array
    {
        return collect(['sku', 'external_name', 'ref_code', 'brand_id', 'size', 'color'])->mapWithKeys(fn ($field) => [$field => (string) ($field === 'size' ? $product->inferredSize() : $product->getAttribute($field))])->all();
    }

    private function verifiedEditorProvenance(Product $product, CatalogAttribute $attribute, array $row, array $data, array $options, ?ProductAttributeValue $existing): array
    {
        try {
            $signed = json_decode(Crypt::decryptString($row['ai_proposal']), true, 512, JSON_THROW_ON_ERROR);
            $value = $signed['value'];
            $effective = $this->effective($product);
            $matches = (int) $signed['product_id'] === (int) $product->id
                && $signed['identity'] === $this->proposalIdentity($product) && $signed['expires_at'] >= now()->timestamp
                && ! $existing && ! isset($effective[$attribute->id]) && $attribute->is_active
                && $signed['definition_revision'] === $attribute->revision
                && $row['mode'] === 'value' && (int) $value['attribute_id'] === (int) $attribute->id
                && $value['options'] === $options && $value['boolean'] === $data['boolean_value']
                && (string) $value['number'] === (string) $data['number_value'];
            if ($matches) return ['source' => 'ai', 'evidence' => $signed['evidence']];
        } catch (\Throwable) {
            // A modified or expired token must not silently become a manual assignment.
        }
        throw ValidationException::withMessages(['catalog_values' => 'La propuesta IA cambió, expiró o pertenece a otra variante. Revisá el atributo o generá una nueva propuesta.']);
    }

    public function applyAi(Product $product, array $result, ?int $userId): int
    {
        if (! $this->enabledFor($product) || data_get($result, 'research.status') !== 'matched') {
            return 0;
        }
        return DB::transaction(function () use ($product, $result, $userId) {
            Product::withoutGlobalScopes()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $review = $this->reviewAi($product->fresh(), $result);
            $applied = 0;
            foreach ($review['items'] as $item) {
                if ($item['status'] !== 'accepted') continue;
                $row = $item['value'];
                $value = ProductAttributeValue::create([
                    'product_id' => $product->id, 'attribute_id' => $row['attribute_id'], 'mode' => 'value',
                    'boolean_value' => $row['boolean'], 'number_value' => $row['number'],
                    'source' => 'ai', 'evidence' => $item['evidence'], 'updated_by' => $userId, 'revision' => (string) Str::uuid(),
                ]);
                $value->options()->sync($row['options']);
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
