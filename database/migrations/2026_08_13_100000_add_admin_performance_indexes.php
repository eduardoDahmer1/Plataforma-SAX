<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('orders', ['created_at', 'id'], 'orders_created_at_id_index');
        $this->addIndex('orders', ['status', 'created_at'], 'orders_status_created_at_index');
        $this->addIndex('orders', ['payment_status', 'created_at'], 'orders_payment_status_created_at_index');
        $this->addIndex('orders', ['payment_method', 'created_at'], 'orders_payment_method_created_at_index');

        $this->addIndex('products', ['status', 'stock'], 'products_status_stock_index');
        $this->addIndex('products', ['status', 'created_at'], 'products_status_created_at_index');
        $this->addIndex('products', ['is_outlet', 'status'], 'products_outlet_status_index');

        $this->addIndex('users', ['user_type', 'created_at'], 'users_type_created_at_index');
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_created_at_id_index');
            $table->dropIndex('orders_status_created_at_index');
            $table->dropIndex('orders_payment_status_created_at_index');
            $table->dropIndex('orders_payment_method_created_at_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_stock_index');
            $table->dropIndex('products_status_created_at_index');
            $table->dropIndex('products_outlet_status_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_type_created_at_index');
        });
    }

    private function addIndex(string $table, array $columns, string $name): void
    {
        $exists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('index_name', $name)
            ->exists();

        if (! $exists) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
        }
    }
};
