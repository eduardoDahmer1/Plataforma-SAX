<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BlogAiService
{
    private const ALLOWED_TAGS = ['p', 'h2', 'h3', 'strong', 'em', 'ul', 'ol', 'li', 'blockquote', 'a', 'br'];

    public function generate(array $input): array
    {
        $settings = app(ProductAiSettingsService::class);
        $settings->ensureAvailable();
        $apiKey = $settings->apiKey();
        if ($apiKey === '') {
            throw new RuntimeException('La API de OpenAI no está configurada.');
        }

        $instructions = <<<'PROMPT'
Eres el equipo editorial de SAX Department Store. Investiga obligatoriamente el tema mediante
web_search antes de escribir. Prioriza: (1) sitio oficial de la marca, (2) página oficial del
producto, (3) publicaciones especializadas reconocidas y (4) medios confiables. Evita páginas SEO
de baja calidad, contenido duplicado, tiendas desconocidas y afirmaciones sin respaldo.

Redacta en español natural un artículo editorial útil, sobrio y profesional para lectores
interesados en moda, lujo, belleza, perfumes y productos comercializados por SAX. No exageres,
no inventes información, no menciones el proceso de IA y no conviertas el artículo en publicidad.
Cuando un hecho no pueda verificarse, omítelo. El título, subtítulo y meta description deben ser
claros y específicos. La meta description debe tener como máximo 160 caracteres. Calcula un tiempo
de lectura realista entre 2 y 15 minutos y usa "SAX" como autor.

content_html debe contener solo HTML editorial limpio usando estas etiquetas: p, h2, h3, strong,
em, ul, ol, li, blockquote, a y br. No uses h1, estilos inline, clases, imágenes, scripts, iframes,
objetos, embeds ni atributos de eventos. Los enlaces deben ser http/https. No incluyas las fuentes
dentro de content_html: devuélvelas en sources.

Devuelve entre 2 y 5 fuentes realmente consultadas. Cada URL debe corresponder a una fuente usada
y reason debe explicar brevemente qué información respaldó. Devuelve también entre 1 y 3
sugerencias de imagen, sin descargar ni copiar ninguna imagen. source_url debe apuntar, cuando sea
posible, a una página oficial donde el editor pueda revisar material visual.
PROMPT;

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.openai.timeout', 90))
            ->post(rtrim((string) config('services.openai.base_url'), '/').'/responses', [
                'model' => (string) config('services.openai.model'),
                'instructions' => $instructions,
                'input' => json_encode([
                    'topic' => trim((string) $input['topic']),
                    'product_or_focus' => trim((string) ($input['focus'] ?? '')),
                    'additional_instructions' => trim((string) ($input['instructions'] ?? '')),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
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
                        'name' => 'experimental_blog_article',
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
                default => 'OpenAI no pudo generar el artículo (HTTP '.($status ?: 'desconocido').').',
            };
            throw new RuntimeException($message, previous: $exception);
        }

        $payload = $response->json();
        if (data_get($payload, 'status') === 'incomplete') {
            throw new RuntimeException('OpenAI devolvió una respuesta incompleta.');
        }

        $decoded = json_decode($this->extractOutputText($payload), true);
        if (! is_array($decoded)) {
            throw new RuntimeException('OpenAI devolvió un JSON con formato inesperado.');
        }

        $actualSources = $this->extractWebSources($payload);
        if ($actualSources === []) {
            throw new RuntimeException('OpenAI no devolvió fuentes de investigación web verificables.');
        }

        $sources = $this->verifiedSources((array) ($decoded['sources'] ?? []), $actualSources);
        if ($sources === []) {
            throw new RuntimeException('Las fuentes declaradas no coinciden con la investigación web realizada.');
        }

        $result = [
            'title' => trim((string) ($decoded['title'] ?? '')),
            'subtitle' => trim((string) ($decoded['subtitle'] ?? '')),
            'meta_description' => trim((string) ($decoded['meta_description'] ?? '')),
            'content_html' => $this->sanitizeHtml((string) ($decoded['content_html'] ?? '')),
            'read_time' => max(2, min(15, (int) ($decoded['read_time'] ?? 5))),
            'author' => 'SAX',
            'sources' => $sources,
            'image_suggestions' => $this->normalizeImageSuggestions((array) ($decoded['image_suggestions'] ?? [])),
        ];

        if ($result['title'] === '' || $result['subtitle'] === '' || $result['content_html'] === '') {
            throw new RuntimeException('OpenAI no devolvió todos los campos editoriales obligatorios.');
        }
        if (mb_strlen($result['title']) > 255 || mb_strlen($result['subtitle']) > 2000) {
            throw new RuntimeException('OpenAI devolvió campos editoriales demasiado extensos.');
        }
        $result['meta_description'] = mb_substr($result['meta_description'], 0, 160);

        return $result;
    }

    public function contentWithSources(string $content, array $sources): string
    {
        $items = collect($sources)->map(function (array $source) {
            $title = e($source['title'] ?: $source['url']);
            $url = e($source['url']);
            $reason = e($source['reason'] ?? '');

            return '<li><a href="'.$url.'" target="_blank" rel="noopener noreferrer">'.$title.'</a>'.
                ($reason !== '' ? ' — '.$reason : '').'</li>';
        })->implode('');

        return $content.'<h2>Fuentes consultadas</h2><p>Referencias utilizadas durante la investigación editorial:</p><ul>'.$items.'</ul>';
    }

    private function sanitizeHtml(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="UTF-8"><div id="sax-blog-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $root = $dom->getElementById('sax-blog-root');
        if (! $root) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            return '';
        }

        $this->sanitizeNode($root);
        $clean = '';
        foreach ($root->childNodes as $child) {
            $clean .= $dom->saveHTML($child);
        }
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return trim($clean);
    }

    private function sanitizeNode(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
                    $child->parentNode?->removeChild($child);
                    continue;
                }
                $this->sanitizeNode($child);
                while ($child->firstChild) {
                    $child->parentNode?->insertBefore($child->firstChild, $child);
                }
                $child->parentNode?->removeChild($child);
                continue;
            }

            $href = $tag === 'a' ? trim($child->getAttribute('href')) : '';
            while ($child->attributes->length > 0) {
                $child->removeAttributeNode($child->attributes->item(0));
            }
            if ($tag === 'a' && $this->isSafeUrl($href)) {
                $child->setAttribute('href', $href);
                $child->setAttribute('target', '_blank');
                $child->setAttribute('rel', 'noopener noreferrer');
            }
            $this->sanitizeNode($child);
        }
    }

    private function verifiedSources(array $declared, array $actual): array
    {
        $declaredByUrl = collect($declared)->filter(fn ($source) => is_array($source) && $this->isSafeUrl((string) ($source['url'] ?? '')))
            ->keyBy(fn ($source) => (string) $source['url']);

        return collect($actual)->map(function (array $source) use ($declaredByUrl) {
            $declaredSource = $declaredByUrl->get($source['url'], []);
            return [
                'title' => trim((string) ($declaredSource['title'] ?? $source['title'] ?? $source['url'])),
                'url' => $source['url'],
                'reason' => trim((string) ($declaredSource['reason'] ?? 'Fuente consultada durante la investigación.')),
            ];
        })->take(5)->values()->all();
    }

    private function normalizeImageSuggestions(array $suggestions): array
    {
        return collect($suggestions)->filter(fn ($item) => is_array($item))->map(function (array $item) {
            $sourceUrl = trim((string) ($item['source_url'] ?? ''));
            return [
                'description' => trim((string) ($item['description'] ?? '')),
                'search_query' => trim((string) ($item['search_query'] ?? '')),
                'source_url' => $this->isSafeUrl($sourceUrl) ? $sourceUrl : '',
            ];
        })->filter(fn ($item) => $item['description'] !== '' && $item['search_query'] !== '')->take(3)->values()->all();
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

    private function extractWebSources(array $payload): array
    {
        $sources = [];
        foreach ((array) data_get($payload, 'output', []) as $item) {
            if (data_get($item, 'type') !== 'web_search_call') {
                continue;
            }
            foreach ((array) data_get($item, 'action.sources', []) as $source) {
                $url = trim((string) data_get($source, 'url'));
                if ($this->isSafeUrl($url)) {
                    $sources[$url] = ['title' => trim((string) data_get($source, 'title', $url)), 'url' => $url];
                }
            }
        }
        return array_values($sources);
    }

    private function isSafeUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL)
            && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);
    }

    private function responseSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['title', 'subtitle', 'meta_description', 'content_html', 'read_time', 'author', 'sources', 'image_suggestions'],
            'properties' => [
                'title' => ['type' => 'string'],
                'subtitle' => ['type' => 'string'],
                'meta_description' => ['type' => 'string'],
                'content_html' => ['type' => 'string'],
                'read_time' => ['type' => 'integer', 'minimum' => 2, 'maximum' => 15],
                'author' => ['type' => 'string'],
                'sources' => [
                    'type' => 'array', 'minItems' => 2, 'maxItems' => 5,
                    'items' => [
                        'type' => 'object', 'additionalProperties' => false,
                        'required' => ['title', 'url', 'reason'],
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'url' => ['type' => 'string'],
                            'reason' => ['type' => 'string'],
                        ],
                    ],
                ],
                'image_suggestions' => [
                    'type' => 'array', 'minItems' => 1, 'maxItems' => 3,
                    'items' => [
                        'type' => 'object', 'additionalProperties' => false,
                        'required' => ['description', 'search_query', 'source_url'],
                        'properties' => [
                            'description' => ['type' => 'string'],
                            'search_query' => ['type' => 'string'],
                            'source_url' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
