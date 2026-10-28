<?php

namespace App\Services\Dhl;

use App\Models\Product;
use Illuminate\Support\Str;

final class DhlCustomsProductData
{
    public function englishDescription(Product $product): ?string
    {
        $translation = $product->relationLoaded('translations')
            ? $product->translations->firstWhere('locale', 'en')
            : $product->translations()->where('locale', 'en')->first();

        $description = html_entity_decode(
            strip_tags((string) ($translation?->details ?? '')),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8',
        );
        $description = trim((string) preg_replace('/\s+/u', ' ', $description));

        return $description !== '' ? Str::limit($description, 255, '') : null;
    }

    public function countryOfOrigin(Product $product, string $storeCountryCode): string
    {
        $productOrigin = strtoupper(trim((string) $product->shipping_country_of_origin));

        return preg_match('/^[A-Z]{2}$/', $productOrigin)
            ? $productOrigin
            : strtoupper(trim($storeCountryCode));
    }
}
