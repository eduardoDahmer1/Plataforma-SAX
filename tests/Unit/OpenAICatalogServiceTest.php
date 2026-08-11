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
                            'short_description' => ['pt_br' => 'Uma frase.', 'es' => 'Una frase.', 'en' => 'One sentence.'],
                            'descriptions' => ['pt_br' => str_repeat('Descrição factual. ', 8), 'es' => str_repeat('Descripción factual. ', 8), 'en' => str_repeat('Factual description. ', 8)],
                            'verified_attributes' => ['brand' => 'Marca'],
                            'suggested_attributes' => [],
                            'seo' => ['title_pt_br' => 'MARCA PRODUTO', 'meta_description_pt_br' => 'Produto', 'search_terms' => ['produto']],
                            'taxonomy_selection' => ['subcategory_id' => 999, 'childcategory_id' => 999, 'reason' => 'Sem evidência', 'confidence' => 'low'],
                            'missing_data' => [],
                            'warnings' => [],
                            'confidence' => 'medium',
                            'web_research' => ['status' => 'matched', 'findings' => ['Coincidencia encontrada']],
                        ], JSON_UNESCAPED_UNICODE),
                    ]],
                ], [
                    'type' => 'web_search_call',
                    'action' => ['sources' => [
                        ['title' => 'Fuente oficial', 'url' => 'https://brand.example/product'],
                        ['title' => 'Fuente oficial duplicada', 'url' => 'https://brand.example/product'],
                        ['title' => 'No válida', 'url' => 'javascript:alert(1)'],
                    ]],
                ]],
                'usage' => ['input_tokens' => 10, 'output_tokens' => 20],
            ]),
        ]);

        $product = Product::make([
            'sku' => 'SKU-1',
            'external_name' => 'Marca Produto',
            'name' => 'Produto atual',
            'category_id' => null,
        ]);
        $product->setRelation('brand', null);
        $product->setRelation('category', null);
        $product->setRelation('subcategory', null);
        $product->setRelation('categoriasFilhas', null);

        $result = app(OpenAICatalogService::class)->generateProductProposal($product);

        $this->assertSame('MARCA PRODUTO', $result['proposal']['commercial_name']['pt_br']);
        $this->assertNull($result['proposal']['taxonomy_selection']['subcategory_id']);
        $this->assertSame('matched', $result['research']['status']);
        $this->assertSame([['title' => 'Fuente oficial', 'url' => 'https://brand.example/product']], $result['research']['sources']);
        $this->assertSame(['input_tokens' => 10, 'output_tokens' => 20], $result['usage']);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://api.openai.com/v1/responses'
                && $request->hasHeader('Authorization', 'Bearer test-key')
                && $body['model'] === 'gpt-5.6-luna'
                && $body['tools'][0]['type'] === 'web_search'
                && $body['text']['format']['type'] === 'json_schema'
                && $body['text']['format']['schema']['properties']['verified_attributes']['type'] === 'array'
                && $body['store'] === false;
        });
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
                        'short_description' => ['pt_br' => 'A', 'es' => 'A', 'en' => 'A'], 'descriptions' => ['pt_br' => 'A', 'es' => 'A', 'en' => 'A'],
                        'verified_attributes' => [], 'suggested_attributes' => [], 'seo' => ['title_pt_br' => '', 'meta_description_pt_br' => '', 'search_terms' => []],
                        'taxonomy_selection' => ['subcategory_id' => null, 'childcategory_id' => null, 'reason' => '', 'confidence' => 'low'],
                        'missing_data' => [], 'warnings' => [], 'confidence' => 'low', 'web_research' => ['status' => 'not_found', 'findings' => []],
                    ], JSON_UNESCAPED_UNICODE),
                ]],
            ]],
        ])]);

        $product = Product::make(['external_name' => 'Produto', 'category_id' => null]);
        $product->setRelation('brand', null)->setRelation('category', null)->setRelation('subcategory', null)->setRelation('categoriasFilhas', null);
        $result = app(OpenAICatalogService::class)->generateProductProposal($product);

        $this->assertSame('not_found', $result['research']['status']);
        $this->assertNotEmpty($result['research']['warning']);
        $this->assertSame([], $result['research']['sources']);
    }
}
