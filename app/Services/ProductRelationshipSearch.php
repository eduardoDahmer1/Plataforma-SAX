<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductRelationshipSearch
{
    public function normalize(?string $text): string
    {
        return trim(preg_replace('/[^A-Z0-9]+/', ' ', Str::upper(Str::ascii(Product::normalizeCatalogText($text)))) ?? '');
    }

    private function tokens(string $text): array
    {
        return array_values(array_unique(explode(' ', $this->normalize($text))));
    }

    private function modelTokens(string $text, string $brand): array
    {
        $text = Product::referenceParts($text)['reference'];

        return array_values(array_diff($this->tokens($text), $this->tokens($brand), [
            '', 'ZAPATO', 'SAPATO', 'SHOES', 'DE', 'DA', 'DO', 'FOR',
            'NERO', 'BLACK', 'NEGRO', 'PRETO', 'WHITE', 'BLANCO', 'BRANCO', 'BIANCO',
            'BLUE', 'BLU', 'AZUL', 'RED', 'ROJO', 'VERMELHO', 'GREEN', 'VERDE',
            'BEIGE', 'BROWN', 'MARRON', 'MARROM', 'GREY', 'GRAY', 'GRIS', 'CINZA', 'ROSA',
        ]));
    }

    public function match(Product $candidate, string $query, ?Product $current = null): ?array
    {
        $brand = (string) ($current?->brand?->name ?? $candidate->brand?->name ?? '');
        $wanted = $this->modelTokens($query, $brand);
        $actual = array_values(array_unique(array_merge(
            $this->modelTokens((string) $candidate->external_name, (string) $candidate->brand?->name),
            $this->modelTokens((string) $candidate->name, (string) $candidate->brand?->name),
        )));
        $haystack = $this->tokens(($candidate->external_name ?? '').' '.($candidate->name ?? '').' '.($candidate->sku ?? '').' '.($candidate->brand?->name ?? ''));
        $allWords = count(array_diff($this->tokens($query), $haystack)) === 0;
        $sameBrand = $current && $current->brand_id && $candidate->brand_id == $current->brand_id;
        if ($current && $current->brand_id && $candidate->brand_id && ! $sameBrand) {
            return null;
        }
        $exactReference = $current && $current->relationshipReferenceKey() !== ''
            && $current->relationshipReferenceKey() === $candidate->relationshipReferenceKey();
        $overlap = count(array_intersect($wanted, $actual));
        // A different model number is not a spelling variation (Fantasy 7 / Fantasy 8).
        $numbers = fn ($tokens) => array_values(array_filter($tokens, fn ($token) => preg_match('/[0-9]/', $token)));
        $conflict = $numbers($wanted) && $numbers($actual) && array_diff($numbers($wanted), $numbers($actual));
        $modelMatch = $wanted && ! $conflict && $overlap === count($wanted);
        $partial = ! $conflict && $overlap >= 2 && $overlap / max(1, count($wanted)) >= 0.6;
        if (! $allWords && ! $modelMatch && ! $partial) {
            return null;
        }

        return [
            'score' => ($allWords ? 100 : 0) + ($exactReference ? 80 : 0) + ($modelMatch ? 60 : 0) + ($sameBrand ? 20 : 0) + $overlap,
            'reason' => $exactReference ? 'Mesma referência' : ($modelMatch ? 'Modelo compatível' : ($allWords ? 'Nome, marca ou SKU' : 'Nome parcialmente semelhante — revisar')),
            'exact' => (bool) ($exactReference && $sameBrand),
        ];
    }

    public function compatibleSize(Product $current, Product $candidate): bool
    {
        return $current->relationshipColorKey() !== ''
            && $current->relationshipColorKey() === $candidate->relationshipColorKey()
            && $this->normalize($current->inferredSize()) !== $this->normalize($candidate->inferredSize())
            && $this->match($candidate, $current->relationshipSearchTerm(), $current) !== null;
    }

    public function search(string $query, ?Product $current, string $context): Collection
    {
        $matches = collect();
        // Read in batches, with no candidate cutoff before normalization and ranking.
        Product::with('brand:id,name')->when($current, fn ($q) => $q->where('id', '!=', $current->id))
            ->when($current?->brand_id, fn ($q) => $q->where(fn ($brands) => $brands->where('brand_id', $current->brand_id)->orWhereNull('brand_id')))
            ->select(['id', 'brand_id', 'sku', 'name', 'external_name', 'photo', 'color', 'size', 'stock', 'parent_id', 'product_role'])
            ->chunkById(500, function ($products) use (&$matches, $query, $current, $context) {
                foreach ($products as $product) {
                    $match = $this->match($product, $query, $current);
                    if (! $match) {
                        continue;
                    }
                    $color = $product->relationshipColorKey();
                    $currentColor = $current?->relationshipColorKey() ?? '';
                    if ($context === 'size' && $current) {
                        if (! $this->compatibleSize($current, $product)) {
                            continue;
                        }
                    }
                    if ($context === 'color' && $currentColor !== '' && $color === $currentColor) {
                        continue;
                    }
                    $product->match_score = $match['score'];
                    $product->match_reason = $match['reason'];
                    $product->auto_select = $match['exact'] && $currentColor !== '' && $color !== ''
                        && ($context !== 'size' || $this->compatibleSize($current, $product));
                    $product->brand_name = $product->brand?->name;
                    $matches->push($product);
                }
            });
        $matches = $matches->sortBy([['match_score', 'desc'], ['external_name', 'asc'], ['id', 'asc']]);
        if ($context === 'color') {
            // Do not merge unrelated models just because their color is the same.
            $matches = $matches->groupBy(fn ($p) => $p->brand_id.'|'.$p->relationshipReferenceKey().'|'.($p->relationshipColorKey() ?: 'unknown-'.$p->id))
                ->map(fn ($group) => $group->sortByDesc(fn ($p) => [$p->product_role === 'P', filled($p->photo), (int) $p->stock])->first())
                ->sortBy([['match_score', 'desc'], ['external_name', 'asc'], ['id', 'asc']]);
        }

        return $matches->values()->map(function ($product) {
            $product->photo = $product->photo_url;
            $product->size = $product->inferredSize();
            $product->reference = $product->referenceLabel();
            $product->inferred_color = $product->inferredColorKey();
            $product->color_code = $product->inferredColorCode();
            $product->unsetRelation('brand');

            return $product;
        });
    }
}
