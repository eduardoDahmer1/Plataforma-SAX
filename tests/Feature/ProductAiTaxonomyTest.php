<?php
namespace Tests\Feature;

use App\Http\Controllers\Admin\ProductAiTaxonomyController;
use App\Services\StoreTaxonomyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\Support\UsesProductAiDatabase;
use Tests\TestCase;

class ProductAiTaxonomyTest extends TestCase
{
    use UsesProductAiDatabase;

    public function test_creation_reuses_duplicates_and_preserves_parent_relationships(): void
    {
        Schema::create('childcategories', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug');
            $table->integer('category_id'); $table->integer('subcategory_id'); $table->timestamps();
        });
        $parent = DB::table('categories')->insertGetId(['name' => 'Moda', 'slug' => 'moda']);
        $controller = app(ProductAiTaxonomyController::class);
        $taxonomy = app(StoreTaxonomyService::class);
        $payload = ['type' => 'subcategory', 'name' => 'Bolsas', 'category_id' => $parent];
        $first = $controller->store(Request::create('/', 'POST', $payload), $taxonomy);
        $this->assertSame(201, $first->getStatusCode());
        $sub = $first->getData(true)['item']['id'];
        $second = $controller->store(Request::create('/', 'POST', $payload), $taxonomy);
        $this->assertFalse($second->getData(true)['created']);
        $this->assertSame($sub, $second->getData(true)['item']['id']);
        $child = $controller->store(Request::create('/', 'POST', ['type' => 'childcategory', 'name' => 'Bolsas de viagem', 'category_id' => $parent, 'subcategory_id' => $sub]), $taxonomy);
        $this->assertSame(201, $child->getStatusCode());
        $this->assertSame($parent, $child->getData(true)['item']['category_id']);
        $this->assertSame($sub, $child->getData(true)['item']['subcategory_id']);
        $other = DB::table('categories')->insertGetId(['name' => 'Casa', 'slug' => 'casa']);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $controller->store(Request::create('/', 'POST', ['type' => 'childcategory', 'name' => 'Inválida', 'category_id' => $other, 'subcategory_id' => $sub]), $taxonomy);
    }
}
