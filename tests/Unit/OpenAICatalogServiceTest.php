<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\OpenAICatalogService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAICatalogServiceTest extends TestCase
{
    public function test_it_generates_a_normalized_proposal_and_returns_web_sources(): void
    {
        config()->set('services.openai.api_key', 'test-key');
        config()->set('services.openai.base_url', 'https://api.openai.com/v1');
        config()->set('services.openai.model', 'gpt-5.6-luna');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'status' => 'completed',
                'output' => [[
                    'type' => 'message',
                    'content' => [[
                        'type' => 'output_text',
                        'text' => json_encode([
                            'editorial_summary' => 'Resumen',
                            'commercial_name' => ['pt_br' => 'marca produto', 'es' => 'marca producto', 'en' => 'brand product'],
                            'descriptions' => ['pt_br' => str_repeat('Descrição factual. ', 8), 'es' => str_repeat('Descripción factual. ', 8), 'en' => str_repeat('Factual description. ', 8)],
                            'verified_attributes' => ['brand' => 'Marca'],
                            'suggested_attributes' => [],
                            'seo' => ['title_pt_br' => 'MARCA PRODUTO', 'meta_description_pt_br' => 'Produto', 'search_terms' => ['produto']],
                            'taxonomy_selection' => ['category_id' => 999, 'subcategory_id' => 999, 'childcategory_id' => 999, 'reason' => 'Sem evidência', 'confidence' => 'low'],
                            'missing_data' => [],
                            'warnings' => [],
                            'confidence' => 'medium',
                            'web_research' => [
                                'status' => 'matched',
                                'findings' => ['Coincidencia encontrada'],
                                'selected_source_urls' => [
                                    'https://brand.example/products/ref-123',
                                    'https://retailer.example/product',
                                    'https://distributor.example/item',
                                ],
                            ],
                        ], JSON_UNESCAPED_UNICODE),
                    ]],
                ], [
                    'type' => 'web_search_call',
                    'action' => ['sources' => [
                        ['title' => 'Fuente comercial', 'url' => 'https://retailer.example/product'],
                        ['title' => 'Fuente oficial', 'url' => 'https://brand.example/products/ref-123'],
                        ['title' => 'Fuente oficial duplicada', 'url' => 'https://brand.example/products/ref-123'],
                        ['title' => 'Distribuidor', 'url' => 'https://distributor.example/item'],
                        ['title' => 'Cuarta fuente', 'url' => 'https://fourth.example/item'],
                        ['title' => 'No válida', 'url' => 'javascript:alert(1)'],
                    ]],
                ]],
                'usage' => ['input_tokens' => 10, 'output_tokens' => 20],
            ]),
        ]);

        $product = Product::make([
            'sku' => 'SKU-1',
            'external_name' => 'Marca Produto REF-123 #U *BL1',
            'name' => 'Produto atual',
            'ref_code' => 'REF-123',
            'category_id' => null,
        ]);
        $product->setRelation('brand', null);
        $product->setRelation('category', null);
        $product->setRelation('subcategory', null);
        $product->setRelation('categoriasFilhas', null);

        $result = $this->serviceWithTaxonomy()->generateProductProposal($product);

        $this->assertSame('MARCA PRODUTO', $result['proposal']['commercial_name']['pt_br']);
        $this->assertSame(str_repeat('Descripción factual. ', 8), $result['proposal']['descriptions']['es']);
        $this->assertNull($result['proposal']['taxonomy_selection']['category_id']);
        $this->assertNull($result['proposal']['taxonomy_selection']['subcategory_id']);
        $this->assertSame('matched', $result['research']['status']);
        $this->assertSame([
            ['title' => 'Fuente oficial', 'url' => 'https://brand.example/products/ref-123'],
            ['title' => 'Fuente comercial', 'url' => 'https://retailer.example/product'],
            ['title' => 'Distribuidor', 'url' => 'https://distributor.example/item'],
        ], $result['research']['sources']);
        $this->assertSame(['input_tokens' => 10, 'output_tokens' => 20], $result['usage']);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://api.openai.com/v1/responses'
                && $request->hasHeader('Authorization', 'Bearer test-key')
                && $body['model'] === 'gpt-5.6-luna'
                && $body['tools'][0]['type'] === 'web_search'
                && $body['tools'][0]['search_context_size'] === 'high'
                && $body['tool_choice'] === 'required'
                && $body['reasoning']['effort'] === 'medium'
                && $body['text']['format']['type'] === 'json_schema'
                && $body['text']['format']['schema']['properties']['verified_attributes']['type'] === 'array'
                && in_array('category_id', $body['text']['format']['schema']['properties']['taxonomy_selection']['required'], true)
                && ! isset($body['text']['format']['schema']['properties']['short_description'])
                && $body['store'] === false;
        });

        $recordedRequest = Http::recorded()[0][0];
        $requestBody = $recordedRequest->data();
        $knownData = json_decode(str($requestBody['input'])->after("\n")->toString(), true);

        $this->assertStringContainsString('80 a 160 palabras como referencia editorial', $requestBody['instructions']);
        $this->assertStringContainsString('60 a 100 palabras es correcta', $requestBody['instructions']);
        $this->assertStringContainsString('cada párrafo debe aportar información', $requestBody['instructions']);
        $this->assertStringContainsString('otra variante del mismo modelo', $requestBody['instructions']);
        $this->assertStringContainsString('actitud urbana', $requestBody['instructions']);
        $this->assertStringContainsString('texto final que verá el cliente', $requestBody['instructions']);
        $this->assertStringContainsString('para esta referencia', $requestBody['instructions']);
        $this->assertStringContainsString('URLs, enlaces, citas, referencias bibliográficas', $requestBody['instructions']);
        $this->assertStringContainsString('deben devolverse únicamente dentro de web_research', $requestBody['instructions']);
        $this->assertStringContainsString('nunca escribas una frase', $requestBody['instructions']);
        $this->assertStringContainsString('Solo los tres campos commercial_name', $requestBody['instructions']);
        $this->assertStringNotContainsString('entre 150 y 220 palabras', $requestBody['instructions']);
        $this->assertStringContainsString('No limites la investigación a tres páginas', $requestBody['instructions']);
        $this->assertStringContainsString('manufacturer_reference_candidates', $requestBody['instructions']);
        $this->assertStringNotContainsString('GTIN exacto', $requestBody['instructions']);
        $this->assertStringContainsString('No recomiendes con', $requestBody['instructions']);
        $this->assertStringContainsString('allowed_categories', $requestBody['instructions']);
        $this->assertSame(['REF-123'], $knownData['manufacturer_reference_candidates']);
        $this->assertSame(10, $knownData['taxonomy']['allowed_categories'][0]['id']);
        $this->assertSame(10, $knownData['taxonomy']['allowed_subcategories'][0]['category_id']);
        $this->assertArrayNotHasKey('gtin', $knownData);
        $this->assertArrayNotHasKey('mpn', $knownData);
    }

    public function test_it_continues_with_internal_data_when_web_research_finds_nothing(): void
    {
        config()->set('services.openai.api_key', 'test-key');
        Http::fake(['api.openai.com/*' => Http::response([
            'status' => 'completed',
            'output' => [[
                'type' => 'message',
                'content' => [[
                    'type' => 'output_text',
                    'text' => json_encode([
                        'editorial_summary' => '', 'commercial_name' => ['pt_br' => 'A', 'es' => 'A', 'en' => 'A'],
                        'descriptions' => ['pt_br' => 'A', 'es' => 'A', 'en' => 'A'],
                        'verified_attributes' => [], 'suggested_attributes' => [], 'seo' => ['title_pt_br' => '', 'meta_description_pt_br' => '', 'search_terms' => []],
                        'taxonomy_selection' => ['category_id' => null, 'subcategory_id' => null, 'childcategory_id' => null, 'reason' => '', 'confidence' => 'low'],
                        'missing_data' => [], 'warnings' => [], 'confidence' => 'low', 'web_research' => ['status' => 'not_found', 'findings' => [], 'selected_source_urls' => []],
                    ], JSON_UNESCAPED_UNICODE),
                ]],
            ]],
        ])]);

        $product = Product::make(['external_name' => 'Produto', 'category_id' => null]);
        $product->setRelation('brand', null)->setRelation('category', null)->setRelation('subcategory', null)->setRelation('categoriasFilhas', null);
        $result = $this->serviceWithTaxonomy()->generateProductProposal($product);

        $this->assertSame('not_found', $result['research']['status']);
        $this->assertNotEmpty($result['research']['warning']);
        $this->assertSame([], $result['research']['sources']);
    }

    private function serviceWithTaxonomy(): OpenAICatalogService
    {
        $taxonomy = $this->taxonomy();
        $service = \Mockery::mock(OpenAICatalogService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $service->shouldReceive('loadActiveTaxonomy')->andReturn($taxonomy);

        return $service;
    }

    private function taxonomy(): array
    {
        $category = new \App\Models\Category(['name' => 'Calçados', 'slug' => 'calcados']);
        $category->id = 10;
        $subcategory = new \App\Models\Subcategory(['name' => 'Tênis', 'slug' => 'tenis', 'category_id' => 10]);
        $subcategory->id = 20;
        $childCategory = new \App\Models\CategoriasFilhas([
            'name' => 'Tênis casuais',
            'slug' => 'tenis-casuais',
            'category_id' => 10,
            'subcategory_id' => 20,
        ]);
        $childCategory->id = 30;

        return [
            'categories' => collect([$category]),
            'subcategories' => collect([$subcategory]),
            'child_categories' => collect([$childCategory]),
        ];
    }
}
