<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Dhl\DhlSettingsService;
use App\Services\Dhl\DhlShipmentService;
use App\Services\Dhl\MyDhlApiClient;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

final class DhlShipmentServiceTest extends TestCase
{
    public function test_it_builds_the_customs_shipment_pickup_and_document_payload(): void
    {
        config()->set('services.dhl', [
            'enabled' => true,
            'environment' => 'sandbox',
            'base_url' => 'https://express.api.dhl.com/mydhlapi/test',
            'api_key' => 'sandbox-key',
            'api_secret' => 'sandbox-secret',
            'account_number' => '123456789',
            'currency' => 'USD',
            'incoterm' => 'DAP',
            'origin' => [
                'country_code' => 'PY',
                'postal_code' => '7000',
                'city_name' => 'Ciudad del Este',
                'province_code' => '10',
                'district_name' => 'Centro',
                'address_line_1' => 'Origin Street 1',
                'company_name' => 'SAX',
                'contact_name' => 'SAX Logistics',
                'phone' => '+595999000111',
                'email' => 'logistics@example.test',
            ],
        ]);

        $settings = new DhlSettingsService;
        $service = new DhlShipmentService(new MyDhlApiClient($settings), $settings);
        $order = new Order([
            'order_number' => 'SAX-100',
            'shipping_currency' => 'USD',
            'shipping_service_code' => 'P',
            'shipping_packages' => [[
                'name' => 'DHL Box', 'weight' => 1.2, 'billable_weight' => 1.2,
                'length' => 30, 'width' => 20, 'height' => 10,
            ]],
            'discount' => 10,
            'name' => 'Jane Customer',
            'email' => 'jane@example.test',
            'phone_country' => '1',
            'phone' => '2125551000',
            'street' => '350 Fifth Avenue',
            'number' => '1',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'US',
            'cep' => '10001',
        ]);
        $product = new Product([
            'shipping_hs_code' => '4202.21',
            'shipping_country_of_origin' => 'IT',
            'shipping_customs_description' => 'Leather handbag',
        ]);
        $item = new OrderItem([
            'name' => 'Handbag',
            'quantity' => 2,
            'price' => 100,
            'shipping_weight_kg' => 0.5,
            'shipping_hs_code' => '4202.21',
            'shipping_country_of_origin' => 'IT',
            'shipping_customs_description' => 'Leather handbag',
        ]);
        $item->setRelation('product', $product);
        $order->setRelation('items', new Collection([$item]));

        $payload = $service->buildPayload($order, [
            'planned_at' => Carbon::parse('2026-11-27 10:00', 'America/Asuncion'),
            'product_code' => 'P',
            'request_pickup' => true,
            'pickup_close_time' => '16:00',
            'pickup_location' => 'Recepção SAX',
        ]);

        $this->assertSame('2026-11-27T10:00:00 GMT-03:00', $payload['plannedShippingDateAndTime']);
        $this->assertSame('123456789', data_get($payload, 'accounts.0.number'));
        $this->assertTrue(data_get($payload, 'pickup.isRequested'));
        $this->assertSame('4202.21', data_get($payload, 'content.exportDeclaration.lineItems.0.commodityCodes.0.value'));
        $this->assertSame('IT', data_get($payload, 'content.exportDeclaration.lineItems.0.manufacturerCountry'));
        $this->assertSame(95.0, data_get($payload, 'content.exportDeclaration.lineItems.0.price'));
        $this->assertTrue($payload['requestOndemandDeliveryURL']);
        $this->assertSame('invoice', data_get($payload, 'outputImageProperties.imageOptions.2.typeCode'));
        $this->assertSame('shipmentReceipt', data_get($payload, 'outputImageProperties.imageOptions.3.typeCode'));
    }
}
