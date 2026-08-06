<?php

namespace App\Services;

use App\Models\CategoriasFilhas;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DeepSeekCatalogService
{
    /**
     * Generates a reviewable proposal only. This method never writes to Product.
     */
    public function generateProductProposal(Product $product): array
    {
        $apiKey = (string) config('services.deepseek.api_key');
        if ($apiKey === '') {
            throw new RuntimeException('La API de DeepSeek no está configurada.');
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

        $systemPrompt = <<<'PROMPT'
Eres el asistente oficial de catalogación de productos del ecommerce de SAX Department Store.
Devuelve SOLAMENTE JSON válido, sin Markdown ni texto adicional. La propuesta será revisada por
una persona y nunca debe tratarse automáticamente como información verificada.

Tu función es convertir los datos internos recibidos en información comercial estandarizada en
portugués de Brasil, español e inglés. Sigue obligatoriamente estas reglas:

1. Escribe commercial_name.pt_br, commercial_name.es y commercial_name.en completamente en
MAYÚSCULAS. Los tres nombres deben seguir la misma estructura y representar exactamente el mismo
producto.
2. Conserva con máxima fidelidad la marca, el modelo, la referencia, la variante, el color y el
tamaño o volumen presentes en los datos. No traduzcas marcas, nombres propios, referencias ni
códigos del fabricante.
3. Si original_name contiene una talla después de #, reconoce esa parte como talla, pero no la
incluyas en el nombre comercial. Puede conservarse como atributo verificado.
4. Si original_name contiene un código de color después de *, reconoce esa parte como código de
color. No lo conviertas en un nombre comercial de color sin evidencia explícita.
5. Identifica la marca y la referencia únicamente a partir de los campos recibidos. Confirma el
tipo exacto de producto solamente cuando los datos aportados permitan hacerlo.
6. No tienes acceso a internet en esta solicitud. No afirmes que buscaste, investigaste o
confirmaste información en sitios externos. Si falta evidencia para modelo, color comercial,
material, características o público objetivo, indícalo en missing_data.
7. Mantén el mismo contenido factual en los tres idiomas, redactado naturalmente en cada idioma.
No agregues en una traducción datos que no aparezcan en las otras.
8. Nunca inventes datos.
9. La categoría principal fixed_category viene de la integración y es inmutable. No propongas
cambiarla. Para taxonomy_selection, selecciona únicamente IDs incluidos en allowed_subcategories
y allowed_child_categories. La categoría hija elegida debe pertenecer a la subcategoría elegida.
Si no hay evidencia suficiente o no existe una opción adecuada, devuelve null en ese ID.

Separa hechos de sugerencias. verified_attributes solo puede contener datos recibidos
explícitamente. suggested_attributes puede contener interpretaciones editoriales razonables, pero
cada una debe explicar su motivo y nivel de confianza. Nunca presentes como hechos materiales,
medidas, composición, origen, especificaciones técnicas, notas olfativas, concentración,
ingredientes, rendimiento o afirmaciones inferidas. Agrega a missing_data los campos importantes
que no estén disponibles.

Use this exact JSON shape:
{
  "editorial_summary": "",
  "commercial_name": {"pt_br": "", "es": "", "en": ""},
  "short_description": {"pt_br": "", "es": "", "en": ""},
  "descriptions": {"pt_br": "", "es": "", "en": ""},
  "verified_attributes": {},
  "suggested_attributes": [
    {"name": "", "value": "", "reason": "", "confidence": "low"}
  ],
  "seo": {
    "title_pt_br": "",
    "meta_description_pt_br": "",
    "search_terms": []
  },
  "taxonomy_selection": {
    "subcategory_id": null,
    "childcategory_id": null,
    "reason": "",
    "confidence": "low"
  },
  "missing_data": [],
  "warnings": [],
  "confidence": "low"
}

Redacta textos naturales, específicos y acordes a un catálogo premium, evitando frases genéricas.
Cada descripción completa debe tener entre 70 y 120 palabras, en texto plano. Puede explicar el
posicionamiento comercial y la experiencia buscada, pero sin inventar propiedades físicas.
short_description debe ser una sola oración concisa. No menciones la IA en el contenido comercial.
PROMPT;

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.deepseek.timeout', 60))
            ->post(rtrim((string) config('services.deepseek.base_url'), '/') . '/chat/completions', [
                'model' => (string) config('services.deepseek.model', 'deepseek-v4-flash'),
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    [
                        'role' => 'user',
                        'content' => "Prepare a product catalog proposal from this known JSON data:\n" .
                            json_encode($knownData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ],
                ],
                'response_format' => ['type' => 'json_object'],
                'thinking' => ['type' => 'disabled'],
                'temperature' => 0.4,
                'max_tokens' => 1800,
                'stream' => false,
            ]);

        try {
            $response->throw();
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            throw new RuntimeException(
                $status === 402
                    ? 'La cuenta de DeepSeek no tiene saldo suficiente.'
                    : 'DeepSeek no pudo generar la propuesta (HTTP ' . ($status ?: 'desconocido') . ').',
                previous: $exception
            );
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('DeepSeek devolvió una respuesta vacía.');
        }

        $proposal = json_decode($content, true);
        if (! is_array($proposal) || ! isset($proposal['commercial_name'], $proposal['descriptions'])) {
            throw new RuntimeException('DeepSeek devolvió un JSON con formato inesperado.');
        }

        $proposal = $this->normalizeProposal(
            $proposal,
            $allowedSubcategories->keyBy(fn (Subcategory $subcategory) => (int) $subcategory->id),
            $allowedChildCategories->keyBy(fn (CategoriasFilhas $childCategory) => (int) $childCategory->id),
        );

        return [
            'proposal' => $proposal,
            'source' => [
                'provider' => 'DeepSeek',
                'model' => (string) config('services.deepseek.model', 'deepseek-v4-flash'),
                'saved' => false,
            ],
            'usage' => $response->json('usage', []),
        ];
    }

    /**
     * Enforces catalog rules that should not depend only on model compliance.
     */
    private function normalizeProposal(array $proposal, $allowedSubcategories, $allowedChildCategories): array
    {
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

            if (! $selectedChildCategory
                || (int) $selectedChildCategory->subcategory_id !== (int) $selectedSubcategory->id) {
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
