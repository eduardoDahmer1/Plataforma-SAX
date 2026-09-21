<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class OpticalProductSyncService
{
    private array $columnCache = [];

    public function enabled(): bool
    {
        return (bool) config('catalog-sync.enabled')
            && filled(config('database.connections.'.config('catalog-sync.connection').'.database'));
    }

    public function syncFamily(int $productId): int
    {
        if (! $this->enabled()) {
            return 0;
        }

        $seed = Product::withoutGlobalScopes()->with('category')->find($productId);
        if (! $seed || ! $this->isOptical($seed)) {
            return 0;
        }

        $products = $this->familyProducts($seed);
        $peer = DB::connection((string) config('catalog-sync.connection'));

        $peer->transaction(function () use ($peer, $products): void {
            foreach ($products as $product) {
                $this->syncReferences($peer, $product);
                $this->syncProduct($peer, $product);
            }
        });

        foreach ($products as $product) {
            $this->copyProductMedia($product);
        }

        Log::info('Optical product family synchronized with peer storefront.', [
            'product_id' => $productId,
            'product_ids' => $products->pluck('id')->all(),
            'peer_database' => config('database.connections.'.config('catalog-sync.connection').'.database'),
        ]);

        return $products->count();
    }

    public function isOptical(Product $product): bool
    {
        $category = $product->relationLoaded('category') ? $product->category : $product->category()->first();
        if (! $category) {
            return false;
        }

        $refCode = trim((string) $category->getAttribute('ref_code'));
        $slug = strtolower(trim((string) $category->getAttribute('slug')));

        return $refCode === (string) config('catalog-sync.optical_category_ref_code')
            || in_array($slug, (array) config('catalog-sync.optical_category_slugs', []), true);
    }

    private function familyProducts(Product $seed): Collection
    {
        $ids = collect([$seed->id, $seed->parent_id, $seed->color_parent_id])
            ->filter()->map(fn ($id) => (int) $id)->unique()->values();

        // Two expansions cover a color-family root, its anchors and every size
        // variant even when the edited row itself is a child.
        for ($pass = 0; $pass < 2; $pass++) {
            $related = Product::withoutGlobalScopes()
                ->where(function ($query) use ($ids): void {
                    $query->whereIn('id', $ids)
                        ->orWhereIn('parent_id', $ids)
                        ->orWhereIn('color_parent_id', $ids);
                })
                ->get(['id', 'parent_id', 'color_parent_id']);

            $ids = $ids->merge($related->pluck('id'))
                ->merge($related->pluck('parent_id'))
                ->merge($related->pluck('color_parent_id'))
                ->filter()->map(fn ($id) => (int) $id)->unique()->values();
        }

        return Product::withoutGlobalScopes()
            ->with(['translations', 'category'])
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get()
            ->filter(fn (Product $product) => $this->isOptical($product))
            ->values();
    }

    private function syncReferences(ConnectionInterface $peer, Product $product): void
    {
        $this->copyReferenceRow($peer, 'categories', (int) $product->category_id);
        $this->copyDependentRows($peer, 'category_translations', 'category_id', (int) $product->category_id);
        $this->copyReferenceRow($peer, 'brands', (int) $product->brand_id);
        $this->copyReferenceRow($peer, 'subcategories', (int) $product->subcategory_id);
        $this->copyReferenceRow($peer, 'childcategories', (int) $product->childcategory_id);
    }

    private function syncProduct(ConnectionInterface $peer, Product $product): void
    {
        $attributes = $this->sharedAttributes('products', $product->getAttributes());
        // Administrative edit metadata belongs to the storefront where the
        // edit happened. Mirroring the timestamp would credit peer edits to
        // that storefront's previous local editor.
        unset($attributes['updated_by'], $attributes['admin_edited_at']);

        $peer->table('products')->updateOrInsert(['id' => $product->id], $attributes);
        $this->replaceDependentRows($peer, 'product_translations', 'product_id', $product->id);

        foreach (['galleries', 'product_store', 'pickup_product'] as $table) {
            $this->replaceDependentRows($peer, $table, 'product_id', $product->id);
        }
    }

    private function copyReferenceRow(ConnectionInterface $peer, string $table, int $id): void
    {
        if ($id <= 0 || ! $this->tableExists(null, $table) || ! $this->tableExists($peer, $table)) {
            return;
        }

        $row = DB::table($table)->where('id', $id)->first();
        if (! $row) {
            return;
        }

        $peer->table($table)->updateOrInsert(['id' => $id], $this->sharedAttributes($table, (array) $row));
    }

    private function replaceDependentRows(ConnectionInterface $peer, string $table, string $foreignKey, int $ownerId): void
    {
        if (! $this->tableExists(null, $table) || ! $this->tableExists($peer, $table)) {
            return;
        }

        $rows = DB::table($table)->where($foreignKey, $ownerId)->get();
        $peer->table($table)->where($foreignKey, $ownerId)->delete();

        foreach ($rows as $row) {
            $attributes = $this->sharedAttributes($table, (array) $row);
            unset($attributes['id']);
            $peer->table($table)->insert($attributes);
        }
    }

    private function copyDependentRows(ConnectionInterface $peer, string $table, string $foreignKey, int $ownerId): void
    {
        if ($ownerId <= 0 || ! $this->tableExists(null, $table) || ! $this->tableExists($peer, $table)) {
            return;
        }

        $rows = DB::table($table)->where($foreignKey, $ownerId)->get();
        foreach ($rows as $row) {
            $attributes = $this->sharedAttributes($table, (array) $row);
            if (isset($attributes['id'])) {
                $peer->table($table)->updateOrInsert(['id' => $attributes['id']], $attributes);
            }
        }
    }

    private function sharedAttributes(string $table, array $attributes): array
    {
        $sourceColumns = $this->columns(null, $table);
        $peerColumns = $this->columns(DB::connection((string) config('catalog-sync.connection')), $table);
        $shared = array_flip(array_intersect($sourceColumns, $peerColumns));

        return array_intersect_key($attributes, $shared);
    }

    private function columns(?ConnectionInterface $connection, string $table): array
    {
        $key = ($connection ? 'peer:' : 'source:').$table;
        if (isset($this->columnCache[$key])) {
            return $this->columnCache[$key];
        }

        $schema = $connection ? $connection->getSchemaBuilder() : Schema::getFacadeRoot();

        return $this->columnCache[$key] = $schema->getColumnListing($table);
    }

    private function tableExists(?ConnectionInterface $connection, string $table): bool
    {
        try {
            $schema = $connection ? $connection->getSchemaBuilder() : Schema::getFacadeRoot();
            return $schema->hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    private function copyProductMedia(Product $product): void
    {
        $peerRoot = trim((string) config('catalog-sync.peer_storage_path'));
        if ($peerRoot === '') {
            return;
        }

        $paths = collect([$product->getRawOriginal('photo'), $product->getRawOriginal('thumbnail')]);
        $gallery = $product->getRawOriginal('gallery');
        $gallery = is_array($gallery) ? $gallery : json_decode((string) $gallery, true);
        if (is_array($gallery)) {
            $paths = $paths->merge($gallery);
        }

        foreach ($paths->filter()->unique() as $relativePath) {
            $relativePath = ltrim(str_replace('\\', '/', (string) $relativePath), '/');
            if ($relativePath === '' || Str::contains($relativePath, ['../', '/..'])) {
                continue;
            }

            $source = Storage::disk('public')->path($relativePath);
            if (! is_file($source)) {
                continue;
            }

            $destination = rtrim($peerRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$relativePath;
            $directory = dirname($destination);
            if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
                throw new RuntimeException("Could not create peer media directory: {$directory}");
            }
            if (! copy($source, $destination)) {
                throw new RuntimeException("Could not copy product media to peer storefront: {$relativePath}");
            }
        }
    }
}
