<?php

namespace App\Observers;

use App\Services\ProductFeedRefreshService;
use Illuminate\Database\Eloquent\Model;

class CatalogFeedTaxonomyObserver
{
    public function __construct(private ProductFeedRefreshService $feedRefresh)
    {
    }

    public function saved(Model $model): void
    {
        $this->feedRefresh->markDirty();
    }

    public function deleted(Model $model): void
    {
        $this->feedRefresh->markDirty();
    }
}
