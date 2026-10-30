<?php

namespace App\Services\Dhl;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;

final class DhlCustomsProductData
{
    private const EMPTY_ENGLISH_DESCRIPTION_HTML = [
        '',
        '&nbsp;',
        '<br>',
        '<br/>',
        '<br />',
        '<p></p>',
        '<p><br></p>',
        '<p><br/></p>',
        '<p><br /></p>',
        '<p>&nbsp;</p>',
        '<div></div>',
        '<div><br></div>',
        '<div><br/></div>',
        '<div><br /></div>',
        '<div>&nbsp;</div>',
    ];

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

    public function applyEnglishDescriptionFilter(Builder $query, string $filter): void
    {
        if (! in_array($filter, ['with_description', 'without_description'], true)) {
            return;
        }

        $hasDescription = function (Builder $translations): void {
            $normalizedDetails = new Expression(
                "LOWER(TRIM(REPLACE(REPLACE(details, CHAR(13), ''), CHAR(10), '')))"
            );

            $translations
                ->where('locale', 'en')
                ->whereNotNull('details')
                ->whereNotIn($normalizedDetails, self::EMPTY_ENGLISH_DESCRIPTION_HTML);
        };

        if ($filter === 'with_description') {
            $query->whereHas('translations', $hasDescription);

            return;
        }

        $query->whereDoesntHave('translations', $hasDescription);
    }
}
