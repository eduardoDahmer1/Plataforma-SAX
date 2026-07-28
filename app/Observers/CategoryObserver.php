<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\CustomerNotificationService;

class CategoryObserver
{
    public function __construct(private CustomerNotificationService $notifications) {}

    public function created(Category $category): void
    {
        if ((int) $category->status === 1) $this->notify($category);
    }

    public function updated(Category $category): void
    {
        if ($category->wasChanged('status') && (int) $category->status === 1) $this->notify($category);
    }

    private function notify(Category $category): void
    {
        $this->notifications->notifyCustomers('new_category', 'Nova categoria disponível', "Conheça a categoria {$category->name}.", "/categorias/{$category->slug}", ['category_id' => $category->id]);
    }
}
