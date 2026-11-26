<?php

namespace App\Services\Dhl;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

final class DhlShipmentService
{
    public function __construct(
        private MyDhlApiClient $client,
        private DhlSettingsService $settings,
    ) {}

    /**
     * Cria uma única remessa MyDHL para o pedido. Esta operação não é repetida
     * automaticamente e deve permanecer atrás de uma ação explícita do admin.
     */
    public function create(Order $order, array $options): Order
    {
        $order->loadMissing(['items.product.translations']);
        $this->assertCanCreate($order);

        $plannedAt = $options['planned_at'];
        if (! $plannedAt instanceof CarbonInterface) {
            throw new InvalidArgumentException('Informe uma data e hora válidas para o envio.');
        }

        $payload = $this->buildPayload($order, $options);
        $response = $this->client->createShipment($payload);
        $trackingNumber = trim((string) ($response['shipmentTrackingNumber'] ?? ''));

        if ($trackingNumber === '') {
            throw new InvalidArgumentException('A DHL criou a solicitação, mas não devolveu o número AWB. Não tente novamente antes de consultar o MyDHL.');
        }

        // O AWB é salvo antes dos arquivos: mesmo que um PDF falhe, a remessa
        // externa nunca fica sem referência local e não será criada em dobro.
        $order->forceFill([
            'dhl_tracking_number' => $trackingNumber,
            'dhl_dispatch_confirmation_number' => $response['dispatchConfirmationNumber'] ?? null,
            // O sandbox pode devolver em trackingUrl o próprio endpoint técnico
            // da API (protegido por credenciais), que não deve ser exposto ao cliente.
            'dhl_tracking_url' => $this->publicTrackingUrl($trackingNumber),
            'dhl_odd_url' => $response['onDemandDeliveryURL'] ?? $response['onDemandDeliveryUrl'] ?? null,
            'dhl_shipment_status' => 'created',
            'dhl_shipment_environment' => (string) ($this->settings->resolved()['environment'] ?? 'sandbox'),
            'dhl_planned_shipping_at' => $plannedAt,
            'dhl_shipment_created_at' => now(),
            'dhl_warnings' => array_values((array) ($response['warnings'] ?? [])),
            'shipping_packages' => $this->attachPieceTracking(
                (array) $order->shipping_packages,
                (array) ($response['packages'] ?? []),
            ),
        ])->save();

        $documents = $this->storeDhlDocuments($order, (array) ($response['documents'] ?? []));
        $documents[] = $this->storePackingList($order);
        $order->forceFill(['dhl_documents' => array_values(array_filter($documents))])->save();

        return $order->fresh(['items.product']);
    }

    public function syncTracking(Order $order): Order
    {
        if (! $order->hasDhlShipment()) {
            throw new InvalidArgumentException('Este pedido ainda não possui uma guia DHL.');
        }

        $response = $this->client->trackShipment((string) $order->dhl_tracking_number);
        $shipment = collect((array) ($response['shipments'] ?? []))->first();

        if (! is_array($shipment)) {
            throw new InvalidArgumentException('A DHL ainda não devolveu eventos para esta guia.');
        }

        $events = $this->trackingEvents($shipment);
        $status = $this->normalizedStatus(
            (string) ($shipment['status'] ?? ''),
            (string) ($events[0]['type_code'] ?? ''),
        );

        $order->forceFill([
            'dhl_shipment_status' => $status,
            'dhl_tracking_events' => $events,
            'dhl_last_tracking_at' => now(),
        ])->save();

        return $order->fresh();
    }

    public function fetchProofOfDelivery(Order $order): Order
    {
        if (! $order->hasDhlShipment()) {
            throw new InvalidArgumentException('Este pedido ainda não possui uma guia DHL.');
        }

        $response = $this->client->proofOfDelivery((string) $order->dhl_tracking_number);
        $documents = (array) $order->dhl_documents;
        $stored = $this->storeDhlDocuments($order, (array) ($response['documents'] ?? []), count($documents));

        if ($stored === []) {
            throw new InvalidArgumentException('A DHL ainda não disponibilizou o comprovante de entrega para esta guia.');
        }

        $documents = collect($documents)
            ->reject(fn (mixed $document): bool => is_array($document) && str_starts_with((string) ($document['type'] ?? ''), 'epod'))
            ->concat($stored)
            ->values()
            ->all();

        $order->forceFill(['dhl_documents' => $documents])->save();

        return $order->fresh();
    }

