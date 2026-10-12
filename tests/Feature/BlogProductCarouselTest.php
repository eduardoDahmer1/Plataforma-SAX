<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Services\BlogProductCarouselService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BlogProductCarouselTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        app()->setLocale('es');
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(true);
        });
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'external_name', 'sku', 'slug', 'photo', 'product_role'] as $field) {
                $table->string($field)->nullable();
            }
            $table->decimal('price')->default(10);
            $table->integer('stock')->default(2);
            $table->boolean('status')->default(true);
            $table->boolean('is_outlet')->default(false);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('category_id')->default(1);
            $table->unsignedBigInteger('brand_id')->default(1);
        });
        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('locale');
            $table->string('name');
        });
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_default')->default(true);
        });
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->text('content');
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
        DB::table('categories')->insert(['id' => 1, 'status' => true]);
        DB::table('brands')->insert(['id' => 1, 'name' => 'SAX']);
    }

    private function product(int $id, array $attributes = []): void
    {
        DB::table('products')->insert(array_merge([
            'id' => $id, 'name' => 'Producto '.$id, 'sku' => 'SKU-'.$id,
            'slug' => 'producto-'.$id, 'photo' => 'products/example.webp', 'product_role' => 'P',
        ], $attributes));
    }

    private function block(string $ids, string $title = 'Selección SAX'): string
    {
        return '<div class="blog-products-placeholder mceNonEditable" contenteditable="false" data-blog-products="'.$ids.'" data-title="'.$title.'"><strong>Vista del editor</strong></div>';
    }

    public function test_renders_ordered_live_products_at_the_selected_position(): void
    {
        $this->product(1);
        $this->product(2);
        DB::table('product_translations')->insert(['product_id' => 2, 'locale' => 'es', 'name' => 'Nombre traducido']);
        $content = '<p>Antes</p>'.$this->block('2,1').'<p>Después</p>';
        $service = app(BlogProductCarouselService::class);
        $html = $service->render($content);
        $this->assertLessThan(strpos($html, 'producto-1'), strpos($html, 'producto-2'));
        $this->assertStringContainsString('Nombre traducido', $html);
        $this->assertStringContainsString('10,00', $html);
        $this->assertStringStartsWith('<p>Antes</p><section', $html);
        $this->assertStringEndsWith('<p>Después</p>', $html);
        $this->assertStringNotContainsString('Vista del editor', $html);

        DB::table('products')->where('id', 1)->update(['price' => 37.5, 'name' => 'Nombre actualizado', 'slug' => 'nuevo-enlace']);
        $html = $service->render($content);
        $this->assertStringContainsString('37,50', $html);
        $this->assertStringContainsString('Nombre actualizado', $html);
        $this->assertStringContainsString('nuevo-enlace', $html);
    }

    public function test_omits_unavailable_deleted_outlet_and_low_price_products_and_empty_blocks(): void
    {
        $this->product(1);
        $this->product(2, ['status' => false]);
        $this->product(3, ['stock' => 0]);
        $this->product(4, ['is_outlet' => true]);
        $this->product(5, ['price' => 1]);
        $service = app(BlogProductCarouselService::class);
        $html = $service->render($this->block('1,2,3,4,5,999'));
        $this->assertStringContainsString('producto-1', $html);
        foreach ([2, 3, 4, 5, 999] as $id) {
            $this->assertStringNotContainsString('producto-'.$id.'"', $html);
        }
        DB::table('categories')->where('id', 1)->update(['status' => false]);
        $this->assertSame('<p>Texto</p>', $service->render('<p>Texto</p>'.$this->block('1,2,999')));
    }

    public function test_parent_with_available_child_matches_the_storefront_catalog(): void
    {
        $this->product(1, ['stock' => 0, 'status' => false]);
        $this->product(2, ['product_role' => 'F', 'parent_id' => 1]);
        $this->assertStringContainsString('producto-1', app(BlogProductCarouselService::class)->render($this->block('1')));
    }

    public function test_search_uses_blog_permission_route_and_available_catalog_and_restores_selected_ids(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\CheckAdmin::class]);
        $this->product(1);
        $this->product(2, ['status' => false]);
        $this->getJson(route('admin.blogs.products.search', ['q' => 'SKU-']))
            ->assertOk()->assertJsonCount(1, 'products')->assertJsonPath('products.0.id', 1);
        $this->getJson(route('admin.blogs.products.search', ['ids' => [1, 2]]))
            ->assertOk()->assertJsonCount(2, 'products')->assertJsonPath('products.1.available', false);
        $this->getJson(route('admin.blogs.products.search', ['ids' => ['bad']]))->assertUnprocessable();
    }

    public function test_search_requires_authentication(): void
    {
        $this->getJson(route('admin.blogs.products.search', ['q' => 'SKU']))->assertUnauthorized();
    }

    public function test_save_and_reopen_preserves_ids_and_invalidates_cached_article(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\CheckAdmin::class]);
        $blog = Blog::create(['title' => 'Artículo', 'slug' => 'articulo', 'content' => '<p>Original</p>']);
        Cache::put('blog_show_articulo', $blog);
        $content = '<p>Texto</p>'.$this->block('2,1');
        $this->putJson(route('admin.blogs.update', $blog), [
            'title' => 'Artículo', 'slug' => 'articulo', 'content' => $content,
        ])->assertOk();
        $this->assertSame($content, $blog->fresh()->content);
        $this->assertNull(Cache::get('blog_show_articulo'));
    }

    public function test_limits_and_invalid_ids_are_rejected_on_save(): void
    {
        $this->withoutMiddleware([\App\Http\Middleware\Authenticate::class, \App\Http\Middleware\CheckAdmin::class]);
        $blog = Blog::create(['title' => 'Artículo', 'slug' => 'articulo', 'content' => '<p>Original</p>']);
        foreach (['1,0', 'abc', implode(',', range(1, 21))] as $ids) {
            $this->putJson(route('admin.blogs.update', $blog), [
                'title' => 'Artículo', 'content' => $this->block($ids),
            ])->assertUnprocessable()->assertJsonValidationErrors('content');
        }
        $this->assertSame('<p>Original</p>', $blog->fresh()->content);
        $this->expectException(ValidationException::class);
        app(BlogProductCarouselService::class)->validate(str_repeat($this->block('1'), 11));
    }

    public function test_headings_are_escaped_multiple_carousels_have_unique_ids_and_plain_articles_are_unchanged(): void
    {
        $this->product(1);
        $service = app(BlogProductCarouselService::class);
        $html = $service->render($this->block('1', '&lt;script&gt;alert(1)&lt;/script&gt;').$this->block('1'));
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('id="blog-products-1"', $html);
        $this->assertStringContainsString('id="blog-products-2"', $html);
        $this->assertSame('<p>Sin carrusel</p>', $service->render('<p>Sin carrusel</p>'));
    }
}
