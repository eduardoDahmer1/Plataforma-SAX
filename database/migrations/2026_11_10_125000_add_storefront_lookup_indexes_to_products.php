<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->indexExists('products_slug_index')) {
            // products.slug is TEXT, but every current value is shorter than
            // 191 characters. A prefix index keeps the migration non-invasive.
            DB::statement('CREATE INDEX products_slug_index ON products (slug(191))');
        }

        if (! $this->indexExists('products_color_parent_id_index')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->index('color_parent_id', 'products_color_parent_id_index');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('products_slug_index')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropIndex('products_slug_index');
            });
        }

        if ($this->indexExists('products_color_parent_id_index')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropIndex('products_color_parent_id_index');
            });
        }
    }

    private function indexExists(string $name): bool
    {
        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', 'products')
            ->where('index_name', $name)
            ->exists();
    }
};