    /** @return array<string, mixed> */
    public function buildPayload(Order $order, array $options): array
    {
        $config = $this->settings->resolved();
        $origin = (array) ($config['origin'] ?? []);
        $plannedAt = $options['planned_at'];
        $pickupRequested = (bool) ($options['request_pickup'] ?? false);
        $currency = strtoupper((string) ($order->shipping_currency ?: ($config['currency'] ?? 'USD')));
        $currency = preg_match('/^[A-Z]{3}$/', $currency) ? $currency : 'USD';
        $lines = $this->customsLines($order, (string) ($origin['country_code'] ?? 'PY'));
        $declaredValue = round(collect($lines)->sum(fn (array $line): float => (float) $line['price'] * (int) data_get($line, 'quantity.value', 1)), 2);
        $totalGrossWeight = round(collect($lines)->sum(fn (array $line): float => (float) data_get($line, 'weight.grossValue', 0)), 3);

        $pickup = ['isRequested' => false];
        if ($pickupRequested) {
            $pickup = [
                'isRequested' => true,
                'closeTime' => (string) ($options['pickup_close_time'] ?? ''),
                'location' => Str::limit(trim((string) ($options['pickup_location'] ?? 'Recepção SAX')), 80, ''),
                'pickupDetails' => [
                    'postalAddress' => $this->originPostalAddress($origin),
                    'contactInformation' => $this->originContact($origin),
                ],
            ];
        }

        $description = Str::limit(
            $order->items->pluck('name')->filter()->unique()->implode(', ') ?: 'Retail merchandise',
            70,
            '',
        );

        return [
            'plannedShippingDateAndTime' => $plannedAt->format('Y-m-d\TH:i:s \G\M\TP'),
            'accounts' => [[
                'typeCode' => 'shipper',
                'number' => $this->client->accountNumber(),
            ]],
            'customerDetails' => [
                'shipperDetails' => [
                    'postalAddress' => $this->originPostalAddress($origin),
                    'contactInformation' => $this->originContact($origin),
                    'typeCode' => 'business',
                ],
                'receiverDetails' => [
                    'postalAddress' => $this->receiverPostalAddress($order),
                    'contactInformation' => $this->receiverContact($order),
                    'typeCode' => 'private',
                ],
            ],
            'content' => [
                'packages' => collect((array) $order->shipping_packages)->map(fn (array $package): array => [
                    'weight' => round(max(0.001, (float) ($package['weight'] ?? $package['billable_weight'] ?? 0)), 3),
                    'dimensions' => [
                        'length' => round(max(1, (float) ($package['length'] ?? 0)), 1),
                        'width' => round(max(1, (float) ($package['width'] ?? 0)), 1),
                        'height' => round(max(1, (float) ($package['height'] ?? 0)), 1),
                    ],
                ])->values()->all(),
                'unitOfMeasurement' => 'metric',
                'isCustomsDeclarable' => true,
                'incoterm' => strtoupper((string) ($config['incoterm'] ?? 'DAP')),
                'description' => $description,
                'declaredValue' => max(0.01, $declaredValue),
                'declaredValueCurrency' => $currency,
                'exportDeclaration' => [
                    'lineItems' => $lines,
                    'invoice' => array_filter([
                        'number' => Str::limit((string) ($order->order_number ?: $order->getKey()), 35, ''),
                        'date' => now()->toDateString(),
                        'signatureName' => $origin['contact_name'] ?? null,
                        'signatureTitle' => 'Authorized representative',
                        'totalNetWeight' => $totalGrossWeight,
                        'totalGrossWeight' => $totalGrossWeight,
                    ], static fn (mixed $value): bool => $value !== null && $value !== ''),
                    'exportReasonType' => 'permanent',
                    'shipmentType' => 'commercial',
                    'placeOfIncoterm' => (string) ($order->city ?: $order->country),
                ],
            ],
            'getRateEstimates' => false,
            'productCode' => strtoupper(trim((string) ($options['product_code'] ?? $order->shipping_service_code ?? 'P'))),
            'pickup' => $pickup,
            'shipmentNotification' => [[
                'typeCode' => 'email',
                'languageCountryCode' => strtoupper((string) $order->country),
                'receiverId' => (string) $order->email,
            ]],
            'customerReferences' => [[
                'value' => Str::limit((string) ($order->order_number ?: $order->getKey()), 35, ''),
                'typeCode' => 'CU',
            ]],
            'requestOndemandDeliveryURL' => true,
            'outputImageProperties' => [
                'printerDPI' => 300,
                'encodingFormat' => 'pdf',
                'imageOptions' => [
                    ['typeCode' => 'label', 'isRequested' => true],
                    ['typeCode' => 'waybillDoc', 'isRequested' => true],
                    ['typeCode' => 'invoice', 'isRequested' => true, 'invoiceType' => 'commercial'],
                    ['typeCode' => 'shipmentReceipt', 'isRequested' => true],
                ],
            ],
        ];
    }

