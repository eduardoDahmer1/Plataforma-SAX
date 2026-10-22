<?php

namespace App\Console\Commands;

use App\Services\CatalogAttributeService;
use App\Services\CatalogAttributeSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncCatalogAttributes extends Command
{
    protected $signature = 'catalog:sync-attributes';

    protected $description = 'Sincroniza el diccionario y valores ópticos mediante el peer habilitado; no modifica CEC.';

    public function handle(CatalogAttributeService $catalog, CatalogAttributeSyncService $sync): int
    {
        if (! config('catalog-sync.enabled') || ! $catalog->available()) {
            $this->info('Sincronización de atributos deshabilitada.');

            return self::SUCCESS;
        }
        $peer = DB::connection(config('catalog-sync.connection'));
        $sync->dictionary($peer);
        DB::table('product_attribute_values')->select('product_id')->distinct()->orderBy('product_id')->chunk(100, function ($rows) use ($catalog, $sync, $peer) {
            foreach ($rows as $row) {
                $product = \App\Models\Product::withoutGlobalScopes()->find($row->product_id);
                if ($product && $catalog->enabledFor($product)) {
                    $sync->product($peer, $product->id);
                }
            }
        });
        $this->info('Diccionario, traducciones y valores sincronizados.');

        return self::SUCCESS;
    }
}
