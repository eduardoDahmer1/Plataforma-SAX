<?php

namespace App\Services;

use App\Models\CategoriasFilhas;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAICatalogService
{
    /**
     * Generates a reviewable proposal only. This method never writes to Product.
     */
    public function generateProductProposal(Product $product): array
    {
        $apiKey = (string) config('services.openai.api_key');
        if ($apiKey === '') {
            throw new RuntimeException('La API de OpenAI no está configurada.');
        }

        $allowedSubcategories = filled($product->category_id)
            ? Subcategory::where('category_id', $product->category_id)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'category_id'])
            : collect();
        $allowedSubcategoryIds = $allowedSubcategories->pluck('id')->map(fn ($id) => (int) $id);
        $allowedChildCategories = $allowedSubcategoryIds->isNotEmpty()
            ? CategoriasFilhas::whereIn('subcategory_id', $allowedSubcategoryIds)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'subcategory_id', 'category_id'])
            : collect();

        $knownData = [
            'sku' => $product->sku,
            'original_name' => $product->external_name,
            'current_commercial_name' => $product->name,
            'current_description' => $product->description,
            'brand' => $product->brand?->name,
            'category' => $product->category?->name,
            'subcategory' => $product->subcategory?->name,
            'child_category' => $product->categoriasFilhas?->name,
            'size_or_volume' => $product->size,
            'color' => $product->color,
            'material' => $product->material,
            'measure' => $product->measure,
            'gtin' => $product->gtin,
            'mpn' => $product->mpn,
            'reference_code' => $product->ref_code,
            'taxonomy' => [
                'fixed_category' => [
                    'id' => $product->category_id,
                    'name' => $product->category?->name,
                ],
                'allowed_subcategories' => $allowedSubcategories->map(fn (Subcategory $subcategory) => [
                    'id' => (int) $subcategory->id,
                    'name' => $subcategory->name ?: $subcategory->slug,
                ])->values()->all(),
                'allowed_child_categories' => $allowedChildCategories->map(fn (CategoriasFilhas $childCategory) => [
                    'id' => (int) $childCategory->id,
                    'subcategory_id' => (int) $childCategory->subcategory_id,
                    'name' => $childCategory->name ?: $childCategory->slug,
                ])->values()->all(),
            ],
        ];

        $instructions = <<<'PROMPT'
Eres el asistente oficial de catalogación de productos del ecommerce de SAX Department Store.
La salida debe cumplir exactamente el JSON Schema recibido. La propuesta será revisada por una
persona y nunca debe tratarse automáticamente como información verificada.

Convierte los datos internos recibidos en información comercial estandarizada en portugués de
Brasil, español e inglés. Conserva con máxima fidelidad la marca, modelo, referencia, variante,
color y tamaño o volumen. No traduzcas marcas, nombres propios, referencias ni códigos.

Puedes usar web_search cuando existan identificadores suficientes (marca, GTIN, MPN, referencia
o nombre original) para buscar una coincidencia del producto. Prioriza el sitio oficial de la
marca, distribuidores autorizados y fuentes comerciales confiables. No afirmes que una búsqueda
confirmó un dato si no existe una coincidencia clara. Si no hay coincidencia confiable, devuelve
web_research.status como not_found y continúa usando únicamente los datos internos.

Los datos externos deben quedar identificados en web_research.findings y nunca pueden reemplazar
la categoría principal fixed_category ni permitir IDs fuera de allowed_subcategories y
allowed_child_categories. No inventes características, materiales, medidas, ingredientes,
concentraciones, rendimiento u otras especificaciones. Separa hechos de sugerencias y agrega la
información faltante a missing_data. Mantén el mismo contenido factual en los tres idiomas.

