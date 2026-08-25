<?php

namespace App\Services\Dhl;

use App\Models\DhlMeasurementRule;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

final class DhlMeasurementRuleResolver
{
    public function forProduct(Product $product): ?DhlMeasurementRule
    {
        $rules = Cache::remember(DhlMeasurementRule::CACHE_KEY, now()->addMinutes(10), fn () =>
            DhlMeasurementRule::query()->where('active', true)->get()->keyBy('hierarchy_key')
        );

        $keys = array_filter([
            $product->childcategory_id ? 'childcategory:'.(int) $product->childcategory_id : null,
            $product->subcategory_id ? 'subcategory:'.(int) $product->subcategory_id : null,
            $product->category_id ? 'category:'.(int) $product->category_id : null,
        ]);

        foreach ($keys as $key) {
            if ($rules->has($key)) {
                return $rules->get($key);
            }
        }

        return null;
    }

    public function clearCache(): void
    {
        Cache::forget(DhlMeasurementRule::CACHE_KEY);
    }
}
