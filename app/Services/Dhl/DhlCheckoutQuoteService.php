<?php

namespace App\Services\Dhl;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class DhlCheckoutQuoteService
{
    public function __construct(
        private DhlPackingService $packing,
        private DhlRateService $rates,
        private DhlSettingsService $settings,
    ) {
    }

    /**
     * @param Collection<int, mixed> $cart
     * @return array<string, mixed>
     */
    public function quote(Collection $cart, array $destination, float $declaredValue, float $subtotal): array
    {
        $config = $this->settings->resolved();
        $currency = strtoupper((string) ($config['currency'] ?? 'USD'));
        $packing = $this->packing->pack($cart);
        $response = $this->rates->quote(
            $destination,
            collect($packing['packages'])->map(fn (array $package): array => [
                'weight' => $package['weight'],
                'length' => $package['length'],
                'width' => $package['width'],
                'height' => $package['height'],
                'type_code' => $package['dhl_package_code'] ?? '',
            ])->all(),
            max(0.01, $declaredValue),
        );

        $services = collect((array) ($response['products'] ?? []))
            ->map(function (array $product) use ($currency): ?array {
                $price = collect((array) ($product['totalPrice'] ?? []))
                    ->first(fn (array $candidate): bool => strtoupper((string) ($candidate['priceCurrency'] ?? '')) === $currency);

                if (! $price || ! is_numeric($price['price'] ?? null) || (float) $price['price'] <= 0) {
                    return null;
                }

                return [
                    'code' => (string) ($product['productCode'] ?? ''),
                    'name' => (string) ($product['productName'] ?? 'DHL Express'),
                    'provider_price' => round((float) $price['price'], 2),
                    'currency' => $currency,
                    'delivery' => data_get($product, 'deliveryCapabilities.estimatedDeliveryDateAndTime'),
                ];
            })
            ->filter()
            ->sortBy('provider_price')
            ->values();

        $selected = $services->first();
        if (! $selected) {
            throw new InvalidArgumentException("A DHL não devolveu uma tarifa válida em {$currency} para este carrinho e destino.");
        }

        $markupEnabled = (bool) ($config['rate_markup_enabled'] ?? true);
        $markupPercent = $markupEnabled
            ? max(0, (float) ($config['rate_markup_percent'] ?? 0))
            : 0.0;
        $priceWithMarkup = round($selected['provider_price'] * (1 + ($markupPercent / 100)), 2);
        $freeShipping = $this->qualifiesForFreeShipping($packing, $subtotal, $config);

        $customerPrice = $freeShipping ? 0.0 : $priceWithMarkup;
        $packageCount = count($packing['packages']);

        return [
            'reference' => (string) Str::uuid(),
            'provider' => 'dhl',
            'service_code' => $selected['code'],
            'service_name' => $selected['name'],
            'provider_price' => $selected['provider_price'],
            'markup_percent' => $markupPercent,
            'price' => $customerPrice,
            'average_price_per_package' => round($customerPrice / max(1, $packageCount), 2),
            'currency' => $selected['currency'],
            'estimated_delivery_at' => $selected['delivery'],
            'free_shipping' => $freeShipping,
            'packages' => $packing['packages'],
            'package_count' => $packageCount,
            'item_count' => $packing['item_count'],
            'actual_weight' => $packing['actual_weight'],
            'billable_weight' => $packing['billable_weight'],
            'uses_estimates' => $packing['uses_estimates'],
            'requires_manual_review' => $packing['requires_manual_review'],
            'review_items' => $packing['review_items'],
            'taxes_included' => false,
            'tax_notice' => 'Impostos, tributos aduaneiros e taxas locais não estão incluídos. Quando aplicáveis, serão cobrados do destinatário no país de destino.',
            'expires_at' => now()->addMinutes(10)->toIso8601String(),
        ];
    }

    private function qualifiesForFreeShipping(array $packing, float $subtotal, array $config): bool
    {
        if (! ($config['free_shipping_enabled'] ?? false)) {
            return false;
        }

        $criteria = [];
        if ((int) ($config['free_shipping_min_items'] ?? 0) > 0) {
            $criteria[] = $packing['item_count'] >= (int) $config['free_shipping_min_items'];
        }
        if ((float) ($config['free_shipping_min_subtotal'] ?? 0) > 0) {
            $criteria[] = $subtotal >= (float) $config['free_shipping_min_subtotal'];
        }
        if ((float) ($config['free_shipping_max_billable_weight_kg'] ?? 0) > 0) {
            $criteria[] = $packing['billable_weight'] <= (float) $config['free_shipping_max_billable_weight_kg'];
        }

        return $criteria !== [] && ! in_array(false, $criteria, true);
    }
}
