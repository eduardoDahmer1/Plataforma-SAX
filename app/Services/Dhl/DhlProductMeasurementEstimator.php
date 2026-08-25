<?php

namespace App\Services\Dhl;

use App\Models\Product;
use Illuminate\Support\Str;

final class DhlProductMeasurementEstimator
{
    public function __construct(private ?DhlMeasurementRuleResolver $rules = null)
    {
    }

    /**
     * Bases provisórias e conservadoras. Medidas reais do produto sempre prevalecem.
     *
     * @return array<string, array{label:string,weight:float,length:float,width:float,height:float}>
     */
    public static function profiles(): array
    {
        return [
            'accessory' => ['label' => 'Acessório pequeno', 'weight' => 0.300, 'length' => 18, 'width' => 12, 'height' => 5],
            'eyewear' => ['label' => 'Óculos com estojo', 'weight' => 0.350, 'length' => 18, 'width' => 9, 'height' => 7],
            'apparel_light' => ['label' => 'Roupa leve dobrada', 'weight' => 0.500, 'length' => 30, 'width' => 22, 'height' => 4],
            'apparel_heavy' => ['label' => 'Roupa pesada / casaco', 'weight' => 0.900, 'length' => 31, 'width' => 25, 'height' => 8],
            'footwear' => ['label' => 'Par de calçados', 'weight' => 1.200, 'length' => 32, 'width' => 20, 'height' => 12],
            'handbag' => ['label' => 'Bolsa / mochila compacta', 'weight' => 1.400, 'length' => 32, 'width' => 27, 'height' => 14],
            'home' => ['label' => 'Casa / item volumoso', 'weight' => 2.000, 'length' => 32, 'width' => 28, 'height' => 20],
            'kids_tshirt' => ['label' => 'Camiseta infantil dobrada', 'weight' => 0.200, 'length' => 28, 'width' => 22, 'height' => 3],
            'tshirt' => ['label' => 'Camiseta adulta dobrada', 'weight' => 0.300, 'length' => 30, 'width' => 24, 'height' => 3],
            'polo' => ['label' => 'Camisa polo dobrada', 'weight' => 0.350, 'length' => 31, 'width' => 23, 'height' => 4],
            'shirt' => ['label' => 'Camisa dobrada', 'weight' => 0.350, 'length' => 32, 'width' => 24, 'height' => 4],
            'top' => ['label' => 'Top / blusa leve dobrada', 'weight' => 0.200, 'length' => 28, 'width' => 20, 'height' => 3],
            'dress' => ['label' => 'Vestido dobrado', 'weight' => 0.550, 'length' => 32, 'width' => 24, 'height' => 6],
            'pants' => ['label' => 'Calça dobrada', 'weight' => 0.650, 'length' => 32, 'width' => 24, 'height' => 6],
            'shorts' => ['label' => 'Short / bermuda dobrado', 'weight' => 0.350, 'length' => 28, 'width' => 22, 'height' => 4],
            'skirt' => ['label' => 'Saia dobrada', 'weight' => 0.350, 'length' => 30, 'width' => 23, 'height' => 4],
            'sweater' => ['label' => 'Suéter / moletom dobrado', 'weight' => 0.650, 'length' => 35, 'width' => 28, 'height' => 8],
            'jacket' => ['label' => 'Jaqueta dobrada', 'weight' => 0.900, 'length' => 40, 'width' => 30, 'height' => 10],
            'outfit' => ['label' => 'Conjunto infantil dobrado', 'weight' => 0.600, 'length' => 32, 'width' => 24, 'height' => 7],
            'small_apparel' => ['label' => 'Meias / roupa íntima', 'weight' => 0.150, 'length' => 20, 'width' => 14, 'height' => 3],
            'kids_footwear' => ['label' => 'Calçado infantil', 'weight' => 0.800, 'length' => 28, 'width' => 18, 'height' => 10],
            'perfume' => ['label' => 'Perfume / cosmético líquido', 'weight' => 0.500, 'length' => 18, 'width' => 12, 'height' => 8],
            'wine' => ['label' => 'Vinho / espumante', 'weight' => 1.500, 'length' => 35, 'width' => 12, 'height' => 12],
            'spirits' => ['label' => 'Bebida destilada', 'weight' => 1.600, 'length' => 35, 'width' => 13, 'height' => 13],
            'aerosol' => ['label' => 'Aerossol / spray', 'weight' => 0.500, 'length' => 22, 'width' => 10, 'height' => 10],
            'battery' => ['label' => 'Produto com bateria', 'weight' => 1.000, 'length' => 25, 'width' => 18, 'height' => 10],
            'tobacco' => ['label' => 'Tabaco / charuto', 'weight' => 0.500, 'length' => 25, 'width' => 18, 'height' => 8],
            'furniture' => ['label' => 'Móvel / item extragrande', 'weight' => 15.000, 'length' => 80, 'width' => 60, 'height' => 50],
        ];
    }

