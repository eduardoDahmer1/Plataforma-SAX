<?php

namespace Tests\Unit;

use App\Services\BlogAiService;
use App\Services\ProductAiSettingsService;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class BlogAiServiceTest extends TestCase
{
    public function test_it_requires_web_search_uses_schema_and_sanitizes_generated_html(): void
    {
        config(['services.openai.api_key' => 'test-key']);
        $settings = Mockery::mock(ProductAiSettingsService::class);
        $settings->shouldReceive('ensureAvailable')->once();
        $settings->shouldReceive('apiKey')->once()->andReturn('test-key');
        $this->app->instance(ProductAiSettingsService::class, $settings);

        $article = [
            'title' => 'Título editorial',
            'subtitle' => 'Subtítulo editorial',
            'meta_description' => str_repeat('a', 170),
            'content_html' => '<h2 onclick="alert(1)">Sección</h2><p>Texto <a href="javascript:alert(1)" style="color:red">peligroso</a>.</p><script>alert(1)</script><img src=x onerror=alert(1)>',
            'read_time' => 5,
            'author' => 'SAX',
            'sources' => [
                ['title' => 'Fuente oficial', 'url' => 'https://example.com/official', 'reason' => 'Información oficial.'],
                ['title' => 'Fuente inventada', 'url' => 'https://invalid.example/fake', 'reason' => 'No fue consultada.'],
            ],
            'image_suggestions' => [[
                'description' => 'Imagen del producto',
                'search_query' => 'producto sitio oficial',
                'source_url' => 'https://example.com/official',
            ]],
        ];

        Http::fake(function ($request) use ($article) {
            $payload = $request->data();
            $this->assertSame('required', $payload['tool_choice']);
            $this->assertSame('web_search', $payload['tools'][0]['type']);
            $this->assertSame('json_schema', $payload['text']['format']['type']);
            $this->assertTrue($payload['text']['format']['strict']);

            return Http::response([
                'status' => 'completed',
                'output' => [
                    ['type' => 'web_search_call', 'action' => ['sources' => [
                        ['title' => 'Fuente oficial', 'url' => 'https://example.com/official'],
                    ]]],
                    ['type' => 'message', 'content' => [[
                        'type' => 'output_text', 'text' => json_encode($article),
                    ]]],
                ],
            ]);
        });

        $result = app(BlogAiService::class)->generate(['topic' => 'Tema']);

        $this->assertCount(1, $result['sources']);
        $this->assertSame('https://example.com/official', $result['sources'][0]['url']);
        $this->assertSame(160, mb_strlen($result['meta_description']));
        $this->assertStringNotContainsString('script', strtolower($result['content_html']));
        $this->assertStringNotContainsString('onclick', strtolower($result['content_html']));
        $this->assertStringNotContainsString('javascript:', strtolower($result['content_html']));
        $this->assertStringNotContainsString('<img', strtolower($result['content_html']));
    }
}
