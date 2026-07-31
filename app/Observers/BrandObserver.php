<?php

namespace App\Observers;

use App\Models\Brand;
use App\Services\CustomerNotificationService;

class BrandObserver
{
    public function __construct(private CustomerNotificationService $notifications) {}

    public function created(Brand $brand): void
    {
        if ((int) $brand->status === 1) $this->notify($brand);
    }

    public function updated(Brand $brand): void
    {
        if ($brand->wasChanged('status') && (int) $brand->status === 1) $this->notify($brand);
    }

    private function notify(Brand $brand): void
    {
        $this->notifications->notifyCustomers('new_brand', 'Nova marca disponível', "A marca {$brand->name} chegou à SAX.", "/marcas/{$brand->slug}", [
            'brand_id' => $brand->id,
            'translation_params' => ['brand' => $brand->name],
        ]);
    }
}
