<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait UsesProductAiDatabase
{
    private string $aiTestStorage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->aiTestStorage = sys_get_temp_dir().'/sax-ai-tests-'.bin2hex(random_bytes(8));
        $this->app->useStoragePath($this->aiTestStorage);

        // Isolate these tests from historical MySQL-only catalog migrations.
        (require database_path('migrations/2014_10_12_000000_create_users_table.php'))->up();
        Schema::table('users', fn (Blueprint $table) => $table->integer('user_type')->default(0));
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            foreach (['sku', 'name', 'external_name', 'ref_code', 'photo', 'gallery', 'description'] as $column) {
                $table->text($column)->nullable();
            }
            foreach (['brand_id', 'category_id', 'subcategory_id', 'parent_id', 'stock'] as $column) {
                $table->integer($column)->nullable();
            }
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('status')->default(false);
            $table->boolean('is_outlet')->default(false);
            $table->timestamps();
        });
        foreach (['brands', 'categories', 'subcategories'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->integer('category_id')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }
        foreach ([
            '2025_08_25_160934_create_system_settings_table.php',
            '2026_08_13_100000_create_product_ai_preparations_table.php',
            '2026_08_14_100000_create_product_ai_batches_tables.php',
            '2026_09_09_120000_add_ai_controls_to_system_settings.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->aiTestStorage)) {
            \Illuminate\Support\Facades\File::deleteDirectory($this->aiTestStorage);
        }
        parent::tearDown();
    }
}
