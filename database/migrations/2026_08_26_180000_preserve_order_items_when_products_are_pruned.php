<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $snapshotColumns = array_filter(
            ['name', 'external_name', 'slug', 'sku'],
            fn (string $column): bool => Schema::hasColumn('order_items', $column)
                && Schema::hasColumn('products', $column)
        );

        DB::table('order_items')
            ->whereNotNull('product_id')
            ->orderBy('id')
            ->chunkById(500, function ($items) use ($snapshotColumns): void {
                $products = DB::table('products')
                    ->whereIn('id', $items->pluck('product_id')->filter()->unique())
                    ->get()
                    ->keyBy('id');

                foreach ($items as $item) {
                    $product = $products->get($item->product_id);
                    if (! $product) {
                        continue;
                    }

                    $updates = [];
                    foreach ($snapshotColumns as $column) {
                        if (blank($item->{$column} ?? null) && filled($product->{$column} ?? null)) {
                            $updates[$column] = $product->{$column};
                        }
                    }
                    if ($updates !== []) {
                        DB::table('order_items')->where('id', $item->id)->update($updates);
                    }
                }
            });

        $this->replaceProductForeignKey('SET NULL');
    }

    public function down(): void
    {
        // A coluna permanece nullable porque pedidos podem apontar para um
        // produto já removido. Restaurar CASCADE não exige apagar o histórico.
        $this->replaceProductForeignKey('CASCADE');
    }

    private function replaceProductForeignKey(string $deleteRule): void
    {
        if (DB::getDriverName() !== 'mysql') {
            Schema::table('order_items', function (Blueprint $table) use ($deleteRule): void {
                $table->dropForeign(['product_id']);
                $table->unsignedBigInteger('product_id')->nullable()->change();
                $foreign = $table->foreign('product_id')->references('id')->on('products');
                $deleteRule === 'SET NULL' ? $foreign->nullOnDelete() : $foreign->cascadeOnDelete();
            });
            return;
        }

        $database = DB::getDatabaseName();
        $constraint = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('CONSTRAINT_SCHEMA', $database)
            ->where('TABLE_NAME', 'order_items')
            ->where('COLUMN_NAME', 'product_id')
            ->where('REFERENCED_TABLE_NAME', 'products')
            ->value('CONSTRAINT_NAME');

        if ($constraint && preg_match('/^[A-Za-z0-9_]+$/', $constraint)) {
            DB::statement("ALTER TABLE `order_items` DROP FOREIGN KEY `{$constraint}`");
        }

        DB::statement('ALTER TABLE `order_items` MODIFY `product_id` BIGINT UNSIGNED NULL');

        // Pode haver referências órfãs deixadas por uma limpeza manual feita
        // com a chave estrangeira desabilitada/removida. Elas precisam virar
        // NULL antes que o MySQL permita recriar a restrição.
        DB::statement(
            'UPDATE `order_items` AS oi '
            .'LEFT JOIN `products` AS p ON p.`id` = oi.`product_id` '
            .'SET oi.`product_id` = NULL '
            .'WHERE oi.`product_id` IS NOT NULL AND p.`id` IS NULL'
        );

        DB::statement(
            'ALTER TABLE `order_items` ADD CONSTRAINT `order_items_product_id_foreign` '
            .'FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE '.$deleteRule
        );
    }
};
