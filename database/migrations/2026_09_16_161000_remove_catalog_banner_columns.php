<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropExistingColumns('categories', ['banner']);
        $this->dropExistingColumns('subcategories', ['banner']);
        $this->dropExistingColumns('childcategories', ['banner']);
        $this->dropExistingColumns('brands', ['banner', 'internal_banner']);
        $this->dropExistingColumns('attributes', [
            'banner10',
            'banner10_link',
            'banner_horizontal',
            'banner_horizontal_link',
        ]);
    }

    public function down(): void
    {
        if (Schema::hasTable('categories') && ! Schema::hasColumn('categories', 'banner')) {
            Schema::table('categories', fn (Blueprint $table) => $table->string('banner', 255)->nullable());
        }

        if (Schema::hasTable('subcategories') && ! Schema::hasColumn('subcategories', 'banner')) {
            Schema::table('subcategories', fn (Blueprint $table) => $table->string('banner', 255)->nullable());
        }

        if (Schema::hasTable('childcategories') && ! Schema::hasColumn('childcategories', 'banner')) {
            Schema::table('childcategories', fn (Blueprint $table) => $table->string('banner', 255)->nullable());
        }

        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                if (! Schema::hasColumn('brands', 'banner')) {
                    $table->string('banner', 255)->nullable();
                }
                if (! Schema::hasColumn('brands', 'internal_banner')) {
                    $table->string('internal_banner')->nullable();
                }
            });
        }

        if (Schema::hasTable('attributes')) {
            Schema::table('attributes', function (Blueprint $table) {
                if (! Schema::hasColumn('attributes', 'banner10')) {
                    $table->string('banner10')->nullable();
                }
                if (! Schema::hasColumn('attributes', 'banner10_link')) {
                    $table->string('banner10_link', 255)->nullable();
                }
                if (! Schema::hasColumn('attributes', 'banner_horizontal')) {
                    $table->string('banner_horizontal')->nullable();
                }
                if (! Schema::hasColumn('attributes', 'banner_horizontal_link')) {
                    $table->text('banner_horizontal_link')->nullable();
                }
            });
        }
    }

    private function dropExistingColumns(string $tableName, array $columns): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $existingColumns = array_values(array_filter(
            $columns,
            fn (string $column) => Schema::hasColumn($tableName, $column)
        ));

        if ($existingColumns === []) {
            return;
        }

        Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn($existingColumns));
    }
};
