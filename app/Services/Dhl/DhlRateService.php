<?php

namespace App\Services\Dhl;

use InvalidArgumentException;

final class DhlRateService
{
    private array $settings;

    public function __construct(private MyDhlApiClient $client, ?DhlSettingsService $settingsService = null)
    {
        $this->settings = $settingsService?->resolved() ?? (array) config('services.dhl', []);
    }

    /**
     * @param array<int, array{weight: float|int, length: float|int, width: float|int, height: float|int, type_code?: string}> $packages
     */
    public function quote(array $destination, array $packages, float $declaredValue, ?string $plannedAt = null): array
    {
        $origin = $this->normalizeOrigin((array) ($this->settings['origin'] ?? []));
        $destination = $this->normalizeDestination($destination);
        $packages = $this->normalizePackages($packages);

        $excludedCountryCodes = collect((array) ($this->settings['excluded_country_codes'] ?? ['PY']))
            ->map(fn ($countryCode): string => strtoupper(trim((string) $countryCode)))
            ->filter()
            ->all();

        if (in_array($destination['country_code'], $excludedCountryCodes, true)) {
            throw new InvalidArgumentException('A DHL não está disponível para o país de destino informado.');
        }

        if ($declaredValue <= 0) {
            throw new InvalidArgumentException('O valor declarado precisa ser maior que zero.');
        }

        $currency = strtoupper((string) ($this->settings['currency'] ?? 'USD'));
        $plannedAt ??= now(config('app.timezone'))->addWeekday()->setTime(10, 0)->format('Y-m-d\TH:i:s\G\M\TP');

        $payload = [
            'customerDetails' => [
                'shipperDetails' => $this->postalAddress($origin),
                'receiverDetails' => $this->postalAddress($destination),
            ],
            'accounts' => [[
                'typeCode' => 'shipper',
                'number' => $this->client->accountNumber(),
            ]],
            'plannedShippingDateAndTime' => $plannedAt,
            'unitOfMeasurement' => (string) ($this->settings['unit_of_measurement'] ?? 'metric'),
            'isCustomsDeclarable' => true,
            'monetaryAmount' => [[
                'typeCode' => 'declaredValue',
                'value' => round($declaredValue, 2),
                'currency' => $currency,
            ]],
            'requestAllValueAddedServices' => false,
            'estimatedDeliveryDate' => [
                'isRequested' => true,
                'typeCode' => 'QDDC',
            ],
            'returnStandardProductsOnly' => false,
            'nextBusinessDay' => true,
            'packages' => $packages,
        ];

        return $this->client->rates($payload);
    }

    private function normalizeDestination(array $destination): array
    {
        $countryCode = strtoupper(trim((string) ($destination['country_code'] ?? '')));
        $cityName = trim((string) ($destination['city_name'] ?? ''));
        $postalCode = trim((string) ($destination['postal_code'] ?? ''));

        if (preg_match('/^[A-Z]{2}$/', $countryCode) !== 1 || $cityName === '') {
            throw new InvalidArgumentException('O destino DHL precisa ter país ISO-2 e cidade.');
        }

        if ($countryCode === 'BR') {
            $postalCode = preg_replace('/\D+/', '', $postalCode) ?? '';

            if (strlen($postalCode) !== 8) {
                throw new InvalidArgumentException('O CEP brasileiro precisa ter exatamente 8 dígitos.');
            }
        }

        return [
            'country_code' => $countryCode,
            'postal_code' => $postalCode,
            'city_name' => $cityName,
            'province_code' => trim((string) ($destination['province_code'] ?? '')),
            'district_name' => trim((string) ($destination['district_name'] ?? '')),
            'address_line_1' => trim((string) ($destination['address_line_1'] ?? '')),
            'address_line_2' => trim((string) ($destination['address_line_2'] ?? '')),
        ];
    }

    private function normalizeOrigin(array $origin): array
    {
        $countryCode = strtoupper(trim((string) ($origin['country_code'] ?? '')));
        $postalCode = trim((string) ($origin['postal_code'] ?? ''));
        $cityName = trim((string) ($origin['city_name'] ?? ''));
        $addressLine1 = trim((string) ($origin['address_line_1'] ?? ''));

        if (preg_match('/^[A-Z]{2}$/', $countryCode) !== 1
            || $postalCode === ''
            || $cityName === ''
            || $addressLine1 === '') {
            throw new InvalidArgumentException('A origem DHL precisa ter país ISO-2, código postal, cidade e endereço.');
        }

        return array_merge($origin, [
            'country_code' => $countryCode,
            'postal_code' => $postalCode,
            'city_name' => $cityName,
            'address_line_1' => $addressLine1,
        ]);
    }

    private function normalizePackages(array $packages): array
    {
        if ($packages === []) {
            throw new InvalidArgumentException('Informe ao menos um volume para cotar com a DHL.');
        }

        return collect($packages)->map(function (array $package, int $index): array {
            $weight = (float) ($package['weight'] ?? 0);
            $length = (float) ($package['length'] ?? 0);
            $width = (float) ($package['width'] ?? 0);
            $height = (float) ($package['height'] ?? 0);

            if (min($weight, $length, $width, $height) <= 0) {
                throw new InvalidArgumentException('Peso e dimensões do volume '.($index + 1).' precisam ser maiores que zero.');
            }

            $normalized = [
                'weight' => round($weight, 3),
                'dimensions' => [
                    'length' => round($length, 3),
                    'width' => round($width, 3),
                    'height' => round($height, 3),
                ],
            ];

            $typeCode = strtoupper(trim((string) ($package['type_code'] ?? '')));
            if ($typeCode !== '' && preg_match('/^[A-Z0-9]{3}$/', $typeCode) === 1) {
                $normalized['typeCode'] = $typeCode;
            }

            return $normalized;
        })->values()->all();
    }

    private function postalAddress(array $address): array
    {
        return array_filter([
            'postalCode' => trim((string) ($address['postal_code'] ?? '')),
            'cityName' => trim((string) ($address['city_name'] ?? '')),
            'countryCode' => strtoupper(trim((string) ($address['country_code'] ?? ''))),
            'provinceCode' => trim((string) ($address['province_code'] ?? '')),
            'addressLine1' => trim((string) ($address['address_line_1'] ?? '')),
            'addressLine2' => trim((string) ($address['address_line_2'] ?? '')),
            'countyName' => trim((string) ($address['district_name'] ?? '')),
        ], static fn (string $value): bool => $value !== '');
    }
}
