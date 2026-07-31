<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\AdminNotificationService;

class ProductObserver
{
    private const LOW_STOCK_LIMIT = 5;

    public function __construct(private AdminNotificationService $notifications) {}

    public function updated(Product $product): void
    {
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
}
