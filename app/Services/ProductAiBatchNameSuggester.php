<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductAiBatchNameSuggester
{
    public function suggest(Collection $products): string
    {
        $products = $products->filter(fn ($product) => $product instanceof Product)->unique('id')->values();
        $count = $products->count();

        $keywordLabels = $products
            ->map(fn (Product $product) => $this->labelFromKeywords($product))
            ->filter()
            ->countBy()
            ->sortDesc();
        $taxonomyLabels = $products
            ->map(fn (Product $product) => $this->labelFromTaxonomy($product))
            ->filter()
            ->countBy()
            ->sortDesc();

        $keyword = $keywordLabels->keys()->first();
        $keywordCount = (int) ($keywordLabels->first() ?? 0);
        $taxonomy = $taxonomyLabels->keys()->first();
        $label = $keyword && $keywordCount >= max(1, (int) ceil($count / 2))
            ? $keyword
            : ($taxonomy ?: ($keyword ?: 'Productos'));

        return Str::limit($label.' para editar · '.$count.' '.($count === 1 ? 'producto' : 'productos'), 120, '');
    }

    private function labelFromTaxonomy(Product $product): ?string
    {
        $generic = ['producto', 'productos', 'catalogo', 'catalog', 'moda', 'otros', 'otras', 'general', 'sin categoria'];

        foreach ([$product->categoriasFilhas?->name, $product->subcategory?->name, $product->category?->name] as $label) {
            $label = trim((string) $label);
            if ($label === '' || in_array(Str::lower(Str::ascii($label)), $generic, true)) {
                continue;
            }

            return $label;
        }

        return null;
    }

    private function labelFromKeywords(Product $product): ?string
    {
        $text = Str::lower(Str::ascii(implode(' ', [
            $product->name,
            $product->external_name,
            $product->category?->name,
            $product->subcategory?->name,
            $product->categoriasFilhas?->name,
        ])));

        foreach ([
            'Tenis' => ['tenis', 'sneaker', 'zapatilla', 'sapatilha'],
            'Camisetas' => ['camiseta', 't-shirt', 'tshirt'],
            'Blusas' => ['blusa'],
            'Camisas' => ['camisa'],
            'Vinos' => ['vino', 'vinho', 'wine'],
            'Vestidos' => ['vestido'],
            'Bolsas' => ['bolsa', 'cartera', 'handbag'],
            'Perfumes' => ['perfume', 'fragancia'],
            'Pantalones' => ['pantalon', 'jean'],
            'Shorts y bermudas' => ['short', 'bermuda'],
            'Zapatos' => ['zapato', 'calzado', 'mocasin'],
            'Relojes' => ['reloj', 'watch'],
            'Anteojos' => ['anteojo', 'gafas', 'oculos'],
        ] as $label => $terms) {
            if (Str::contains($text, $terms)) {
                return $label;
            }
        }

        return null;
    }
}
