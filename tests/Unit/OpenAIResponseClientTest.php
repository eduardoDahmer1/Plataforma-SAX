<?php

namespace Tests\Unit;

use App\Services\OpenAIResponseClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAIResponseClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.openai.base_url'=>'https://api.openai.com/v1', 'services.openai.max_attempts'=>3, 'services.openai.retry_max_output_tokens'=>30000]);
    }

    private function generate(): array
    {
        return app(OpenAIResponseClient::class)->create('test-key', ['model'=>'test-model', 'max_output_tokens'=>10000, 'store'=>false]);
    }

    public function test_token_exhaustion_recovers_with_a_larger_budget_without_returning_partial_text(): void
    {
        Http::fake(['api.openai.com/*'=>Http::sequence()
            ->push(['status'=>'incomplete', 'incomplete_details'=>['reason'=>'max_output_tokens'], 'output'=>['partial']])
            ->push(['status'=>'completed', 'output'=>['complete']])]);
        $this->assertSame(['complete'], $this->generate()['output']);
        $this->assertSame([10000, 20000], Http::recorded()->map(fn ($pair) => $pair[0]['max_output_tokens'])->all());
    }

    public function test_transient_server_error_recovers_and_permanent_quota_error_does_not_retry(): void
    {
        Http::fake(['api.openai.com/*'=>Http::sequence()->push([], 503)->push(['status'=>'completed'])]);
        $this->assertSame('completed', $this->generate()['status']);
        Http::assertSentCount(2);
    }

    public function test_quota_exhaustion_does_not_retry(): void
    {
        Http::fake(['api.openai.com/*'=>Http::response(['error'=>['code'=>'insufficient_quota']], 429)]);
        try { $this->generate(); $this->fail('Quota error must stop generation'); }
        catch (\RuntimeException $exception) { $this->assertStringContainsString('cuota', $exception->getMessage()); }
        Http::assertSentCount(1);
    }

    public function test_connection_failure_is_recovered_automatically(): void
    {
        $attempts = 0;
        Http::fake(function () use (&$attempts) {
            if (++$attempts === 1) throw new ConnectionException('temporary');
            return Http::response(['status'=>'completed']);
        });
        $this->assertSame('completed', $this->generate()['status']);
        $this->assertSame(2, $attempts);
    }

    public function test_repeated_incomplete_response_stops_at_the_attempt_and_token_limits(): void
    {
        Http::fake(['api.openai.com/*'=>Http::response(['status'=>'incomplete', 'incomplete_details'=>['reason'=>'max_output_tokens']])]);
        try { $this->generate(); $this->fail('Partial output must not be returned'); }
        catch (\RuntimeException $exception) { $this->assertStringContainsString('límite', $exception->getMessage()); }
        $this->assertSame([10000, 20000, 30000], Http::recorded()->map(fn ($pair) => $pair[0]['max_output_tokens'])->all());
    }
}
