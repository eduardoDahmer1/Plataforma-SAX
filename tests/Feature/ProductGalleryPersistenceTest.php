<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ProductControllerAdmin;
use App\Models\Product;
use App\Services\ImageConverterService;
use App\Services\ProductRelationshipSearch;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\Support\UsesProductAiDatabase;
use Tests\TestCase;

class ProductGalleryPersistenceTest extends TestCase
{
    use UsesProductAiDatabase { setUp as setUpDatabase; }

    private $productEvents;

    protected function setUp(): void
    {
        $this->setUpDatabase();
        $this->productEvents = Product::getEventDispatcher();
        Product::unsetEventDispatcher();
        config(['catalog-sync.enabled' => false]);
        Storage::fake('public');
        Schema::table('products', function (Blueprint $table) {
            foreach (['slug', 'size', 'color', 'product_role', 'highlights', 'stores', 'shipping_country_of_origin', 'shipping_hs_code', 'previous_price', 'promotion_price'] as $column) {
                $table->text($column)->nullable();
            }
            foreach (['color_parent_id', 'childcategory_id', 'updated_by'] as $column) {
                $table->integer($column)->nullable();
            }
            $table->boolean('shipping_is_dangerous_goods')->default(false);
            $table->timestamp('admin_edited_at')->nullable();
        });
        Schema::create('product_additional_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
        });
    }

    protected function tearDown(): void
    {
        Product::setEventDispatcher($this->productEvents);
        parent::tearDown();
    }

    public function test_pdp_builds_the_same_gallery_for_both_formats(): void
    {
        $view = file_get_contents(resource_path('views/produtos/show.blade.php'));
        $start = strpos($view, '@php');
        $end = strpos($view, '$colorMap', $start);
        $template = substr($view, $start, $end - $start)."\n@endphp\n{!! json_encode(\$gallery) !!}";
        $paths = ['products/gallery/additional.webp'];
        foreach ([json_encode($paths), json_encode(json_encode($paths))] as $raw) {
            $product = new Product();
            $product->setRawAttributes(['photo' => 'products/photo/main.webp', 'gallery' => $raw], true);
            $rendered = \Illuminate\Support\Facades\Blade::render($template, ['product' => $product]);
            $this->assertSame(['products/photo/main.webp', ...$paths], json_decode(trim($rendered), true), $rendered);
        }
    }

    private function request(array $data, bool $upload = false): Request
    {
        $request = Request::create('/admin/products', 'POST', $data, [], $upload ? [
            'gallery' => [UploadedFile::fake()->image('gallery.jpg')],
        ] : []);
        $request->headers->set('Accept', 'application/json');
        return $request;
    }

    private function assertGallery(int $id, array $expected): void
    {
        $raw = DB::table('products')->where('id', $id)->value('gallery');
        $this->assertSame($expected, json_decode($raw, true));
        $this->assertSame($expected, Product::findOrFail($id)->gallery);
    }

    public function test_admin_creation_editing_and_child_propagation_store_arrays(): void
    {
        $converter = \Mockery::mock(ImageConverterService::class);
        $converter->shouldReceive('toWebp')->twice()->andReturn('products/gallery/first.webp', 'products/gallery/second.webp');
        $this->app->instance(ImageConverterService::class, $converter);
        $controller = app(ProductControllerAdmin::class);
        $data = ['sku' => 'GALLERY', 'external_name' => 'Gallery product', 'price' => 100, 'stock' => 5];
        $controller->store($this->request($data, true));
        $id = (int) DB::table('products')->value('id');
        $first = ['products/gallery/first.webp'];
        $this->assertGallery($id, $first);

        // Historical data is introduced only in this disposable SQLite database.
        DB::table('products')->where('id', $id)->update(['gallery' => json_encode(json_encode($first))]);
        $childId = DB::table('products')->insertGetId(['sku' => 'CHILD', 'price' => 100, 'stock' => 5, 'size' => 'L']);
        $relationships = \Mockery::mock(ProductRelationshipSearch::class);
        $relationships->shouldReceive('compatibleSize')->twice()->andReturn(true);
        $this->app->instance(ProductRelationshipSearch::class, $relationships);
        $data['parent_id'] = [$childId];
        $response = $controller->update($this->request($data, true), $id);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $expected = [...$first, 'products/gallery/second.webp'];
        $this->assertGallery($id, $expected);
        $this->assertGallery($childId, $expected);

        $response = $controller->update($this->request($data), $id);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertGallery($id, $expected);
    }

    public function test_single_and_multiple_deletion_support_both_formats(): void
    {
        $controller = app(ProductControllerAdmin::class);
        foreach ([false, true] as $legacy) {
            $paths = ['products/gallery/a.webp', 'products/gallery/b.webp'];
            foreach ($paths as $path) Storage::disk('public')->put($path, 'image');
            $json = json_encode($paths);
            $id = DB::table('products')->insertGetId(['sku' => 'DELETE-'.$legacy, 'price' => 100, 'gallery' => $legacy ? json_encode($json) : $json]);
            $controller->deleteGalleryImage($this->request([]), $id, 'a.webp');
            $this->assertGallery($id, [$paths[1]]);
            Storage::disk('public')->assertMissing($paths[0]);
            Storage::disk('public')->assertExists($paths[1]);
            $controller->multiDeleteGalleryImage($this->request(['image_names' => 'b.webp']), $id);
            $this->assertGallery($id, []);
            Storage::disk('public')->assertMissing($paths[1]);
        }
    }
}
