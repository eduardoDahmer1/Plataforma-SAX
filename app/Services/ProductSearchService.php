<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductSearchService
{
    private const MAX_DESCRIPTORS = 18;

    private const IGNORED_TERMS = [
        'a', 'as', 'com', 'cor', 'da', 'das', 'de', 'do', 'dos', 'e', 'em',
        'marca', 'modelo', 'na', 'nas', 'no', 'nos', 'o', 'os', 'para', 'por',
        'tam', 'tamanho', 'tamanos', 't',
        'con', 'del', 'el', 'la', 'las', 'los', 'talla', 'tallas',
        'and', 'brand', 'colour', 'for', 'of', 'the', 'with', 'size', 'sizes',
    ];

    private const TEXT_ALIASES = [
        'camisa' => ['shirt', 'shirts', 'camisa', 'camisas', 'camiseta', 'camisetas', 'remera', 'remeras', 'playera', 'playeras', 't-shirt', 'tshirt', 'tee'],
        'camiseta' => ['shirt', 'shirts', 'camisa', 'camisas', 'camiseta', 'camisetas', 'remera', 'remeras', 'playera', 'playeras', 't-shirt', 'tshirt', 'tee'],
        'shirt' => ['shirt', 'shirts', 'camisa', 'camisas', 'camiseta', 'camisetas', 'remera', 'remeras', 'playera', 'playeras', 't-shirt', 'tshirt', 'tee'],
        'pants' => ['pants', 'trousers', 'calca', 'calcas', 'pantalon', 'pantalones'],
        'shorts' => ['short', 'shorts', 'bermuda', 'bermudas'],
        'bag' => ['bag', 'bags', 'handbag', 'handbags', 'bolsa', 'bolsas', 'bolso', 'bolsos', 'cartera', 'carteras'],
        'backpack' => ['backpack', 'backpacks', 'mochila', 'mochilas'],
        'dress' => ['dress', 'dresses', 'vestido', 'vestidos'],
        'skirt' => ['skirt', 'skirts', 'saia', 'saias', 'falda', 'faldas'],
        'jacket' => ['jacket', 'jackets', 'coat', 'coats', 'jaqueta', 'jaquetas', 'casaco', 'casacos', 'chaqueta', 'chaquetas', 'abrigo', 'abrigos'],
        'sweater' => ['sweater', 'sweaters', 'pullover', 'sueter', 'sueteres', 'blusa', 'blusas'],
        'tenis' => ['tenis', 'tênis', 'sneaker', 'sneakers', 'shoe', 'shoes', 'sapato', 'sapatos', 'calçado', 'calcado', 'calcados', 'zapato', 'zapatos', 'zapatilla', 'zapatillas', 'calzado', 'calzados'],
        'shoes' => ['tenis', 'tênis', 'sneaker', 'sneakers', 'shoe', 'shoes', 'sapato', 'sapatos', 'calçado', 'calcado', 'calcados', 'zapato', 'zapatos', 'zapatilla', 'zapatillas', 'calzado', 'calzados'],
        'boots' => ['boot', 'boots', 'bota', 'botas'],
        'sandals' => ['sandal', 'sandals', 'sandalia', 'sandalias'],
        'oculos' => ['oculos', 'óculos', 'occhiali', 'gafas', 'anteojos', 'glasses', 'eyewear', 'sunglass', 'sunglasses'],
        'belt' => ['belt', 'belts', 'cinto', 'cintos', 'cinturon', 'cinturones'],
        'wallet' => ['wallet', 'wallets', 'carteira', 'carteiras', 'billetera', 'billeteras'],
        'watch' => ['watch', 'watches', 'relogio', 'relogios', 'reloj', 'relojes'],
        'perfume' => ['perfume', 'perfumes', 'fragrance', 'fragrances', 'fragancia', 'fragancias'],
        'hat' => ['hat', 'hats', 'cap', 'caps', 'chapeu', 'chapeus', 'bone', 'bones', 'sombrero', 'sombreros', 'gorra', 'gorras'],
        'underwear' => ['underwear', 'boxer', 'boxers', 'cueca', 'cuecas', 'ropa-interior'],
        'bra' => ['bra', 'bras', 'sutia', 'sutias', 'soutien', 'corpino', 'corpinos'],
        'blazer' => ['blazer', 'blazers', 'terno', 'ternos', 'traje', 'trajes', 'suit', 'suits'],
        'jeans' => ['jeans', 'jean', 'denim', 'calca jeans', 'pantalon vaquero', 'vaqueros'],
        'leggings' => ['legging', 'leggings', 'calca legging', 'calzas'],
        'polo' => ['polo', 'polos', 'camisa polo', 'polo shirt'],
        'blouse' => ['blouse', 'blouses', 'blusa', 'blusas'],
        'vest' => ['vest', 'vests', 'colete', 'coletes', 'chaleco', 'chalecos'],
        'jumpsuit' => ['jumpsuit', 'jumpsuits', 'macacao', 'macacoes', 'enterizo', 'enterizos'],
        'lingerie' => ['lingerie', 'lenceria', 'roupa intima', 'ropa interior'],
        'briefcase' => ['briefcase', 'briefcases', 'maletin', 'maletines', 'pasta executiva'],
        'luggage' => ['luggage', 'suitcase', 'suitcases', 'mala', 'malas', 'maleta', 'maletas'],
        'clutch' => ['clutch', 'clutches', 'bolsa de mao', 'bolso de mano'],
        'crossbody' => ['crossbody', 'bandolera', 'bandoleras', 'bolsa transversal'],
        'tote' => ['tote', 'totes', 'bolsa tote', 'bolso tote'],
        'loafers' => ['loafer', 'loafers', 'mocassim', 'mocassins', 'mocasin', 'mocasines'],
        'flats' => ['flat', 'flats', 'sapatilha', 'sapatilhas', 'chatita', 'chatitas'],
        'tie' => ['tie', 'ties', 'gravata', 'gravatas', 'corbata', 'corbatas'],
        'socks' => ['sock', 'socks', 'meia', 'meias', 'media', 'medias'],
        'jewelry' => ['jewelry', 'jewellery', 'joia', 'joias', 'joya', 'joyas', 'bijuteria', 'bijuterias'],
        'makeup' => ['makeup', 'maquiagem', 'maquillaje', 'cosmetico', 'cosmeticos', 'cosmetic', 'cosmetics'],
        'wine' => ['wine', 'wines', 'vinho', 'vinhos', 'vino', 'vinos'],
        'drink' => ['drink', 'drinks', 'bebida', 'bebidas', 'licor', 'licores'],
        'cigar' => ['cigar', 'cigars', 'charuto', 'charutos', 'habano', 'habanos'],
        'home' => ['home', 'casa', 'decoracao', 'decoracion', 'decoration', 'decor'],
        'masculino' => ['masculino', 'masculina', 'homem', 'men', 'male', 'caballero'],
        'feminino' => ['feminino', 'feminina', 'femenino', 'femenina', 'mulher', 'women', 'woman', 'dama'],
        'femenino' => ['feminino', 'feminina', 'femenino', 'femenina', 'mulher', 'women', 'woman', 'dama'],
        'femenina' => ['feminino', 'feminina', 'femenino', 'femenina', 'mulher', 'women', 'woman', 'dama'],
        'infantil' => ['infantil', 'criança', 'crianca', 'kids', 'kid', 'niño', 'niña'],
    ];

    private const COLOR_ALIASES = [
        'preto' => ['terms' => ['preto', 'preta', 'negro', 'negra', 'black'], 'hex' => ['#000000']],
        'preta' => ['terms' => ['preto', 'preta', 'negro', 'negra', 'black'], 'hex' => ['#000000']],
        'negro' => ['terms' => ['preto', 'preta', 'negro', 'negra', 'black'], 'hex' => ['#000000']],
        'black' => ['terms' => ['preto', 'preta', 'negro', 'negra', 'black'], 'hex' => ['#000000']],
        'branco' => ['terms' => ['branco', 'branca', 'blanco', 'blanca', 'white'], 'hex' => ['#FFFFFF']],
        'branca' => ['terms' => ['branco', 'branca', 'blanco', 'blanca', 'white'], 'hex' => ['#FFFFFF']],
        'azul' => ['terms' => ['azul', 'blue'], 'hex' => ['#0000FF', '#4169E1', '#0047AB']],
        'azul marinho' => ['terms' => ['azul marinho', 'navy', 'navy blue', 'azul naval'], 'hex' => ['#000080', '#1F2B56']],
        'azul claro' => ['terms' => ['azul claro', 'celeste', 'light blue', 'sky blue'], 'hex' => ['#ADD8E6', '#87CEEB', '#89CFF0']],
        'marrom' => ['terms' => ['marrom', 'marron', 'marrones', 'castanho', 'brown', 'browns'], 'hex' => ['#A52A2A', '#8B4513']],
        'marron' => ['terms' => ['marrom', 'marron', 'marrones', 'castanho', 'brown', 'browns'], 'hex' => ['#A52A2A', '#8B4513']],
        'bege' => ['terms' => ['bege', 'beige', 'nude', 'arena', 'areia', 'sand'], 'hex' => ['#F5F5DC', '#C2B280', '#E3BC9A']],
        'cinza' => ['terms' => ['cinza', 'gris', 'gray', 'grey'], 'hex' => ['#808080']],
        'vermelho' => ['terms' => ['vermelho', 'vermelha', 'rojo', 'roja', 'red'], 'hex' => ['#FF0000']],
        'verde' => ['terms' => ['verde', 'green'], 'hex' => ['#008000']],
        'verde oliva' => ['terms' => ['verde oliva', 'oliva', 'olive', 'olive green'], 'hex' => ['#808000', '#556B2F']],
        'verde menta' => ['terms' => ['verde menta', 'menta', 'mint', 'mint green'], 'hex' => ['#98FF98']],
        'rosa' => ['terms' => ['rosa', 'pink'], 'hex' => ['#FFC0CB', '#FF1493']],
        'amarelo' => ['terms' => ['amarelo', 'amarela', 'amarillo', 'amarilla', 'yellow'], 'hex' => ['#FFFF00']],
        'laranja' => ['terms' => ['laranja', 'naranja', 'orange'], 'hex' => ['#FFA500']],
        'dourado' => ['terms' => ['dourado', 'dourada', 'dorado', 'dorada', 'gold'], 'hex' => ['#FFD700']],
        'prata' => ['terms' => ['prata', 'plateado', 'silver'], 'hex' => ['#C0C0C0']],
        'roxo' => ['terms' => ['roxo', 'roxa', 'morado', 'morada', 'purple'], 'hex' => ['#800080', '#4B0082']],
        'lilas' => ['terms' => ['lilas', 'lilac', 'lavanda', 'lavender'], 'hex' => ['#E6E6FA', '#C8A2C8']],
        'bordo' => ['terms' => ['bordo', 'bordô', 'burgundy', 'vinho', 'wine', 'marsala'], 'hex' => ['#800000', '#722F37']],
        'off white' => ['terms' => ['off white', 'off-white', 'branco gelo', 'blanco roto'], 'hex' => ['#FAF9F6', '#FFFFF0']],
        'caramelo' => ['terms' => ['caramelo', 'caramel', 'camel', 'cognac', 'conhaque'], 'hex' => ['#C19A6B', '#9A463D']],
        'terracota' => ['terms' => ['terracota', 'terracotta', 'telha', 'rust'], 'hex' => ['#E2725B', '#B7410E']],
        'chumbo' => ['terms' => ['chumbo', 'grafite', 'charcoal', 'antracite', 'anthracite'], 'hex' => ['#36454F', '#292929']],
        'fucsia' => ['terms' => ['fucsia', 'fúcsia', 'fuchsia', 'magenta'], 'hex' => ['#FF00FF', '#C71585']],
        'salmao' => ['terms' => ['salmao', 'salmão', 'salmon'], 'hex' => ['#FA8072']],
        'coral' => ['terms' => ['coral'], 'hex' => ['#FF7F50']],
        'mostarda' => ['terms' => ['mostarda', 'mustard'], 'hex' => ['#FFDB58']],
        'creme' => ['terms' => ['creme', 'cream', 'marfim', 'ivory'], 'hex' => ['#FFFDD0', '#FFFFF0']],
        'turquesa' => ['terms' => ['turquesa', 'turquoise', 'teal', 'verde azulado'], 'hex' => ['#40E0D0', '#008080']],
    ];

    private const SIZE_ALIASES = [
        'PP' => ['PP', 'XS'],
        'XS' => ['XS', 'PP'],
        'P' => ['P', 'S'],
        'S' => ['S', 'P'],
        'M' => ['M'],
        'G' => ['G', 'L'],
        'L' => ['L', 'G'],
        'GG' => ['GG', 'XL'],
        'XL' => ['XL', 'GG'],
        'XG' => ['XG', 'XXL', '2XL'],
        'XXL' => ['XXL', '2XL', 'XG'],
        '2XL' => ['2XL', 'XXL', 'XG'],
        'XXXL' => ['XXXL', '3XL'],
        '3XL' => ['3XL', 'XXXL'],
        'UNICO' => ['UNICO', 'ÚNICO', 'U', 'ONE SIZE', 'OS', 'OSFA'],
        'OS' => ['OS', 'OSFA', 'U', 'UNICO', 'ÚNICO', 'ONE SIZE'],
        'OSFA' => ['OSFA', 'OS', 'U', 'UNICO', 'ÚNICO', 'ONE SIZE'],
        'AJUSTAVEL' => ['AJUSTAVEL', 'AJUSTÁVEL', 'ADJUSTABLE', 'REGULABLE'],
    ];

    private static ?array $colorCatalog = null;

    private static ?array $sizeCatalog = null;

    private static ?array $keywordCatalog = null;

    public function apply(Builder $query, ?string $search): Builder
    {
        foreach ($this->descriptors($search) as $descriptor) {
            $query->where(function (Builder $termQuery) use ($descriptor) {
                if ($descriptor['type'] === 'size') {
                    $this->applySizeDescriptor($termQuery, $descriptor['sizes']);

                    return;
                }

                $this->applyTextDescriptor($termQuery, $descriptor['terms'], $descriptor['hex']);
            });
        }

        return $query;
    }

    public function applyRelevance(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);
        if ($search === '') {
            return $query;
        }

        $contains = '%' . $this->escapeLike($search) . '%';
        $starts = $this->escapeLike($search) . '%';

        return $query->orderByRaw(
            'CASE '
            . 'WHEN products.sku = ? THEN 0 '
            . 'WHEN products.sku LIKE ? THEN 1 '
            . 'WHEN products.external_name = ? OR products.name = ? THEN 2 '
            . 'WHEN products.external_name LIKE ? OR products.name LIKE ? THEN 3 '
            . 'WHEN products.external_name LIKE ? OR products.name LIKE ? THEN 4 '
            . 'ELSE 5 END',
            [$search, $starts, $search, $search, $starts, $starts, $contains, $contains]
        );
    }

    public function descriptors(?string $search): array
    {
        $normalized = Str::upper(Str::ascii(trim((string) $search)));
        $normalized = preg_replace('/(\d+(?:[.,]\d+)?)\s*(ML|MM|CM|L)\b/u', '$1$2', $normalized) ?? $normalized;
        $normalized = $this->protectCompoundTerms($normalized);
        $parts = preg_split('/[^A-Z0-9#.+_-]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return collect($parts)
            ->map(fn (string $part) => str_replace('_', ' ', trim($part, '.')))
            ->filter(fn (string $part) => $part !== '')
            ->reject(fn (string $part) => in_array(Str::lower($part), self::IGNORED_TERMS, true))
            ->take(self::MAX_DESCRIPTORS)
            ->map(fn (string $part) => $this->descriptorFor($part))
            ->unique(fn (array $descriptor) => $descriptor['type'] . ':' . implode('|', $descriptor['terms'] ?? $descriptor['sizes']))
            ->values()
            ->all();
    }

    private function descriptorFor(string $token): array
    {
        $upper = $this->normalizeLookup($token);
        $sizes = $this->sizeCatalog()[$upper] ?? null;
        if ($sizes !== null) {
            return ['type' => 'size', 'sizes' => $sizes, 'terms' => [], 'hex' => []];
        }

        if (preg_match('/^#[0-9A-F]{6}$/', $upper) === 1) {
            return ['type' => 'text', 'terms' => [$upper], 'hex' => [$upper]];
        }

        $key = Str::lower($upper);
        $color = $this->colorFor($key);
        if ($color !== null) {
            return [
                'type' => 'text',
                'terms' => $color['terms'],
                'hex' => $color['hex'],
            ];
        }

        return [
            'type' => 'text',
            'terms' => $this->textTermsFor($key),
            'hex' => [],
        ];
    }

    private function applyTextDescriptor(Builder $query, array $terms, array $hex): void
    {
        foreach (array_unique($terms) as $term) {
            $like = '%' . $this->escapeLike($term) . '%';

            $query->orWhere('products.external_name', 'like', $like)
                ->orWhere('products.name', 'like', $like)
                ->orWhere('products.sku', 'like', $like);
        }

        if ($terms !== []) {
            $this->applyTaxonomyText($query, 'brands', 'brand_id', $terms);
            $this->applyTaxonomyText($query, 'categories', 'category_id', $terms);
            $this->applyTaxonomyText($query, 'subcategories', 'subcategory_id', $terms);
            $this->applyTaxonomyText($query, 'childcategories', 'childcategory_id', $terms);

            $query->orWhereExists(function ($translationQuery) use ($terms) {
                $translationQuery->selectRaw('1')
                    ->from('product_translations as search_product_translations')
                    ->whereColumn('search_product_translations.product_id', 'products.id')
                    ->whereIn('search_product_translations.locale', ['pt-br', 'pt', 'es', 'en'])
                    ->where(function ($translatedText) use ($terms) {
                        foreach (array_unique($terms) as $term) {
                            $like = '%' . $this->escapeLike($term) . '%';
                            $translatedText->orWhere('search_product_translations.name', 'like', $like)
                                ->orWhere('search_product_translations.tags', 'like', $like);
                        }
                    });
            });

            $query->orWhereExists(function ($translationQuery) use ($terms) {
                $translationQuery->selectRaw('1')
                    ->from('category_translations as search_category_translations')
                    ->whereColumn('search_category_translations.category_id', 'products.category_id')
                    ->whereIn('search_category_translations.locale', ['pt-br', 'pt', 'es', 'en'])
                    ->where(function ($translatedText) use ($terms) {
                        foreach (array_unique($terms) as $term) {
                            $translatedText->orWhere(
                                'search_category_translations.name',
                                'like',
                                '%' . $this->escapeLike($term) . '%'
                            );
                        }
                    });
            });
        }

        if ($hex !== []) {
            // A exclusão usa apenas as famílias cromáticas curadas. Incluir aqui
            // todos os nomes auxiliares do JSON criaria uma expressão REGEXP muito
            // grande e prejudicaria consultas simples de cor.
            $otherColorPattern = collect(self::COLOR_ALIASES)
                ->flatMap(fn (array $color) => $color['terms'])
                ->map(fn (string $color) => Str::upper(Str::ascii($color)))
                ->diff(collect($terms)->map(fn (string $color) => Str::upper(Str::ascii($color))))
                ->unique()
                ->map(fn (string $color) => preg_quote($color, '/'))
                ->implode('|');

            $query->orWhere(function (Builder $colorQuery) use ($hex, $otherColorPattern) {
                $colorQuery->whereIn('products.color', $hex);
                $this->excludeConflictingColorName($colorQuery, $otherColorPattern);
            });

            if (Product::supportsMultipleColors()) {
                foreach ($hex as $color) {
                    $query->orWhere(function (Builder $colorsQuery) use ($color, $otherColorPattern) {
                        $colorsQuery->where('products.colors', 'like', '%' . $color . '%');
                        $this->excludeConflictingColorName($colorsQuery, $otherColorPattern);
                    });
                }
            }

        }
    }

    private function applyTaxonomyText(Builder $query, string $table, string $foreignKey, array $terms): void
    {
        $alias = 'search_' . $table . '_text';

        $query->orWhereExists(function ($taxonomyQuery) use ($table, $alias, $foreignKey, $terms) {
            $taxonomyQuery->selectRaw('1')
                ->from($table . ' as ' . $alias)
                ->whereColumn($alias . '.id', 'products.' . $foreignKey)
                ->where(function ($taxonomyText) use ($alias, $terms) {
                    foreach (array_unique($terms) as $term) {
                        $taxonomyText->orWhere(
                            $alias . '.name',
                            'like',
                            '%' . $this->escapeLike($term) . '%'
                        );
                    }
                });
        });
    }

    private function applySizeDescriptor(Builder $query, array $sizes): void
    {
        $query->where(function (Builder $sizeQuery) use ($sizes) {
            foreach (array_unique($sizes) as $size) {
                $sizeQuery->orWhereRaw('UPPER(TRIM(products.size)) = ?', [Str::upper($size)])
                    ->orWhere('products.external_name', 'like', '%#' . $this->escapeLike($size) . ' %')
                    ->orWhere('products.external_name', 'like', '%#' . $this->escapeLike($size) . ' *')
                    ->orWhere('products.name', 'like', '%#' . $this->escapeLike($size) . ' %');
            }
        });
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }

    private function colorFor(string $token): ?array
    {
        return $this->colorCatalog()[$this->normalizeLookup($token)] ?? null;
    }

    private function textTermsFor(string $token): array
    {
        $normalizedToken = $this->normalizeLookup($token);

        return $this->keywordCatalog()[$normalizedToken] ?? [Str::upper($token)];
    }

    private function colorCatalog(): array
    {
        if (self::$colorCatalog !== null) {
            return self::$colorCatalog;
        }

        $catalog = [];
        foreach (self::COLOR_ALIASES as $key => $color) {
            $group = [
                'terms' => array_values(array_unique($color['terms'])),
                'hex' => array_values(array_unique(array_map('strtoupper', $color['hex']))),
            ];

            foreach (array_merge([$key], $group['terms']) as $alias) {
                $catalog[$this->normalizeLookup($alias)] = $group;
            }
        }

        foreach ($this->readJsonData('color.json') as $hex => $name) {
            if (!is_string($hex) || !is_string($name) || preg_match('/^#[0-9A-F]{6}$/i', $hex) !== 1) {
                continue;
            }

            $lookup = $this->normalizeLookup($name);
            if ($lookup === '') {
                continue;
            }

            if (isset($catalog[$lookup])) {
                $catalog[$lookup]['hex'][] = strtoupper($hex);
                $catalog[$lookup]['hex'] = array_values(array_unique($catalog[$lookup]['hex']));
                continue;
            }

            $catalog[$lookup] = ['terms' => [$name], 'hex' => [strtoupper($hex)]];
        }

        return self::$colorCatalog = $catalog;
    }

    private function sizeCatalog(): array
    {
        if (self::$sizeCatalog !== null) {
            return self::$sizeCatalog;
        }

        $catalog = [];
        foreach (self::SIZE_ALIASES as $key => $sizes) {
            $normalizedSizes = array_values(array_unique(array_map(
                fn (string $size) => Str::upper(Str::ascii($size)),
                $sizes
            )));

            foreach (array_merge([$key], $normalizedSizes) as $alias) {
                $catalog[$this->normalizeLookup($alias)] = $normalizedSizes;
            }
        }

        // A grafia digitada deve prevalecer quando dois padrões são equivalentes
        // (por exemplo, GG/XL e P/S), mantendo uma ordem previsível no filtro.
        foreach (self::SIZE_ALIASES as $key => $sizes) {
            $catalog[$this->normalizeLookup($key)] = array_values(array_unique(array_map(
                fn (string $size) => Str::upper(Str::ascii($size)),
                $sizes
            )));
        }

        foreach ($this->readJsonData('tamanho.json') as $sizes) {
            if (!is_array($sizes)) {
                continue;
            }

            foreach ($sizes as $size) {
                if (!is_string($size) && !is_numeric($size)) {
                    continue;
                }

                $normalized = $this->normalizeLookup((string) $size);
                if ($normalized !== '') {
                    $catalog[$normalized] ??= [$normalized];
                }
            }
        }

        return self::$sizeCatalog = $catalog;
    }

    private function keywordCatalog(): array
    {
        if (self::$keywordCatalog !== null) {
            return self::$keywordCatalog;
        }

        $catalog = [];
        foreach (self::TEXT_ALIASES as $key => $aliases) {
            $group = array_values(array_unique(array_merge([$key], $aliases)));
            foreach ($group as $alias) {
                $catalog[$this->normalizeLookup($alias)] = $group;
            }
        }

        foreach ($this->readJsonData('product_keywords.json') as $keyword => $categoryName) {
            if (!is_string($keyword) || !is_string($categoryName)) {
                continue;
            }

            $key = $this->normalizeLookup($keyword);
            $category = $this->normalizeLookup($categoryName);
            $group = $catalog[$key] ?? $catalog[$category] ?? [$keyword, $categoryName];
            $group = array_values(array_unique(array_merge($group, [$keyword, $categoryName])));

            foreach ($group as $alias) {
                $catalog[$this->normalizeLookup($alias)] = $group;
            }
        }

        $profiles = $this->readJsonData('product_description_profiles.json');
        $profileGroups = [];
        foreach (($profiles['aliases'] ?? []) as $alias => $canonical) {
            if (is_string($alias) && is_string($canonical)) {
                $profileGroups[$this->normalizeLookup($canonical)][] = $alias;
            }
        }

        foreach ($profileGroups as $canonical => $aliases) {
            $group = $catalog[$canonical] ?? [];
            foreach ($aliases as $alias) {
                $group = array_merge($group, $catalog[$this->normalizeLookup($alias)] ?? []);
            }
            $group = array_values(array_unique(array_merge($group, [$canonical], $aliases)));
            foreach ($group as $alias) {
                $catalog[$this->normalizeLookup($alias)] = $group;
            }
        }

        return self::$keywordCatalog = $catalog;
    }

    private function protectCompoundTerms(string $search): string
    {
        $compounds = [];
        foreach (self::COLOR_ALIASES as $key => $color) {
            $compounds = array_merge($compounds, [$key], $color['terms']);
        }
        foreach (self::TEXT_ALIASES as $key => $terms) {
            $compounds = array_merge($compounds, [$key], $terms);
        }
        foreach (array_keys($this->keywordCatalog()) as $term) {
            $compounds[] = $term;
        }
        foreach (array_keys($this->sizeCatalog()) as $term) {
            $compounds[] = $term;
        }

        $compounds = collect($compounds)
            ->map(fn (string $term) => Str::upper(Str::ascii(trim($term))))
            ->filter(fn (string $term) => str_contains($term, ' '))
            ->unique()
            ->sortByDesc('strlen');

        foreach ($compounds as $compound) {
            $pattern = '/(?<![A-Z0-9])' . preg_quote($compound, '/') . '(?![A-Z0-9])/u';
            $search = preg_replace($pattern, str_replace(' ', '_', $compound), $search) ?? $search;
        }

        return $search;
    }

    private function readJsonData(string $file): array
    {
        $path = dirname(__DIR__, 2) . '/public/data/' . $file;

        if (!is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeLookup(string $value): string
    {
        return Str::upper(Str::ascii(trim($value)));
    }

    private function excludeConflictingColorName(Builder $query, string $pattern): void
    {
        if ($pattern === '') {
            return;
        }

        $query->where(function (Builder $nameQuery) use ($pattern) {
            $nameQuery->whereNull('products.name')
                ->orWhereRaw('UPPER(products.name) NOT REGEXP ?', [$pattern]);
        });
    }
}
