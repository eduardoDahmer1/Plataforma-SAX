<?php

namespace Tests\Unit;

use App\Services\Dhl\DhlRateService;
use App\Services\Dhl\MyDhlApiClient;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Facade;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class DhlRateServiceTest extends TestCase
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
                'currency' => 'USD',
                'unit_of_measurement' => 'metric',
                'excluded_country_codes' => ['PY'],
                'origin' => [
                    'country_code' => 'PY',
                    'postal_code' => '7000',
                    'city_name' => 'Ciudad del Este',
                    'province_code' => '10',
                    'district_name' => 'Centro',
                    'address_line_1' => 'Origin Street 1',
                    'address_line_2' => '',
                ],
            ]],
            'app' => ['timezone' => 'America/Asuncion'],
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

    public function test_it_builds_the_official_multi_piece_rate_shape(): void
    {
        Http::fake([
            'express.api.dhl.com/*' => Http::response(['products' => [['productCode' => 'P']]]),
        ]);

        $result = $this->service()->quote([
            'country_code' => 'US',
            'postal_code' => '10001',
            'city_name' => 'New York',
            'address_line_1' => '350 Fifth Avenue',
        ], [[
            'weight' => 1.25,
            'length' => 20,
            'width' => 15,
            'height' => 10,
            'type_code' => '2BX',
        ]], 150.50, '2026-08-18T10:00:00GMT-03:00');

        $this->assertSame('P', $result['products'][0]['productCode']);
        Http::assertSent(function ($request): bool {
            $body = $request->data();

            return data_get($body, 'customerDetails.shipperDetails.countryCode') === 'PY'
                && data_get($body, 'customerDetails.shipperDetails.cityName') === 'Ciudad del Este'
                && data_get($body, 'customerDetails.shipperDetails.provinceCode') === '10'
                && data_get($body, 'customerDetails.shipperDetails.countyName') === 'Centro'
                && data_get($body, 'customerDetails.receiverDetails.countryCode') === 'US'
                && data_get($body, 'accounts.0.number') === '123456789'
                && $body['plannedShippingDateAndTime'] === '2026-08-18T10:00:00GMT-03:00'
                && $body['unitOfMeasurement'] === 'metric'
                && $body['isCustomsDeclarable'] === true
                && data_get($body, 'monetaryAmount.0.currency') === 'USD'
                && data_get($body, 'packages.0.weight') === 1.25
                && data_get($body, 'packages.0.typeCode') === '2BX'
                && data_get($body, 'packages.0.dimensions.length') === 20.0
                && data_get($body, 'estimatedDeliveryDate.isRequested') === true;
        });
    }

    public function test_it_rejects_a_quote_without_physical_package_data(): void
    {
        Http::fake();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ao menos um volume');

        $this->service()->quote([
            'country_code' => 'US',
            'city_name' => 'New York',
        ], [], 100);
    }

    public function test_it_rejects_zero_or_negative_package_measurements(): void
    {
        Http::fake();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('maiores que zero');

        $this->service()->quote([
            'country_code' => 'US',
            'city_name' => 'New York',
        ], [[
            'weight' => 0,
            'length' => 10,
            'width' => 10,
            'height' => 10,
        ]], 100);
    }

    public function test_it_rejects_a_destination_excluded_by_the_store_rule(): void
    {
        Http::fake();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('não está disponível');

        $this->service()->quote([
            'country_code' => 'PY',
            'city_name' => 'Asunción',
        ], [[
            'weight' => 1,
            'length' => 10,
            'width' => 10,
            'height' => 10,
        ]], 100);
    }

    public function test_it_accepts_brazil_and_normalizes_a_formatted_cep(): void
    {
        Http::fake([
            'express.api.dhl.com/*' => Http::response(['products' => [['productCode' => 'P']]]),
        ]);

        $this->service()->quote([
            'country_code' => 'BR',
            'postal_code' => '01001-000',
            'city_name' => 'São Paulo',
            'province_code' => 'SP',
            'address_line_1' => 'Praça da Sé 1',
        ], [[
            'weight' => 1,
            'length' => 10,
            'width' => 10,
            'height' => 10,
        ]], 100);

        Http::assertSent(fn ($request): bool =>
            data_get($request->data(), 'customerDetails.receiverDetails.countryCode') === 'BR'
            && data_get($request->data(), 'customerDetails.receiverDetails.postalCode') === '01001000'
            && data_get($request->data(), 'customerDetails.receiverDetails.provinceCode') === 'SP'
        );
    }

    private function service(): DhlRateService
    {
        return new DhlRateService(new MyDhlApiClient());
    }
}