Si original_name contiene una talla después de #, reconócela pero no la incluyas en el nombre
comercial. Si contiene un código después de *, trátalo como código de color y no lo conviertas en
un color comercial sin evidencia explícita. Los tres nombres comerciales deben estar en MAYÚSCULAS.
Cada descripción completa debe tener entre 70 y 120 palabras y short_description debe ser una sola
oración concisa. No menciones la IA en el contenido comercial.
PROMPT;

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.openai.timeout', 90))
            ->post(rtrim((string) config('services.openai.base_url'), '/').'/responses', [
                'model' => (string) config('services.openai.model', 'gpt-5.6-luna'),
                'instructions' => $instructions,
                'input' => "Prepare a product catalog proposal from this known JSON data:\n".
                    json_encode($knownData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'reasoning' => ['effort' => (string) config('services.openai.reasoning_effort', 'low')],
                'tools' => [['type' => 'web_search']],
                'tool_choice' => 'auto',
                'include' => ['web_search_call.action.sources'],
                'store' => false,
                'max_output_tokens' => (int) config('services.openai.max_output_tokens', 3000),
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'catalog_proposal',
                        'strict' => true,
                        'schema' => $this->responseSchema(),
                    ],
                ],
            ]);

        try {
            $response->throw();
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $message = match ($status) {
                401, 403 => 'La clave de OpenAI no es válida o no tiene permisos.',
                429 => 'OpenAI rechazó la solicitud por límite de uso o cuota agotada.',
                default => 'OpenAI no pudo generar la propuesta (HTTP '.($status ?: 'desconocido').').',
            };
            throw new RuntimeException($message, previous: $exception);
        }

        $payload = $response->json();
        if (data_get($payload, 'status') === 'incomplete') {
            throw new RuntimeException('OpenAI devolvió una respuesta incompleta.');
        }

        $content = $this->extractOutputText($payload);
        if ($content === '') {
            throw new RuntimeException('OpenAI devolvió una respuesta vacía o fue rechazada.');
        }

        $proposal = json_decode($content, true);
        if (! is_array($proposal) || ! isset($proposal['commercial_name'], $proposal['descriptions'])) {
            throw new RuntimeException('OpenAI devolvió un JSON con formato inesperado.');
        }

        $proposal = $this->normalizeProposal(
            $proposal,
            $allowedSubcategories->keyBy(fn (Subcategory $subcategory) => (int) $subcategory->id),
            $allowedChildCategories->keyBy(fn (CategoriasFilhas $childCategory) => (int) $childCategory->id),
        );

        $sources = $this->extractSources($payload);
        $researchStatus = data_get($proposal, 'web_research.status') === 'matched' && $sources !== []
            ? 'matched'
            : 'not_found';

        return [
            'proposal' => $proposal,
            'research' => [
                'status' => $researchStatus,
                'sources' => $sources,
                'warning' => $researchStatus === 'not_found'
                    ? 'No se encontró una coincidencia web confiable; revisá la propuesta con los datos internos.'
                    : null,
            ],
            'source' => [
                'provider' => 'OpenAI',
                'model' => (string) config('services.openai.model', 'gpt-5.6-luna'),
                'saved' => false,
            ],
            'usage' => data_get($payload, 'usage', []),
        ];
    }

    private function extractOutputText(array $payload): string
    {
        $texts = [];
        foreach ((array) data_get($payload, 'output', []) as $item) {
            foreach ((array) data_get($item, 'content', []) as $content) {
                if (data_get($content, 'type') === 'output_text' && is_string(data_get($content, 'text'))) {
                    $texts[] = data_get($content, 'text');
                }
            }
        }

        return trim(implode("\n", $texts));
    }

    private function extractSources(array $payload): array
    {
        $sources = [];
        foreach ((array) data_get($payload, 'output', []) as $item) {
            if (data_get($item, 'type') !== 'web_search_call') {
                continue;
            }
            foreach ((array) data_get($item, 'action.sources', []) as $source) {
                $url = data_get($source, 'url');
                if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL) || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
                    continue;
                }
                $sources[$url] = ['title' => (string) data_get($source, 'title', $url), 'url' => $url];
            }
        }

        return array_values($sources);
    }

    private function responseSchema(): array
    {
        $languageText = [
            'type' => 'object',
            'properties' => ['pt_br' => ['type' => 'string'], 'es' => ['type' => 'string'], 'en' => ['type' => 'string']],
            'required' => ['pt_br', 'es', 'en'],
            'additionalProperties' => false,
        ];

        return [
            'type' => 'object',
            'properties' => [
                'editorial_summary' => ['type' => 'string'],
                'commercial_name' => $languageText,
                'short_description' => $languageText,
                'descriptions' => $languageText,
                'verified_attributes' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'properties' => ['name' => ['type' => 'string'], 'value' => ['type' => 'string']],
                    'required' => ['name', 'value'],
                    'additionalProperties' => false,
                ]],
                'suggested_attributes' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'properties' => ['name' => ['type' => 'string'], 'value' => ['type' => 'string'], 'reason' => ['type' => 'string'], 'confidence' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']]],
                    'required' => ['name', 'value', 'reason', 'confidence'],
                    'additionalProperties' => false,
                ]],
                'seo' => ['type' => 'object', 'properties' => ['title_pt_br' => ['type' => 'string'], 'meta_description_pt_br' => ['type' => 'string'], 'search_terms' => ['type' => 'array', 'items' => ['type' => 'string']]], 'required' => ['title_pt_br', 'meta_description_pt_br', 'search_terms'], 'additionalProperties' => false],
                'taxonomy_selection' => ['type' => 'object', 'properties' => ['subcategory_id' => ['type' => ['integer', 'null']], 'childcategory_id' => ['type' => ['integer', 'null']], 'reason' => ['type' => 'string'], 'confidence' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']]], 'required' => ['subcategory_id', 'childcategory_id', 'reason', 'confidence'], 'additionalProperties' => false],
                'missing_data' => ['type' => 'array', 'items' => ['type' => 'string']],
                'warnings' => ['type' => 'array', 'items' => ['type' => 'string']],
                'confidence' => ['type' => 'string', 'enum' => ['low', 'medium', 'high']],
                'web_research' => ['type' => 'object', 'properties' => ['status' => ['type' => 'string', 'enum' => ['matched', 'not_found']], 'findings' => ['type' => 'array', 'items' => ['type' => 'string']]], 'required' => ['status', 'findings'], 'additionalProperties' => false],
            ],
            'required' => ['editorial_summary', 'commercial_name', 'short_description', 'descriptions', 'verified_attributes', 'suggested_attributes', 'seo', 'taxonomy_selection', 'missing_data', 'warnings', 'confidence', 'web_research'],
            'additionalProperties' => false,
        ];
    }

    private function normalizeProposal(array $proposal, $allowedSubcategories, $allowedChildCategories): array
    {
        $verifiedAttributes = data_get($proposal, 'verified_attributes');
        if (is_array($verifiedAttributes) && array_is_list($verifiedAttributes)) {
            $normalizedAttributes = [];
            foreach ($verifiedAttributes as $attribute) {
                if (is_array($attribute) && filled($attribute['name'] ?? null)) {
                    $normalizedAttributes[(string) $attribute['name']] = $attribute['value'] ?? '';
                }
            }
            data_set($proposal, 'verified_attributes', $normalizedAttributes);
        }

        foreach (['pt_br', 'es', 'en'] as $language) {
            $name = data_get($proposal, "commercial_name.{$language}");
            if (is_string($name)) {
                data_set($proposal, "commercial_name.{$language}", mb_strtoupper(trim($name), 'UTF-8'));
            }
        }

        $requestedSubcategoryId = (int) data_get($proposal, 'taxonomy_selection.subcategory_id', 0);
        $requestedChildCategoryId = (int) data_get($proposal, 'taxonomy_selection.childcategory_id', 0);
        $selectedSubcategory = $allowedSubcategories->get($requestedSubcategoryId);
        $selectedChildCategory = $allowedChildCategories->get($requestedChildCategoryId);

        if (! $selectedSubcategory) {
            data_set($proposal, 'taxonomy_selection.subcategory_id', null);
            data_set($proposal, 'taxonomy_selection.subcategory_name', null);
            data_set($proposal, 'taxonomy_selection.childcategory_id', null);
            data_set($proposal, 'taxonomy_selection.childcategory_name', null);
        } else {
            data_set($proposal, 'taxonomy_selection.subcategory_id', (int) $selectedSubcategory->id);
            data_set($proposal, 'taxonomy_selection.subcategory_name', $selectedSubcategory->name ?: $selectedSubcategory->slug);
            if (! $selectedChildCategory || (int) $selectedChildCategory->subcategory_id !== (int) $selectedSubcategory->id) {
                data_set($proposal, 'taxonomy_selection.childcategory_id', null);
                data_set($proposal, 'taxonomy_selection.childcategory_name', null);
            } else {
                data_set($proposal, 'taxonomy_selection.childcategory_id', (int) $selectedChildCategory->id);
                data_set($proposal, 'taxonomy_selection.childcategory_name', $selectedChildCategory->name ?: $selectedChildCategory->slug);
            }
        }

        return $proposal;
    }
}