    /** @return array{profile:string,label:string,weight:float,length:float,width:float,height:float,volume:float,estimated:bool,restricted:bool} */
    public function forProduct(Product $product, bool $allowFallback = true): array
    {
        $hasExactMeasurements = min(
            (float) $product->shipping_weight_kg,
            (float) $product->shipping_length_cm,
            (float) $product->shipping_width_cm,
            (float) $product->shipping_height_cm,
        ) > 0;

        if ($hasExactMeasurements) {
            return $this->measurement(
                'exact',
                'Medidas reais do cadastro',
                (float) $product->shipping_weight_kg,
                (float) $product->shipping_length_cm,
                (float) $product->shipping_width_cm,
                (float) $product->shipping_height_cm,
                false,
                (bool) $product->shipping_is_dangerous_goods,
            );
        }

        if (! $allowFallback) {
            return $this->measurement('missing', 'Sem medidas', 0, 0, 0, 0, true, true);
        }

        $profile = trim((string) $product->shipping_profile);
        $searchable = $this->searchableText($product);
        $rule = $this->rules?->forProduct($product);

        if ($profile === 'restricted') {
            if ($rule) {
                return $this->measurement(
                    (string) $rule->profile_code,
                    'Média cadastrada: '.$rule->scope_name.' · requer revisão',
                    (float) $rule->weight_kg,
                    (float) $rule->length_cm,
                    (float) $rule->width_cm,
                    (float) $rule->height_cm,
                    true,
                    true,
                );
            }

            $baseProfile = $this->inferProfile($searchable);
            $base = self::profiles()[$baseProfile];

            return $this->measurement(
                $baseProfile,
                $base['label'].' · requer revisão',
                $base['weight'],
                $base['length'],
                $base['width'],
                $base['height'],
                true,
                true,
            );
        }

        if (array_key_exists($profile, self::profiles())) {
            $base = self::profiles()[$profile];
            $requiresReview = $this->profileRequiresReview($profile);

            return $this->measurement(
                $profile,
                $base['label'].($requiresReview ? ' · requer revisão' : ''),
                $base['weight'],
                $base['length'],
                $base['width'],
                $base['height'],
                true,
                $requiresReview,
            );
        }

        $specificProfile = $this->inferSpecificProfile($this->productText($product), $searchable);
        if ($specificProfile !== null) {
            $base = self::profiles()[$specificProfile];
            $requiresReview = $this->profileRequiresReview($specificProfile);

            return $this->measurement(
                $specificProfile,
                'Média automática por tipo: '.$base['label'].($requiresReview ? ' · requer revisão' : ''),
                $base['weight'],
                $base['length'],
                $base['width'],
                $base['height'],
                true,
                $requiresReview,
            );
        }

        if ($rule) {
            $restrictedByText = $this->looksRestricted($searchable)
                && ! in_array($rule->profile_code, ['perfume', 'wine', 'spirits'], true);
            $restricted = $rule->requires_manual_review || $restrictedByText;

            return $this->measurement(
                (string) $rule->profile_code,
                'Média cadastrada: '.$rule->scope_name.($restricted ? ' · requer revisão' : ''),
                (float) $rule->weight_kg,
                (float) $rule->length_cm,
                (float) $rule->width_cm,
                (float) $rule->height_cm,
                true,
                $restricted,
            );
        }

        if ($this->looksRestricted($searchable)) {
            $restrictedProfile = $this->inferProfile($searchable);
            $base = self::profiles()[$restrictedProfile];

            return $this->measurement(
                $restrictedProfile,
                $base['label'].' · requer revisão',
                $base['weight'],
                $base['length'],
                $base['width'],
                $base['height'],
                true,
                true,
            );
        }

        $profile = $this->inferProfile($searchable);
        $base = self::profiles()[$profile];

        return $this->measurement(
            $profile,
            $base['label'],
            $base['weight'],
            $base['length'],
            $base['width'],
            $base['height'],
            true,
            false,
        );
    }

