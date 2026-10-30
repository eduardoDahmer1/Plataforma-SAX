<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\Dhl\DhlCustomsProductData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DhlEnglishDescriptionFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->decimal('price', 10, 2)->default(10);
            $table->timestamps();
        });

        Schema::create('product_translations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('locale', 10);
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_filters_products_by_the_english_catalog_description_used_by_dhl(): void
    {
        foreach (range(1, 5) as $productId) {
            DB::table('products')->insert([
                'id' => $productId,
                'price' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('product_translations')->insert([
            $this->translation(1, 'en', '<p>Crystal wine glass</p>'),
            $this->translation(2, 'en', null),
            $this->translation(3, 'en', ''),
            $this->translation(4, 'en', "\n<p><br></p>\r"),
            $this->translation(5, 'es', '<p>Copa de cristal</p>'),
        ]);

        $service = new DhlCustomsProductData;
        $withDescription = Product::withoutGlobalScopes();
        $service->applyEnglishDescriptionFilter($withDescription, 'with_description');
        $withoutDescription = Product::withoutGlobalScopes();
        $service->applyEnglishDescriptionFilter($withoutDescription, 'without_description');

        $this->assertSame([1], $withDescription->orderBy('id')->pluck('id')->all());
        $this->assertSame([2, 3, 4, 5], $withoutDescription->orderBy('id')->pluck('id')->all());
    }

    /** @return array<string, mixed> */
    private function translation(int $productId, string $locale, ?string $details): array
    {
        return [
            'product_id' => $productId,
            'locale' => $locale,
            'details' => $details,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
