<?php

namespace App\Services\Dhl;

use App\Models\DhlPackage;
use Illuminate\Support\Collection;
use InvalidArgumentException;

final class DhlPackingService
{
    public function __construct(
        private DhlProductMeasurementEstimator $measurements,
        private DhlSettingsService $settings,
        private ?array $configOverride = null,
    ) {
    }

    /**
     * @param Collection<int, mixed> $cart
     * @param Collection<int, DhlPackage>|null $packageTypes
     * @return array{packages:array<int,array>,package_count:int,item_count:int,actual_weight:float,billable_weight:float,uses_estimates:bool,requires_manual_review:bool,review_items:array<int,array>}
     */
    public function pack(Collection $cart, ?Collection $packageTypes = null): array
    {
        $config = $this->configOverride ?? $this->settings->resolved();
        $allowFallback = (bool) ($config['fallback_measurements_enabled'] ?? true);
        $divisor = max(1, (float) ($config['volumetric_divisor'] ?? 5000));
        $packageTypes ??= DhlPackage::query()->where('active', true)->orderBy('sort_order')->get();

        if ($packageTypes->isEmpty()) {
            throw new InvalidArgumentException('Nenhuma embalagem DHL está ativa no painel.');
        }

        $units = collect();
        foreach ($cart as $cartItem) {
            if (! $cartItem->product || (int) $cartItem->quantity < 1) {
                continue;
            }

            $measurement = $this->measurements->forProduct($cartItem->product, $allowFallback);
            if (min(
                (float) $measurement['weight'],
                (float) $measurement['length'],
                (float) $measurement['width'],
                (float) $measurement['height'],
            ) <= 0) {
                $name = $cartItem->product->name ?: $cartItem->product->external_name ?: $cartItem->product->sku;
                if ($measurement['profile'] === 'missing') {
                    throw new InvalidArgumentException("O produto {$name} ainda não possui peso e dimensões. Ative os perfis estimados da DHL no painel ou cadastre as medidas reais do produto.");
                }
                throw new InvalidArgumentException("O produto {$name} não possui uma média válida de peso e dimensões para a cotação DHL.");
            }

            for ($quantity = 0; $quantity < (int) $cartItem->quantity; $quantity++) {
                $units->push($measurement + [
                    'product_id' => (int) $cartItem->product_id,
                    'sku' => (string) $cartItem->product->sku,
                    'name' => (string) ($cartItem->product->name ?: $cartItem->product->external_name ?: $cartItem->product->sku),
                ]);
            }
        }

        if ($units->isEmpty()) {
            throw new InvalidArgumentException('O carrinho não possui produtos válidos para embalar.');
        }

        $units = $units->sortByDesc('volume')->values();
        $packed = [];

        foreach ($units as $unit) {
            $selectedIndex = $this->findExistingPackage($packed, $unit);

            if ($selectedIndex === null) {
                $upgrade = $this->findUpgradeablePackage($packed, $unit, $packageTypes);
                if ($upgrade) {
                    [$selectedIndex, $type] = $upgrade;
                    $packed[$selectedIndex] = $this->upgradePackage($packed[$selectedIndex], $type);
                } else {
                    $type = $packageTypes->first(fn (DhlPackage $candidate): bool => $this->unitFitsType($unit, $candidate));
                    if (! $type) {
                        $packed[] = $this->newCustomPackage($unit);
                        $selectedIndex = array_key_last($packed);
                    } else {
                        $packed[] = $this->newPackage($type);
                        $selectedIndex = array_key_last($packed);
                    }
                }
            }

            $packed[$selectedIndex]['content_weight'] += $unit['weight'];
            $packed[$selectedIndex]['used_volume'] += $unit['volume'];
            $packed[$selectedIndex]['item_count']++;
            $packed[$selectedIndex]['uses_estimates'] = $packed[$selectedIndex]['uses_estimates'] || $unit['estimated'];
            $packed[$selectedIndex]['requires_manual_review'] = $packed[$selectedIndex]['requires_manual_review'] || $unit['restricted'];
            $packed[$selectedIndex]['packed_units'][] = $unit;
            $itemKey = $unit['product_id'].':'.$unit['sku'];
            if (! isset($packed[$selectedIndex]['items'][$itemKey])) {
                $packed[$selectedIndex]['items'][$itemKey] = [
                    'product_id' => $unit['product_id'], 'sku' => $unit['sku'], 'name' => $unit['name'],
                    'quantity' => 0, 'profile' => $unit['profile'], 'estimated' => $unit['estimated'],
                    'requires_manual_review' => $unit['restricted'],
                ];
            }
            $packed[$selectedIndex]['items'][$itemKey]['quantity']++;
        }

        $actualWeight = 0.0;
        $billableWeight = 0.0;
        $usesEstimates = false;
        $requiresManualReview = false;

        foreach ($packed as &$package) {
            $package['weight'] = round($package['content_weight'] + $package['tare_weight'], 3);
            $package['volumetric_weight'] = round(($package['length'] * $package['width'] * $package['height']) / $divisor, 3);
            $package['billable_weight'] = max($package['weight'], $package['volumetric_weight']);
            $package['items'] = array_values($package['items']);
            $actualWeight += $package['weight'];
            $billableWeight += $package['billable_weight'];
            $usesEstimates = $usesEstimates || $package['uses_estimates'];
            $requiresManualReview = $requiresManualReview || $package['requires_manual_review'];
            unset($package['content_weight'], $package['used_volume'], $package['usable_volume'], $package['tare_weight'], $package['max_weight'], $package['max_items'], $package['packed_units']);
        }
        unset($package);

        return [
            'packages' => array_values($packed),
            'package_count' => count($packed),
            'item_count' => $units->count(),
            'actual_weight' => round($actualWeight, 3),
            'billable_weight' => round($billableWeight, 3),
            'uses_estimates' => $usesEstimates,
            'requires_manual_review' => $requiresManualReview,
            'review_items' => $units
                ->filter(fn (array $unit): bool => $unit['restricted'])
                ->unique(fn (array $unit): string => $unit['product_id'].':'.$unit['sku'])
                ->map(fn (array $unit): array => [
                    'product_id' => $unit['product_id'],
                    'sku' => $unit['sku'],
                    'name' => $unit['name'],
                    'profile' => $unit['profile'],
                ])
                ->values()
                ->all(),
        ];
    }