    private function searchableText(Product $product): string
    {
        $values = [
            $product->name,
            $product->external_name,
            $product->relationLoaded('category') ? $product->category?->name : null,
            $product->relationLoaded('subcategory') ? $product->subcategory?->name : null,
            $product->relationLoaded('categoriasFilhas') ? $product->categoriasFilhas?->name : null,
        ];

        return mb_strtolower(Str::ascii(implode(' ', array_filter($values))));
    }

    private function productText(Product $product): string
    {
        return mb_strtolower(Str::ascii(implode(' ', array_filter([
            $product->name,
            $product->external_name,
        ]))));
    }

    private function inferSpecificProfile(string $productText, string $searchable): ?string
    {
        $isKids = Str::contains($searchable, ['infantil', 'kids', 'kid ', 'crianca', 'nino', 'nina', 'bebe', 'baby']);

        if (Str::contains($productText, ['perfume', 'colonia'])) return 'perfume';
        if (Str::contains($productText, ['vinho', 'vino', 'champagne', 'espumante'])) return 'wine';
        if (Str::contains($productText, ['whisky', 'vodka', 'licor', 'tequila', 'bebida', 'alcool', 'alcohol'])) return 'spirits';
        if (Str::contains($productText, ['aerosol', 'spray'])) return 'aerosol';
        if (Str::contains($productText, ['bateria', 'battery'])) return 'battery';
        if (Str::contains($productText, ['habano', 'charuto', 'cigarro'])) return 'tobacco';
        if (Str::contains($productText, ['oculo', 'anteojo', 'gafa'])) return 'eyewear';
        if (Str::contains($productText, ['bota'])) return $isKids ? 'kids_footwear' : 'footwear';
        if (Str::contains($productText, ['tenis', 'sapato', 'sandalia', 'mocasin', 'sneaker', 'zapatilla', 'zapato'])) return $isKids ? 'kids_footwear' : 'footwear';
        if (Str::contains($productText, ['mochila'])) return 'handbag';
        if (Str::contains($productText, ['bolsa', 'cartera', 'maletin', 'pasta'])) return 'handbag';
        if (Str::contains($productText, ['camiseta', 't-shirt', 'tshirt', 'remera'])) return $isKids ? 'kids_tshirt' : 'tshirt';
        if (Str::contains($productText, ['camisa polo', ' polo ']) || str_starts_with($productText, 'polo ')) return 'polo';
        if (Str::contains($productText, ['camisa'])) return 'shirt';
        if (Str::contains($productText, ['vestido'])) return 'dress';
        if (Str::contains($productText, ['pantalon', 'calca', 'jeans', 'legging'])) return 'pants';
        if (Str::contains($productText, ['short', 'bermuda'])) return 'shorts';
        if (Str::contains($productText, ['falda', 'saia'])) return 'skirt';
        if (Str::contains($productText, ['chaqueta', 'jaqueta', 'casaco', 'blazer'])) return 'jacket';
        if (Str::contains($productText, ['sueter', 'sweater', 'buzo', 'moleton', 'moletom', 'cardigan'])) return 'sweater';
        if (Str::contains($productText, ['conjunto', 'macacon', 'overall'])) return 'outfit';
        if (Str::contains($productText, ['meia', 'media ', 'calcetin', 'cueca', 'calcinha'])) return 'small_apparel';
        if (Str::contains($productText, [' top ', 'blusa']) || str_starts_with($productText, 'top ')) return 'top';
        if (Str::contains($productText, ['movel', 'mueble', 'sofa', 'mesa grande', 'armario'])) return 'furniture';

        return null;
    }

