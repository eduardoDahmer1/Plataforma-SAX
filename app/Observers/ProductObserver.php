<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\AdminNotificationService;
use App\Services\ProductFeedRefreshService;

class ProductObserver
{
    private const LOW_STOCK_LIMIT = 5;

    private const FEED_FIELDS = [
        'status', 'stock', 'price', 'is_outlet', 'photo', 'name', 'external_name',
        'description', 'slug', 'brand_id', 'category_id', 'subcategory_id',
        'childcategory_id', 'product_role', 'parent_id', 'ref_code', 'gtin', 'mpn',
    ];

    public function __construct(
        private AdminNotificationService $notifications,
        private ProductFeedRefreshService $feedRefresh,
    ) {}

    public function created(Product $product): void
    {
        $this->feedRefresh->markDirty();
    }

    public function updated(Product $product): void
    {
        if ($product->wasChanged(self::FEED_FIELDS)) {
            $this->feedRefresh->markDirty();
        }

        if (! $product->wasChanged('stock')) {
            return;
        }

        $previousStock = (int) $product->getOriginal('stock');
        $currentStock = (int) $product->stock;
        $name = $product->external_name ?: $product->name ?: "Produto #{$product->getKey()}";
        $url = "/admin/products/{$product->getKey()}/edit";
        $data = [
            'product_id' => $product->getKey(),
            'stock' => $currentStock,
            'translation_params' => ['product' => $name, 'stock' => $currentStock],
        ];

        if ($previousStock > 0 && $currentStock <= 0) {
            $this->notifications->notifyAdmins('out_of_stock', 'Produto sem estoque', "{$name} ficou sem estoque.", $url, $data);
            return;
        }

        if ($previousStock > self::LOW_STOCK_LIMIT && $currentStock > 0 && $currentStock <= self::LOW_STOCK_LIMIT) {
            $this->notifications->notifyAdmins('low_stock', 'Estoque baixo', "{$name} está com apenas {$currentStock} unidade(s).", $url, $data);
        }
    }

    public function deleted(Product $product): void
    {
        $this->feedRefresh->markDirty();
    }
}