    private function assertCanCreate(Order $order): void
    {
        if ($order->shipping_provider !== 'dhl') {
            throw new InvalidArgumentException('Este pedido não usa DHL.');
        }
        if (! $order->isPaid()) {
            throw new InvalidArgumentException('Confirme o pagamento antes de gerar a guia DHL.');
        }
        if ($order->hasDhlShipment()) {
            throw new InvalidArgumentException('Este pedido já possui a guia DHL '.$order->dhl_tracking_number.'.');
        }
        if (empty($order->shipping_packages)) {
            throw new InvalidArgumentException('O pedido não possui o snapshot das embalagens usadas na cotação DHL.');
        }

        $config = $this->settings->resolved();
        $origin = (array) ($config['origin'] ?? []);
        foreach (['country_code', 'postal_code', 'city_name', 'address_line_1', 'company_name', 'contact_name', 'phone'] as $field) {
            if (blank($origin[$field] ?? null)) {
                throw new InvalidArgumentException("Complete o campo de origem DHL '{$field}' antes de gerar a guia.");
            }
        }

        foreach (['country', 'city', 'street', 'email', 'phone'] as $field) {
            if (blank($order->{$field})) {
                throw new InvalidArgumentException("O pedido não possui o dado obrigatório de destino '{$field}'.");
            }
        }
        if (strtoupper((string) $order->country) !== 'PY' && blank($order->cep)) {
            throw new InvalidArgumentException('O pedido não possui o código postal obrigatório do destino.');
        }

        foreach ($order->items as $item) {
            $product = $item->product;
            $dangerous = (bool) ($item->shipping_is_dangerous_goods ?: $product?->shipping_is_dangerous_goods);
            if ($dangerous) {
                throw new InvalidArgumentException("O item '{$item->name}' está marcado como perigoso/restrito. Gere a remessa somente após a DHL informar os códigos DG e de serviço aplicáveis à conta.");
            }

            if (blank($item->shipping_hs_code ?: $product?->shipping_hs_code)) {
                throw new InvalidArgumentException("Informe o código HS do item '{$item->name}' antes de gerar a guia.");
            }
            if (blank($item->shipping_customs_description ?: $product?->shipping_customs_description)) {
                throw new InvalidArgumentException("Informe a descrição aduaneira em inglês do item '{$item->name}' antes de gerar a guia.");
            }
        }
    }

