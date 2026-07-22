<?php

namespace App\Services;

use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DailyMostViewedProducts
{
    private const DEFAULT_LIMIT = 12;
    private const POOL_MULTIPLIER = 5;

    public function get(int $limit = self::DEFAULT_LIMIT): Collection
    {
        $limit = max(1, $limit);
        $date = now();

        return Cache::remember(
            'daily_most_viewed_products_' . $date->toDateString() . '_' . $limit,
            $date->copy()->endOfDay(),
            function () use ($limit, $date) {
                // Primeiro limita aos produtos realmente mais vistos. A ordem
                // diária é aplicada apenas dentro desse grupo qualificado.
                $products = Product::query()
                    ->where('status', 1)
                    ->where('is_outlet', false)
                    ->where('product_role', 'P')
                    ->where('stock', '>', 0)
                    ->whereNotNull('photo')
                    ->where('photo', '!=', '')
                    ->where('views', '>', 0)
                    ->with('brand')
                    ->orderByDesc('views')
                    ->limit($limit * self::POOL_MULTIPLIER)
                    ->get();

                $orderedIds = self::rotateIds(
                    $products->pluck('id')->map(fn ($id) => (int) $id)->all(),
                    $date,
                    $limit
                );

                return $orderedIds
                    ->map(fn (int $id) => $products->firstWhere('id', $id))
                    ->filter()
                    ->values();
            }
        );
    }

    /**
     * Produz uma seleção estável durante o dia e evita os itens do dia anterior
     * quando o catálogo possui produtos suficientes para uma troca completa.
     */
    public static function rotateIds(array $ids, CarbonInterface $date, int $limit): Collection
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $orderForDate = fn (Collection $items, string $seed) => $items
            ->sortBy(fn (int $id) => hash('sha256', $seed . '|' . $id), SORT_STRING)
            ->values();

        $todaySeed = $date->toDateString();
        // Divide todo o pool em dois grupos estáveis. Dias consecutivos usam
        // grupos opostos; a ordem interna continua variando pela data.
        $stableOrder = $orderForDate($ids, 'sax-most-viewed-daily-buckets-v1');
        $todayBucket = ((int) $date->format('z')) % 2;
        $freshIds = $orderForDate(
            $stableOrder->filter(fn (int $id, int $index) => $index % 2 === $todayBucket),
            $todaySeed
        )->take($limit);

        if ($freshIds->count() < $limit) {
            $freshIds = $freshIds->concat(
                $orderForDate($ids->diff($freshIds), $todaySeed)
                    ->take($limit - $freshIds->count())
            );
        }

        return $freshIds->values();
    }
}
