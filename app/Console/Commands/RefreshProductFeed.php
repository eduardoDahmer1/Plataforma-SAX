<?php

namespace App\Console\Commands;

use App\Services\ProductFeedRefreshService;
use Illuminate\Console\Command;

class RefreshProductFeed extends Command
{
    protected $signature = 'products:refresh-feed {--force : Rebuild even when no product change is pending}';

    protected $description = 'Atualiza o XML público quando o catálogo de produtos foi alterado';

    public function handle(ProductFeedRefreshService $refresh): int
    {
        $updated = $refresh->refreshPending($this->option('force'));
        $this->components->info($updated ? 'Feed XML atualizado.' : 'Feed XML já está atualizado.');

        return self::SUCCESS;
    }
}
