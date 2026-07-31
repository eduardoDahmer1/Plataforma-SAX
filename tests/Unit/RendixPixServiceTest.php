<?php

namespace Tests\Unit;

use App\Services\RendixPixService;
use PHPUnit\Framework\TestCase;

class RendixPixServiceTest extends TestCase
{
    public function test_maps_rendix_sale_statuses_to_local_statuses(): void
    {
        $service = new RendixPixService('https://sandbox.example', 'email@example.com', 'secret', '20');

        $this->assertSame('created', $service->localStatus('1'));
        $this->assertSame('pending', $service->localStatus('2'));
        $this->assertSame('paid', $service->localStatus('3'));
        $this->assertSame('expired', $service->localStatus('6'));
        $this->assertSame('failed', $service->localStatus('8'));
        $this->assertSame('failed', $service->localStatus('9'));
        $this->assertSame('failed', $service->localStatus('11'));
        $this->assertSame('refunded', $service->localStatus('12'));
    }

    public function test_extracts_sale_id_from_common_webhook_shapes(): void
    {
        $service = new RendixPixService('https://sandbox.example', 'email@example.com', 'secret', '20');

        $this->assertSame('123', $service->extractWebhookSaleId(['saleId' => 123]));
        $this->assertSame('456', $service->extractWebhookSaleId(['data' => ['saleId' => '456']]));
        $this->assertNull($service->extractWebhookSaleId(['status' => 3]));
        $this->assertSame(
            'SAX-PIX-10-ABC',
            $service->extractWebhookControlNumber(['data' => ['controlNumber' => 'SAX-PIX-10-ABC']]),
        );
    }

    public function test_normalizes_the_sale_shape_returned_by_the_sandbox_api(): void
    {
        $service = new RendixPixService('https://sandbox.example', 'email@example.com', 'secret', '20');

        $sale = $service->extractSaleData([
            'data' => [
                'saleId' => 94954,
                'pixCopyPast' => 'pix-payload',
                'qrCodeBase64' => 'qr-base64',
                'qrCodeExpiration' => '2026-07-30T16:32:36.825+00:00',
            ],
        ]);

        $this->assertSame(94954, $sale['saleId']);
        $this->assertSame('pix-payload', $sale['pixCopyPaste']);
        $this->assertSame('qr-base64', $sale['qrCodeBase64']);
        $this->assertSame('2026-07-30T16:32:36.825+00:00', $sale['qrCodeExpiration']);
    }
}