    private function looksRestricted(string $text): bool
    {
        return Str::contains($text, [
            'perfume', 'colonia', 'aerosol', 'spray', 'alcool', 'alcohol', 'vinho', 'vino',
            'champagne', 'whisky', 'vodka', 'licor', 'tequila', 'espumante', 'bebida',
            'habanos', 'charuto', 'cigarro', 'bateria', 'battery',
        ]);
    }

    private function inferProfile(string $text): string
    {
        if (Str::contains($text, ['perfume', 'colonia'])) {
            return 'perfume';
        }

        if (Str::contains($text, ['vinho', 'vino', 'champagne', 'espumante'])) {
            return 'wine';
        }

        if (Str::contains($text, ['whisky', 'vodka', 'licor', 'tequila', 'bebida', 'alcool', 'alcohol'])) {
            return 'spirits';
        }

        if (Str::contains($text, ['aerosol', 'spray'])) {
            return 'aerosol';
        }

        if (Str::contains($text, ['bateria', 'battery'])) {
            return 'battery';
        }

        if (Str::contains($text, ['habano', 'charuto', 'cigarro'])) {
            return 'tobacco';
        }

        if (Str::contains($text, ['movel', 'mueble', 'sofa', 'mesa grande', 'armario'])) {
            return 'furniture';
        }

        if (Str::contains($text, ['oculo', 'gafa', 'lente'])) {
            return 'eyewear';
        }

        if (Str::contains($text, ['calcado', 'tenis', 'sapato', 'sandalia', 'bota', 'mocasin', 'sneaker'])) {
            return 'footwear';
        }

        if (Str::contains($text, ['bolsa', 'mochila', 'pasta', 'maletin'])) {
            return 'handbag';
        }

        if (Str::contains($text, ['casa', 'decoracao', 'decoracion', 'cozinha', 'cocina', 'eletrodomestico'])) {
            return 'home';
        }

        if (Str::contains($text, ['casaco', 'jaqueta', 'moleton', 'blazer', 'terno', 'jeans', 'calca'])) {
            return 'apparel_heavy';
        }

        if (Str::contains($text, ['camisa', 'camiseta', 'polo', 'vestido', 'roupa', 'short', 'bermuda', 'infantil'])) {
            return 'apparel_light';
        }

        return 'accessory';
    }

    private function profileRequiresReview(string $profile): bool
    {
        return in_array($profile, ['perfume', 'wine', 'spirits', 'aerosol', 'battery', 'tobacco', 'furniture'], true);
    }

    /** @return array{profile:string,label:string,weight:float,length:float,width:float,height:float,volume:float,estimated:bool,restricted:bool} */
    private function measurement(
        string $profile,
        string $label,
        float $weight,
        float $length,
        float $width,
        float $height,
        bool $estimated,
        bool $restricted,
    ): array {
        return compact('profile', 'label', 'weight', 'length', 'width', 'height', 'estimated', 'restricted') + [
            'volume' => $length * $width * $height,
        ];
    }
}
