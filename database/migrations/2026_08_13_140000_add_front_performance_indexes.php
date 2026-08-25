<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('orders', ['user_id', 'created_at'], 'orders_user_created_at_index');
        $this->addIndex('product_views_history', ['user_id', 'updated_at'], 'product_views_user_updated_index');
        $this->addIndex('carts', ['user_id', 'product_id'], 'carts_user_product_index');
        $this->addIndex('products', ['category_id', 'status', 'is_outlet', 'stock'], 'products_category_catalog_index');
        $this->addIndex('products', ['brand_id', 'status', 'is_outlet', 'stock'], 'products_brand_catalog_index');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('orders', 'orders_user_created_at_index');
        $this->dropIndexIfExists('product_views_history', 'product_views_user_updated_index');
        $this->dropIndexIfExists('carts', 'carts_user_product_index');
        $this->dropIndexIfExists('products', 'products_category_catalog_index');
        $this->dropIndexIfExists('products', 'products_brand_catalog_index');
    }

    private function addIndex(string $table, array $columns, string $name): void
    {
        if (! $this->indexExists($table, $name)) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
        }
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        if ($this->indexExists($table, $name)) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
        }
    }

    private function indexExists(string $table, string $name): bool
    {
        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('index_name', $name)
            ->exists();
    }
};