    /** @return list<array<string, mixed>> */
    private function customsLines(Order $order, string $defaultOrigin): array
    {
        $subtotal = max(0.01, (float) $order->items->sum(fn ($item): float => (float) $item->price * (int) $item->quantity));
        $discountFactor = max(0.01, min(1, ($subtotal - (float) $order->discount) / $subtotal));

        return $order->items->values()->map(function ($item, int $index) use ($defaultOrigin, $discountFactor): array {
            $product = $item->product;
            $quantity = max(1, (int) $item->quantity);
            $unitWeight = max(0.001, (float) ($item->shipping_weight_kg ?: $product?->shipping_weight_kg));
            $origin = strtoupper((string) ($item->shipping_country_of_origin ?: $product?->shipping_country_of_origin ?: $defaultOrigin));
            $description = trim((string) ($item->shipping_customs_description ?: $product?->shipping_customs_description));
            $hsCode = preg_replace('/[^A-Za-z0-9.]/', '', (string) ($item->shipping_hs_code ?: $product?->shipping_hs_code));

            return [
                'number' => $index + 1,
                'description' => Str::limit($description, 512, ''),
                'price' => round(max(0.01, (float) $item->price * $discountFactor), 2),
                'quantity' => ['value' => $quantity, 'unitOfMeasurement' => 'PCS'],
                'manufacturerCountry' => preg_match('/^[A-Z]{2}$/', $origin) ? $origin : strtoupper($defaultOrigin),
                'weight' => [
                    'netValue' => round($unitWeight * $quantity, 3),
                    'grossValue' => round($unitWeight * $quantity, 3),
                ],
                'exportReasonType' => 'permanent',
                'isTaxesPaid' => false,
                'commodityCodes' => [['typeCode' => 'outbound', 'value' => $hsCode]],
            ];
        })->all();
    }

