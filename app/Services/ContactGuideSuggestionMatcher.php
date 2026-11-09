<?php

namespace App\Services;

use App\Models\ContactGuideEntry;
use App\Models\Product;
use Illuminate\Support\Str;

class ContactGuideSuggestionMatcher
{
    private const TAXONOMY_CONCEPTS = [
        'perfumery' => ['perfum', 'parfum', 'fragran', 'edition privee'],
        'home' => ['casa', 'home', 'hogar', 'decor', 'cozinha', 'cocina', 'electrodom', 'olla', 'sarten', 'mueble'],
        'beverages' => ['bebid', 'vinho', 'vino', 'wine', 'champagne', 'champan', 'whisk', 'licor', 'charcut', 'cerve', 'espumante'],
        'optical' => ['optic', 'lente', 'oculo', 'gafa', 'eyewear'],
        'bridal' => ['noiva', 'novia', 'bridal', 'casamento', 'boda'],
        'party' => ['festa', 'fiesta', 'party', 'vestido'],
        'kids' => ['kids', 'nino', 'infantil', 'bebe'],
        'outlet' => ['outlet'],
        'feminine' => ['feminin', 'mujer', 'woman', 'dama'],
        'masculine' => ['masculin', 'hombre', 'menswear'],
        'casual' => ['casual', 'viaje', 'viagem', 'maleta', 'sneaker'],
        'luxury' => ['luxo', 'lujo', 'luxury'],
    ];

    public function match(Product $product, ContactGuideEntry $entry): ?string
    {
        return $this->matchValues(
            $product->brand?->name,
            [
                $product->category?->name,
                $product->subcategory?->name,
                $product->categoriasFilhas?->name,
            ],
            $entry->brands ?? [],
            [
                $entry->sector,
                $entry->sector_en,
                $entry->sector_es,
                $entry->description,
                $entry->description_en,
                $entry->description_es,
            ],
        );
    }

    public function matchValues(
        ?string $productBrand,
        array $productTaxonomy,
        array $guideBrands,
        array $guideTaxonomy,
    ): ?string {
        if (filled($productBrand) && collect($guideBrands)->contains(
            fn ($guideBrand) => is_string($guideBrand) && $this->brandsMatch($productBrand, $guideBrand)
        )) {
            return 'brand';
        }

        $productConcepts = $this->taxonomyConcepts($productTaxonomy);
        if ($productConcepts === []) {
            return null;
        }

        return array_intersect($productConcepts, $this->taxonomyConcepts($guideTaxonomy)) !== []
            ? 'taxonomy'
            : null;
    }

    private function brandsMatch(string $productBrand, string $guideBrand): bool
    {
        $product = $this->normalizeBrand($productBrand);
        $guide = $this->normalizeBrand($guideBrand);
        if ($product === '' || $guide === '') {
            return false;
        }
        if ($product === $guide) {
            return true;
        }

        $longer = mb_strlen($product) > mb_strlen($guide) ? $product : $guide;
        $shorter = mb_strlen($product) > mb_strlen($guide) ? $guide : $product;

        return mb_strlen($shorter) >= 4 && str_contains(" {$longer} ", " {$shorter} ");
    }

    private function normalizeBrand(string $value): string
    {
        $normalized = $this->normalize($value);
        $normalized = preg_replace('/\b(and|e|y)\b/u', ' ', $normalized) ?: '';
        $normalized = trim(preg_replace('/\s+/u', ' ', $normalized) ?: '');

        return [
            'ysl' => 'saint laurent',
            'yves saint laurent' => 'saint laurent',
            'salvatore ferragamo' => 'ferragamo',
        ][$normalized] ?? $normalized;
    }

    private function taxonomyConcepts(array $values): array
    {
        $text = collect($values)
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn ($value) => $this->normalize($value))
            ->implode(' ');
        if ($text === '') {
            return [];
        }

        return collect(self::TAXONOMY_CONCEPTS)
            ->filter(fn (array $terms) => Str::contains($text, $terms))
            ->keys()
            ->all();
    }

    private function normalize(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', preg_replace('/[^a-z0-9]+/u', ' ', Str::lower(Str::ascii($value))) ?: '') ?: '');
    }
}
