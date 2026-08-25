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

        $manufacturerIdentity = $product->manufacturerSearchIdentity();
        $manufacturerReferences = (array) ($manufacturerIdentity['reference_candidates'] ?? []);
        $normalizedManufacturerName = $this->normalizeSearchText($manufacturerIdentity['name'] ?? null);

        $knownData = [
            'internal_sku' => $product->sku,
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
            'internal_reference_code' => $product->ref_code,
            'manufacturer_reference_candidates' => $manufacturerReferences,
            'search_hints' => [
                'brand' => $product->brand?->name,
                'product_identity_normalized' => $normalizedManufacturerName,
                'exact_reference_candidates' => $manufacturerReferences,
                'brand_and_reference_candidates' => array_values(array_map(
                    fn ($reference) => trim(implode(' ', array_filter([$product->brand?->name, $reference]))),
                    $manufacturerReferences,
                )),
                'brand_and_product' => trim(implode(' ', array_filter([
                    $product->brand?->name,
                    $normalizedManufacturerName,
                ]))),
            ],
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
Brasil, español e inglés. Conserva con máxima fidelidad la marca, modelo, referencia y variante al
identificar el producto. Usa color y tamaño o volumen únicamente para verificar la variante, pero
nunca los incluyas en commercial_name ni en descriptions. No traduzcas marcas, nombres propios,
referencias ni códigos.

Usa web_search para investigar el producto. La integración no proporciona GTIN ni un campo MPN
separado. manufacturer_reference_candidates contiene todos los códigos con números extraídos del
nombre original antes de la talla (#) y el color (*); trátalos como posibles MPN o referencias del
fabricante. Busca con amplitud suficiente antes de concluir que no existe una coincidencia. Prueba
sucesivamente: (1) cada referencia candidata exacta entre comillas; (2) la marca con cada referencia;
(3) cada referencia con y sin espacios, guiones, puntos o barras; y (4) marca con el nombre normalizado.
No confundas internal_sku ni internal_reference_code con referencias públicas del fabricante: úsalos
solamente como último recurso si no existen candidatos extraídos. Ignora símbolos de talla y códigos
de color que puedan impedir coincidencias, pero no elimines letras o números que formen parte de la
marca, modelo o referencia.
No limites la investigación a tres páginas: el sistema mostrará después únicamente las tres mejores.

Para verificar una coincidencia, compara marca, referencia o identificador, modelo y variante dentro
del contenido de la página. Que la referencia aparezca en la URL es una señal fuerte y debe recibir
prioridad, pero no es un requisito si la página identifica inequívocamente el producto en su contenido.
Prioriza, en este orden: sitio oficial de la marca, distribuidores autorizados y comercios reconocidos
y confiables. Si no existe una fuente oficial, puedes usar una fuente comercial confiable. Descarta
agregadores, resultados genéricos, marketplaces de vendedores no verificados, páginas sin relación
clara y contenido duplicado. Antes de responder, selecciona en web_research.selected_source_urls
como máximo las tres URLs más confiables y específicas que realmente respalden la identificación.
Solo después de agotar las búsquedas razonables, devuelve web_research.status como not_found y
continúa usando únicamente los datos internos.

Los datos externos deben quedar identificados en web_research.findings y nunca pueden reemplazar
la categoría principal fixed_category ni permitir IDs fuera de allowed_subcategories y
allowed_child_categories. Una característica solo puede presentarse como un hecho cuando esté
respaldada por los datos internos o por una fuente web confiable que corresponda inequívocamente al
producto identificado. No deduzcas características a partir de la marca, la categoría, productos
similares, otra variante del mismo modelo ni conocimiento general sobre la colección. Si un dato no
puede verificarse razonablemente, omítelo. No inventes materiales, medidas, ingredientes,
concentraciones, rendimiento u otras especificaciones. Separa hechos de sugerencias y agrega la
información faltante a missing_data. missing_data, warnings, web_research y editorial_summary son
exclusivamente para revisión administrativa: nunca menciones faltantes, incertidumbre, fuentes ni
advertencias dentro de commercial_name o descriptions.

Las descriptions son el texto final que verá el cliente en la tienda, no un informe para el operador.
Nunca hables del proceso de investigación, de la referencia consultada ni del grado de verificación.
No uses expresiones como "característica confirmada", "dato verificado", "para esta referencia",
"según las fuentes", "producto identificado", "información disponible" ni equivalentes en portugués
o inglés. Estas reglas de comprobación son internas: presenta directamente los hechos del producto,
sin explicar cómo fueron encontrados o validados y sin dirigirte al administrador.
Las descriptions deben contener únicamente el texto comercial final del producto. Nunca incluyas
URLs, enlaces, citas, referencias bibliográficas, nombres de fuentes, dominios, marcadores de citación
ni expresiones como "según la web oficial" o "según el fabricante", tampoco sus equivalentes en
portugués o inglés. Las fuentes se utilizan exclusivamente para investigar y verificar información y
deben devolverse únicamente dentro de web_research.

Si original_name contiene una talla después de #, reconócela pero no la incluyas en el nombre
comercial. Si contiene un código después de *, trátalo como código de color y no lo conviertas en
un color comercial sin evidencia explícita. Solo los tres campos commercial_name deben estar en
MAYÚSCULAS. Las descriptions deben usar capitalización natural de oración: nunca escribas una frase
o un párrafo completo en mayúsculas. Conserva mayúsculas únicamente en siglas, códigos y nombres de
marca cuando su escritura oficial lo requiera.

Regla obligatoria para el contenido comercial: commercial_name y descriptions no deben mencionar
ningún color, talla, tamaño, medida, volumen, capacidad ni variante basada en esas características.
Aunque estos datos aparezcan en la entrada o sean confirmados por las fuentes, omítelos por completo
del nombre y de las descripciones. La descripción debe centrarse únicamente en la identidad, diseño,
materiales, acabados, construcción, función y demás cualidades del producto.

Adapta la extensión de cada descripción a la cantidad de información verificable disponible. Usa
aproximadamente 80 a 160 palabras como referencia editorial, no como una cuota obligatoria. Si
existen pocos datos verificables, una descripción de aproximadamente 60 a 100 palabras es correcta
y preferible a completar longitud con inferencias, repeticiones o lenguaje genérico. Puede utilizar
uno, dos o tres párrafos separados por una línea en blanco; cada párrafo debe aportar información
nueva y concreta sobre el producto.

Prioriza colección o línea, materiales, construcción, acabados, tecnologías, funciones, componentes
y detalles distintivos propios del modelo cuando estén verificados. Habla exclusivamente de la
identidad y las características específicas del producto. No uses expresiones de relleno como
"actitud urbana", "líneas arquitectónicas", "diseño vigente", "ejecución cuidada", "para quienes
valoran" o "presencia marcada". No menciones colores, tallas, tamaños, medidas, capacidades o
volúmenes, aunque esos datos aparezcan en la entrada o sean esenciales para identificar el producto.
No recomiendes con qué combinarlo y no describas conjuntos, accesorios complementarios, estilismos,
ocasiones de uso ni otros productos. Evita repeticiones, clichés, superlativos absolutos y frases
como "el mejor", "garantizado" o "lujoso". Precisión e información útil tienen prioridad sobre tono
comercial y longitud.

Escribe pt_br, es y en de manera natural e idiomática para cada público; no hagas traducciones
literales. Las tres versiones deben conservar exactamente los mismos hechos, sin añadir datos en
un idioma que no aparezcan en los demás. No menciones la IA en el contenido comercial.
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
                'reasoning' => ['effort' => (string) config('services.openai.catalog_reasoning_effort', 'medium')],
                'tools' => [[
                    'type' => 'web_search',
                    'search_context_size' => (string) config('services.openai.catalog_search_context_size', 'high'),
                ]],
                'tool_choice' => 'required',
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

        $sources = $this->extractSources(
            $payload,
            $manufacturerReferences,
            (array) data_get($proposal, 'web_research.selected_source_urls', []),
        );
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

    private function normalizeSearchText(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = preg_replace('/[#*]+[^\s]*/u', ' ', $value) ?? $value;
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value) ?: null;
    }

    private function extractSources(array $payload, array $referenceCandidates = [], array $selectedUrls = []): array
    {
        $selectedUrlOrder = [];
        foreach ($selectedUrls as $index => $selectedUrl) {
            if (is_string($selectedUrl) && filter_var($selectedUrl, FILTER_VALIDATE_URL)) {
                $selectedUrlOrder[$selectedUrl] = $index;
            }
        }

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
                $sources[$url] ??= [
                    'title' => (string) data_get($source, 'title', $url),
                    'url' => $url,
                    'has_reference' => $this->urlContainsReference($url, $referenceCandidates),
                    'selected_order' => $selectedUrlOrder[$url] ?? null,
                ];
            }
        }

        $sources = array_values($sources);
        usort($sources, function (array $left, array $right) {
            $leftSelected = $left['selected_order'] !== null;
            $rightSelected = $right['selected_order'] !== null;
            if ($leftSelected !== $rightSelected) {
                return $rightSelected <=> $leftSelected;
            }
            if ($leftSelected && $left['selected_order'] !== $right['selected_order']) {
                return $left['selected_order'] <=> $right['selected_order'];
            }

            return (int) $right['has_reference'] <=> (int) $left['has_reference'];
        });

        return array_map(function (array $source) {
            unset($source['has_reference']);
            unset($source['selected_order']);

            return $source;
        }, array_slice($sources, 0, 3));
    }

    private function urlContainsReference(string $url, array $referenceCandidates): bool
    {
        $decodedUrl = rawurldecode($url);
        $normalizedUrl = preg_replace('/[^a-z0-9]+/', '', mb_strtolower($decodedUrl, 'UTF-8'));

        if (! is_string($normalizedUrl)) {
            return false;
        }

        foreach ($referenceCandidates as $referenceCandidate) {
            $normalizedReference = preg_replace(
                '/[^a-z0-9]+/',
                '',
                mb_strtolower(trim((string) $referenceCandidate), 'UTF-8'),
            );
            if (is_string($normalizedReference)
                && mb_strlen($normalizedReference) >= 4
                && str_contains($normalizedUrl, $normalizedReference)) {
                return true;
            }
        }

        return false;
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
                'web_research' => ['type' => 'object', 'properties' => ['status' => ['type' => 'string', 'enum' => ['matched', 'not_found']], 'findings' => ['type' => 'array', 'items' => ['type' => 'string']], 'selected_source_urls' => ['type' => 'array', 'items' => ['type' => 'string'], 'maxItems' => 3]], 'required' => ['status', 'findings', 'selected_source_urls'], 'additionalProperties' => false],
            ],
            'required' => ['editorial_summary', 'commercial_name', 'descriptions', 'verified_attributes', 'suggested_attributes', 'seo', 'taxonomy_selection', 'missing_data', 'warnings', 'confidence', 'web_research'],
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