    private function findExistingPackage(array $packed, array $unit): ?int
    {
        foreach ($packed as $index => $package) {
            if ($package['item_count'] >= $package['max_items']) {
                continue;
            }

            if (($package['content_weight'] + $unit['weight'] + $package['tare_weight']) > $package['max_weight']) {
                continue;
            }

            if (($package['used_volume'] + $unit['volume']) > $package['usable_volume']) {
                continue;
            }

            if ($this->dimensionsFit($unit, $package)) {
                return $index;
            }
        }

        return null;
    }

    /** @return array{int,DhlPackage}|null */
    private function findUpgradeablePackage(array $packed, array $unit, Collection $packageTypes): ?array
    {
        foreach ($packed as $index => $package) {
            foreach ($packageTypes as $type) {
                if ($type->volumeCm3() <= ($package['length'] * $package['width'] * $package['height'])) {
                    continue;
                }

                if (($package['item_count'] + 1) > $type->max_items
                    || ($package['content_weight'] + $unit['weight'] + $type->tare_weight_kg) > $type->max_gross_weight_kg
                    || ($package['used_volume'] + $unit['volume']) > $type->usableVolumeCm3()) {
                    continue;
                }

                $allUnitsFit = collect([...$package['packed_units'], $unit])
                    ->every(fn (array $packedUnit): bool => $this->dimensionsFit($packedUnit, [
                        'length' => $type->length_cm,
                        'width' => $type->width_cm,
                        'height' => $type->height_cm,
                    ]));

                if ($allUnitsFit) {
                    return [$index, $type];
                }
            }
        }

        return null;
    }

    private function unitFitsType(array $unit, DhlPackage $type): bool
    {
        return $type->max_items > 0
            && ($unit['weight'] + $type->tare_weight_kg) <= $type->max_gross_weight_kg
            && $unit['volume'] <= $type->usableVolumeCm3()
            && $this->dimensionsFit($unit, [
                'length' => $type->length_cm,
                'width' => $type->width_cm,
                'height' => $type->height_cm,
            ]);
    }

    private function dimensionsFit(array $unit, array $package): bool
    {
        $itemDimensions = [(float) $unit['length'], (float) $unit['width'], (float) $unit['height']];
        $boxDimensions = [(float) $package['length'], (float) $package['width'], (float) $package['height']];
        sort($itemDimensions);
        sort($boxDimensions);

        return $itemDimensions[0] <= $boxDimensions[0]
            && $itemDimensions[1] <= $boxDimensions[1]
            && $itemDimensions[2] <= $boxDimensions[2];
    }

    private function newPackage(DhlPackage $type): array
    {
        return [
            'package_id' => $type->id,
            'code' => $type->code,
            'name' => $type->name,
            'dhl_package_code' => $type->dhl_package_code,
            'length' => $type->length_cm,
            'width' => $type->width_cm,
            'height' => $type->height_cm,
            'tare_weight' => $type->tare_weight_kg,
            'max_weight' => $type->max_gross_weight_kg,
            'max_items' => $type->max_items,
            'usable_volume' => $type->usableVolumeCm3(),
            'content_weight' => 0.0,
            'used_volume' => 0.0,
            'item_count' => 0,
            'uses_estimates' => false,
            'requires_manual_review' => false,
            'items' => [],
            'packed_units' => [],
        ];
    }

    /**
     * Produtos maiores que as caixas DHL cadastradas ainda podem ser enviados à
     * API como um volume com dimensões próprias. A DHL decide se há um serviço
     * disponível para esse tamanho e destino.
     */
    private function newCustomPackage(array $unit): array
    {
        return [
            'package_id' => null,
            'code' => 'custom',
            'name' => 'Volume sob medida',
            'dhl_package_code' => '',
            'length' => $unit['length'],
            'width' => $unit['width'],
            'height' => $unit['height'],
            'tare_weight' => 0.0,
            'max_weight' => $unit['weight'],
            'max_items' => 1,
            'usable_volume' => $unit['volume'],
            'content_weight' => 0.0,
            'used_volume' => 0.0,
            'item_count' => 0,
            'uses_estimates' => false,
            'requires_manual_review' => true,
            'items' => [],
            'packed_units' => [],
        ];
    }

    private function upgradePackage(array $package, DhlPackage $type): array
    {
        return array_replace($package, [
            'package_id' => $type->id,
            'code' => $type->code,
            'name' => $type->name,
            'dhl_package_code' => $type->dhl_package_code,
            'length' => $type->length_cm,
            'width' => $type->width_cm,
            'height' => $type->height_cm,
            'tare_weight' => $type->tare_weight_kg,
            'max_weight' => $type->max_gross_weight_kg,
            'max_items' => $type->max_items,
            'usable_volume' => $type->usableVolumeCm3(),
        ]);
    }
}
