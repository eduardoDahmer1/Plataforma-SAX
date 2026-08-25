<?php

namespace App\Services\Dhl;

use App\Models\DhlMeasurementRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class DhlMeasurementRuleDefaults
{
    /** @return array{profile_code:string,weight_kg:float,length_cm:float,width_cm:float,height_cm:float,requires_manual_review:bool} */
    public function recommend(string $text): array
    {
        $text = mb_strtolower(Str::ascii($text));

        if (Str::contains($text, ['perfume', 'perfumeria', 'colonia'])) {
            return $this->values('perfume', .500, 18, 12, 8, true);
        }
        if (Str::contains($text, ['vinho', 'vino', 'champagne', 'espumante'])) {
            return $this->values('wine', 1.500, 35, 12, 12, true);
        }
        if (Str::contains($text, ['whisky', 'vodka', 'licor', 'tequila', 'bebida'])) {
            return $this->values('spirits', 1.600, 35, 13, 13, true);
        }
        if (Str::contains($text, ['movel', 'mueble', 'sofa', 'mesa grande', 'armario'])) {
            return $this->values('furniture', 15, 80, 60, 50, true);
        }
        if (Str::contains($text, ['oculo', 'anteojo', 'gafa', 'lente', 'optico'])) {
            return $this->values('eyewear', .350, 18, 9, 7);
        }
        if (Str::contains($text, ['bota'])) {
            return $this->values('boots', 1.800, 38, 30, 14);
        }
        if (Str::contains($text, ['calcado', 'tenis', 'sapato', 'sandalia', 'mocasin', 'zapatilla', 'zapato'])) {
            return $this->values('footwear', 1.200, 32, 20, 12);
        }
        if (Str::contains($text, ['mochila'])) {
            return $this->values('backpack', 1.000, 45, 32, 15);
        }
        if (Str::contains($text, ['bolsa', 'maletin', 'pasta'])) {
            return $this->values('handbag', 1.200, 35, 28, 15);
        }
        if (Str::contains($text, ['carteira', 'billetera', 'cinto', 'cinturon', 'acessorio', 'accesorio'])) {
            return $this->values('accessory', .350, 20, 15, 6);
        }
        if (Str::contains($text, ['calca', 'pantalon', 'jeans'])) {
            return $this->values('pants', .750, 32, 24, 6);
        }
        if (Str::contains($text, ['casaco', 'jaqueta', 'chaqueta', 'sueter', 'moleton', 'blazer', 'terno'])) {
            return $this->values('apparel_heavy', .950, 35, 28, 10);
        }
        if (Str::contains($text, ['vestido'])) {
            return $this->values('dress', .600, 32, 24, 6);
        }
        if (Str::contains($text, ['camiseta', 'camisa', 'polo', 'blusa', 'top', 'roupa', 'ropa', 'short', 'bermuda', 'fitness'])) {
            return $this->values('apparel_light', .450, 30, 22, 4);
        }
        if (Str::contains($text, ['meia', 'cueca', 'calcetin'])) {
            return $this->values('apparel_small', .250, 22, 16, 4);
        }
        if (Str::contains($text, ['prato', 'plato', 'copa', 'vaso', 'taza', 'decoracao', 'decoracion', 'cozinha', 'cocina', 'casa'])) {
            return $this->values('home', 1.500, 35, 30, 20);
        }
        if (Str::contains($text, ['brinquedo', 'juguete'])) {
            return $this->values('toy', 1.000, 35, 25, 15);
        }
        if (Str::contains($text, ['feminino', 'masculino', 'infantil', 'unisex'])) {
            return $this->values('apparel_light', .500, 32, 24, 6);
        }

        return $this->values('accessory', .500, 25, 18, 8);
    }

    public function sync(): int
    {
        if (! Schema::hasTable('dhl_measurement_rules')) {
            return 0;
        }

        $now = now();
        $rows = [];
        $categoryIds = DB::table('products')->whereNotNull('category_id')->distinct()->pluck('category_id');
        $categories = DB::table('categories')->whereIn('id', $categoryIds)->get()->keyBy('id');

        foreach ($categories as $category) {
            $rows[] = $this->row('category', (int) $category->id, (int) $category->id, null, null, (string) $category->name, (string) $category->name, $now);
        }

        $subcategoryIds = DB::table('products')->whereNotNull('subcategory_id')->distinct()->pluck('subcategory_id');
        $subcategories = DB::table('subcategories')->whereIn('id', $subcategoryIds)->get();
        foreach ($subcategories as $subcategory) {
            $categoryName = (string) ($categories->get($subcategory->category_id)->name ?? '');
            $scopeName = trim($categoryName.' › '.$subcategory->name, ' ›');
            $rows[] = $this->row('subcategory', (int) $subcategory->id, (int) $subcategory->category_id, (int) $subcategory->id, null, $scopeName, $scopeName, $now);
        }

        $childIds = DB::table('products')->whereNotNull('childcategory_id')->distinct()->pluck('childcategory_id');
        $children = DB::table('childcategories')->whereIn('id', $childIds)->get();
        $subcategoryMap = DB::table('subcategories')->whereIn('id', $children->pluck('subcategory_id'))->get()->keyBy('id');
        foreach ($children as $child) {
            $subcategory = $subcategoryMap->get($child->subcategory_id);
            $categoryName = (string) ($categories->get($child->category_id)->name ?? '');
            $scopeName = collect([$categoryName, $subcategory?->name, $child->name])->filter()->implode(' › ');
            $rows[] = $this->row('childcategory', (int) $child->id, (int) $child->category_id, (int) $child->subcategory_id, (int) $child->id, $scopeName, $scopeName, $now);
        }

        $created = 0;
        foreach ($rows as $row) {
            $created += DB::table('dhl_measurement_rules')->insertOrIgnore($row);
        }

        return $created;
    }

    private function row(string $level, int $scopeId, int $categoryId, ?int $subcategoryId, ?int $childcategoryId, string $scopeName, string $searchText, mixed $now): array
    {
        return [
            'hierarchy_key' => $level.':'.$scopeId,
            'level' => $level,
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
            'childcategory_id' => $childcategoryId,
            'scope_name' => $scopeName,
            ...$this->recommend($searchText),
            'active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function values(string $profileCode, float $weight, float $length, float $width, float $height, bool $manualReview = false): array
    {
        return [
            'profile_code' => $profileCode,
            'weight_kg' => $weight,
            'length_cm' => $length,
            'width_cm' => $width,
            'height_cm' => $height,
            'requires_manual_review' => $manualReview,
        ];
    }
}
