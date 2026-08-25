<?php

namespace Tests\Unit;

use App\Exceptions\DhlApiException;
use App\Services\Dhl\MyDhlApiClient;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class MyDhlApiClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();
        Container::setInstance($container);
        Facade::setFacadeApplication($container);
        $container->instance('config', new Repository([
            'services' => ['dhl' => [
                'enabled' => true,
                'environment' => 'sandbox',
                'base_url' => 'https://express.api.dhl.com/mydhlapi/test',
                'api_key' => 'sandbox-key',
                'api_secret' => 'sandbox-secret',
                'account_number' => '123456789',
                'timeout' => 20,
                'retry_times' => 1,
            ]],
        ]));
        $container->instance(Factory::class, new Factory());
        $container->instance('log', new NullLogger());
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);

        parent::tearDown();
    }

    public function test_it_authenticates_and_sends_a_rate_request_to_the_sandbox(): void
    {
        Http::fake([
            'express.api.dhl.com/*' => Http::response(['products' => []]),
        ]);

        $result = (new MyDhlApiClient())->rates(['packages' => []]);

        $this->assertSame(['products' => []], $result);
        Http::assertSent(function ($request): bool {
            return $request->method() === 'POST'
                && $request->url() === 'https://express.api.dhl.com/mydhlapi/test/rates'
                && $request->hasHeader('Authorization', 'Basic '.base64_encode('sandbox-key:sandbox-secret'))
                && $request->hasHeader('Message-Reference')
                && $request['packages'] === [];
        });
    }

    public function test_it_refuses_requests_when_configuration_is_incomplete(): void
    {
        config()->set('services.dhl.api_secret', null);
        Http::fake();

        try {
            (new MyDhlApiClient())->rates([]);
            $this->fail('A configuração incompleta deveria impedir a chamada.');
        } catch (DhlApiException $exception) {
            $this->assertContains('DHL_API_SECRET', $exception->details()['missing']);
            $this->assertNull($exception->httpStatus());
        }

        Http::assertNothingSent();
    }

    public function test_it_exposes_a_safe_api_error_without_credentials(): void
    {
        Http::fake([
            'express.api.dhl.com/*' => Http::response([
                'title' => 'Bad Request',
                'detail' => 'The shipment data is invalid.',
                'additionalDetails' => ['Postal code is required.'],
            ], 400),
        ]);

        try {
            (new MyDhlApiClient())->rates([]);
            $this->fail('A resposta inválida deveria lançar uma exceção.');
        } catch (DhlApiException $exception) {
            $this->assertSame(400, $exception->httpStatus());
            $this->assertSame('The shipment data is invalid.', $exception->getMessage());
            $this->assertSame(['Postal code is required.'], $exception->details()['additional_details']);
            $this->assertStringNotContainsString('sandbox-secret', json_encode($exception->details()));
        }
    }

    public function test_configuration_summary_masks_the_account_number(): void
    {
        $summary = (new MyDhlApiClient())->configurationSummary();

        $this->assertSame('*****6789', $summary['account']);
        $this->assertTrue($summary['credentials_present']);
        $this->assertArrayNotHasKey('api_key', $summary);
        $this->assertArrayNotHasKey('api_secret', $summary);
    }

    public function test_it_translates_the_dhl_postal_format_error(): void
    {
        Http::fake([
            'express.api.dhl.com/*' => Http::response([
                'detail' => '420506: Invalid Postcode Format. Valid formats:AU - 9999(4)',
            ], 400),
        ]);

        try {
            (new MyDhlApiClient())->rates([]);
            $this->fail('O código postal inválido deveria lançar uma exceção.');
        } catch (DhlApiException $exception) {
            $this->assertSame('Código postal inválido para AU. A DHL exige exatamente 4 dígitos.', $exception->getMessage());
        }
    }

    public function test_it_prevents_a_production_url_when_environment_is_sandbox(): void
    {
        config()->set('services.dhl.base_url', 'https://express.api.dhl.com/mydhlapi');
        Http::fake();

        $client = new MyDhlApiClient();

        $this->assertFalse($client->isConfigured());
        $this->assertContains('DHL_BASE_URL/DHL_ENVIRONMENT', $client->missingConfiguration());
        Http::assertNothingSent();
    }

    public function test_it_handles_an_html_gateway_error_without_exposing_json_parser_details(): void
    {
        Http::fake([
            'express.api.dhl.com/*' => Http::response('<!DOCTYPE html><html><body>Bad gateway</body></html>', 502, [
                'Content-Type' => 'text/html',
            ]),
        ]);

        try {
            (new MyDhlApiClient())->rates([]);
            $this->fail('A resposta HTML deveria lançar uma exceção segura.');
        } catch (DhlApiException $exception) {
            $this->assertSame(502, $exception->httpStatus());
            $this->assertSame('O ambiente da DHL está temporariamente indisponível. Tente novamente em alguns instantes.', $exception->getMessage());
            $this->assertStringNotContainsString('DOCTYPE', $exception->getMessage());
        }
    }

    public function test_it_handles_a_success_status_with_a_non_json_body(): void
    {
        Http::fake([
            'express.api.dhl.com/*' => Http::response('<html>Unexpected response</html>', 200, [
                'Content-Type' => 'text/html',
            ]),
        ]);

        $this->expectException(DhlApiException::class);
        $this->expectExceptionMessage('A DHL devolveu uma resposta inesperada');

        (new MyDhlApiClient())->rates([]);
    }
}