    private function originPostalAddress(array $origin): array
    {
        return array_filter([
            'addressLine1' => $origin['address_line_1'] ?? null,
            'addressLine2' => $origin['address_line_2'] ?? null,
            'countryCode' => strtoupper((string) ($origin['country_code'] ?? 'PY')),
            'postalCode' => $origin['postal_code'] ?? null,
            'cityName' => $origin['city_name'] ?? null,
            'countyName' => $origin['district_name'] ?? null,
            'provinceCode' => $origin['province_code'] ?? null,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    private function originContact(array $origin): array
    {
        return array_filter([
            'phone' => $this->phone((string) ($origin['phone'] ?? '')),
            'companyName' => $origin['company_name'] ?? 'SAX Department Store',
            'fullName' => $origin['contact_name'] ?? 'SAX Department Store',
            'email' => $origin['email'] ?? null,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    private function receiverPostalAddress(Order $order): array
    {
        return array_filter([
            'addressLine1' => trim((string) $order->street.' '.(string) $order->number),
            'addressLine2' => $order->complement,
            'countryCode' => strtoupper((string) $order->country),
            'postalCode' => $order->cep,
            'cityName' => $order->city,
            'countyName' => $order->district,
            'provinceCode' => $order->state,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    private function receiverContact(Order $order): array
    {
        $phone = preg_replace('/\D+/', '', (string) $order->phone) ?: '';
        $countryCode = preg_replace('/\D+/', '', (string) $order->phone_country) ?: '';
        if ($countryCode !== '' && ! str_starts_with($phone, $countryCode)) {
            $phone = $countryCode.$phone;
        }

        return array_filter([
            'phone' => $this->phone($phone),
            'companyName' => (string) $order->name,
            'fullName' => (string) $order->name,
            'email' => (string) $order->email,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    private function phone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        return $digits === '' ? '' : '+'.$digits;
    }

    /** @return list<array<string, mixed>> */
    private function storeDhlDocuments(Order $order, array $documents, int $offset = 0): array
    {
        $stored = [];
        foreach ($documents as $index => $document) {
            if (! is_array($document)) {
                continue;
            }
            $content = base64_decode((string) ($document['content'] ?? ''), true);
            if ($content === false || $content === '') {
                continue;
            }

            $type = Str::slug((string) ($document['typeCode'] ?? 'document')) ?: 'document';
            $format = strtolower((string) ($document['imageFormat'] ?? $document['encodingFormat'] ?? 'pdf'));
            $extension = in_array($format, ['pdf', 'zpl', 'epl', 'lp2', 'png'], true) ? $format : 'bin';
            $path = 'dhl/shipments/'.$order->getKey().'/'.$order->dhl_tracking_number.'/'.($offset + $index + 1).'-'.$type.'.'.$extension;

            if (! Storage::disk('local')->put($path, $content)) {
                continue;
            }

            $stored[] = [
                'type' => (string) ($document['typeCode'] ?? 'document'),
                'format' => strtoupper($extension),
                'path' => $path,
                'name' => $this->documentName((string) ($document['typeCode'] ?? 'document')),
            ];
        }

        return $stored;
    }

    private function storePackingList(Order $order): ?array
    {
        $path = 'dhl/shipments/'.$order->getKey().'/'.$order->dhl_tracking_number.'/packing-list.pdf';
        $origin = (array) ($this->settings->resolved()['origin'] ?? []);
        $pdf = Pdf::loadView('admin.orders.dhl-packing-list', [
            'order' => $order->loadMissing('items.product'),
            'origin' => $origin,
        ]);
        if (! Storage::disk('local')->put($path, $pdf->output())) {
            return null;
        }

        return ['type' => 'packingList', 'format' => 'PDF', 'path' => $path, 'name' => 'Packing List'];
    }

    private function documentName(string $type): string
    {
        return match ($type) {
            'label' => 'Etiqueta DHL',
            'waybillDoc' => 'Guia / Waybill',
            'invoice' => 'Fatura comercial',
            'shipmentReceipt' => 'Comprovante da remessa',
            'epod-detail', 'epod-detail-esig', 'epod-summary', 'epod-table' => 'Comprovante de entrega (POD)',
            default => 'Documento DHL',
        };
    }

    private function attachPieceTracking(array $packages, array $responsePackages): array
    {
        return collect($packages)->values()->map(function (array $package, int $index) use ($responsePackages): array {
            $responsePackage = is_array($responsePackages[$index] ?? null) ? $responsePackages[$index] : [];
            $package['tracking_number'] = $responsePackage['trackingNumber']
                ?? $responsePackage['shipmentTrackingNumber']
                ?? null;

            return $package;
        })->all();
    }

    /** @return list<array<string, mixed>> */
    private function trackingEvents(array $shipment): array
    {
        $events = collect((array) ($shipment['events'] ?? []));
        foreach ((array) ($shipment['pieces'] ?? []) as $piece) {
            if (is_array($piece)) {
                $events = $events->concat((array) ($piece['events'] ?? []));
            }
        }

        return $events
            ->filter(fn (mixed $event): bool => is_array($event))
            ->map(function (array $event): array {
                $occurredAt = trim((string) ($event['date'] ?? '').'T'.(string) ($event['time'] ?? '').(string) ($event['GMTOffset'] ?? ''));
                try {
                    $occurredAt = str_contains($occurredAt, 'T') ? CarbonImmutable::parse($occurredAt)->toIso8601String() : null;
                } catch (Throwable) {
                    $occurredAt = null;
                }

                return [
                    'occurred_at' => $occurredAt,
                    'type_code' => (string) ($event['typeCode'] ?? ''),
                    'description' => (string) ($event['description'] ?? ''),
                    'location' => collect((array) ($event['serviceArea'] ?? []))
                        ->pluck('description')->filter()->implode(' · '),
                    'signed_by' => $event['signedBy'] ?? null,
                ];
            })
            ->unique(fn (array $event): string => implode('|', [$event['occurred_at'], $event['type_code'], $event['description'], $event['location']]))
            ->sortByDesc('occurred_at')
            ->values()
            ->all();
    }

    private function normalizedStatus(string $rawStatus, string $eventCode): string
    {
        $status = strtolower(trim($rawStatus));
        $eventCode = strtoupper(trim($eventCode));

        return match (true) {
            str_contains($status, 'deliver') || in_array($eventCode, ['OK', 'DL', 'DD'], true) => 'delivered',
            str_contains($status, 'courier') || $eventCode === 'WC' => 'out_for_delivery',
            str_contains($status, 'exception') || in_array($eventCode, ['NH', 'BA', 'MS', 'OH'], true) => 'exception',
            str_contains($status, 'return') || in_array($eventCode, ['RR', 'RT'], true) => 'returned',
            in_array($eventCode, ['PU', 'AF'], true) => 'picked_up',
            $eventCode === '' && $status === '' => 'created',
            default => 'in_transit',
        };
    }

    private function publicTrackingUrl(string $trackingNumber): string
    {
        return 'https://www.dhl.com/global-en/home/tracking.html?tracking-id='.rawurlencode($trackingNumber);
    }
}
