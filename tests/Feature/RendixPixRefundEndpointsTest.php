<?php

namespace Tests\Feature;

use App\Services\RendixPixService;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Container\Container;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;

class RendixPixRefundEndpointsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();
        $container->instance(Factory::class, new Factory());
        $container->instance('cache', new Repository(new ArrayStore()));
        Facade::setFacadeApplication($container);

        Cache::flush();
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);

        parent::tearDown();
    }

    public function test_previews_and_confirms_a_full_refund_with_the_documented_payload(): void
    {
        Http::fake([
            'https://rendix.example/efx/v2/external/login' => Http::response([
                'success' => true,
                'data' => ['token' => 'token-test', 'expirationInMilliSeconds' => 3600000],
            ]),
            'https://rendix.example/efx/v1/external/sell/process-refund/94962*' => Http::response([
                'success' => true,
                'data' => ['saleId' => 94962, 'refundNationalCurrency' => 26.39],
            ]),
            'https://rendix.example/efx/v1/sell/cancel/94962' => Http::response([
                'success' => true,
                'data' => ['saleId' => 94962, 'status' => 12],
            ]),
        ]);

        $service = new RendixPixService(
            'https://rendix.example',
            'ecommerce@example.com',
            'secret',
            '5764',
            environment: 'production',
        );

        $this->assertTrue($service->previewRefund('94962', 4.80)['ok']);
        $this->assertTrue($service->refundSale('94962', 4.80)['ok']);

        Http::assertSent(function (Request $request): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return $request->method() === 'GET'
                && str_starts_with($request->url(), 'https://rendix.example/efx/v1/external/sell/process-refund/94962')
                && (float) ($query['refund'] ?? 0) === 4.80;
        });

        Http::assertSent(fn (Request $request): bool =>
            $request->method() === 'PATCH'
            && $request->url() === 'https://rendix.example/efx/v1/sell/cancel/94962'
            && (float) data_get($request->data(), 'refund.amount') === 4.80
        );
    }
}
