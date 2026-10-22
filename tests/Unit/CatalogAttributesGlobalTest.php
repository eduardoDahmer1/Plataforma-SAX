<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\CatalogAttributeService;
use App\Services\CatalogAttributeSyncService;
use Illuminate\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;
use Mockery;
use PHPUnit\Framework\TestCase;

/** No Laravel bootstrap, PDO, database connections, or executed SQL. */
class CatalogAttributesGlobalTest extends TestCase
{
    protected function tearDown(): void
    {
        Model::unsetConnectionResolver();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
        Mockery::close();
        parent::tearDown();
    }

    public function test_original_migration_defines_only_four_tables_and_reverses_them_in_order(): void
    {
        Facade::setFacadeApplication(new Container);
        Schema::swap(Mockery::mock());
        $tables = [];
        $drops = [];
        Schema::shouldReceive('create')->times(4)->andReturnUsing(function ($name, $callback) use (&$tables) {
            $blueprint = new Blueprint($name);
            $callback($blueprint);
            $tables[] = $name;
        });
        Schema::shouldReceive('dropIfExists')->times(4)->andReturnUsing(function ($name) use (&$drops) { $drops[] = $name; });
        $migration = require __DIR__.'/../../database/migrations/2026_10_19_120000_create_catalog_product_attributes.php';
        $migration->up();
        $migration->down();
        $this->assertSame(['catalog_attributes', 'catalog_attribute_options', 'product_attribute_values', 'product_attribute_value_options'], $tables);
        $this->assertSame(array_reverse($tables), $drops);
    }

    public function test_activation_is_independent_of_category_including_uncategorized_products(): void
    {
        $service = new class extends CatalogAttributeService {
            public bool $enabled = true;
            public function available(): bool { return $this->enabled; }
        };
        foreach ([null, 3, 13, 999] as $category) {
            $this->assertTrue($service->enabledFor(new Product(['category_id' => $category])));
        }
        $service->enabled = false;
        $this->assertFalse($service->enabledFor(new Product));
    }

    public function test_global_missing_and_ai_context_exclude_effective_and_manual_decisions(): void
    {
        // SQL generation is exercised, but select is replaced and PDO is a tripwire.
        $connection = new class(function () { throw new \LogicException('Database access forbidden'); }) extends SQLiteConnection {
            public array $queries = [];
            public function select($query, $bindings = [], $useReadPdo = true)
            {
                $this->queries[] = [$query, $bindings];
                if (str_contains($query, '"catalog_attributes"')) return [(object) ['id' => 1], (object) ['id' => 2], (object) ['id' => 3]];
                if (str_contains($query, '"product_attribute_values"')) return [(object) ['attribute_id' => 2]];
                throw new \LogicException('Unexpected query: '.$query);
            }
        };
        $resolver = new ConnectionResolver(['fake' => $connection]);
        $resolver->setDefaultConnection('fake');
        Model::setConnectionResolver($resolver);
        $service = new class extends CatalogAttributeService {
            public function enabledFor(Product $product): bool { return true; }
            public function effective(Product $product, array $visited = []): array { return [1 => ['inherited' => true]]; }
            public function dictionary(): Collection { return collect([['id' => 1, 'active' => true], ['id' => 3, 'active' => true], ['id' => 4, 'active' => false]]); }
        };
        $product = (new Product)->forceFill(['id' => 10, 'category_id' => 999]);
        $this->assertSame([3], $service->missing($product));
        $context = $service->aiContext($product);
        $this->assertSame([3], $context['missing_attribute_ids']);
        $this->assertSame([2], $context['protected_ids']);
        $this->assertSame([1, 3], array_column($context['dictionary'], 'id'));
        foreach ($connection->queries as [$sql, $bindings]) {
            $this->assertStringNotContainsString('categor', $sql);
            if (str_contains($sql, '"catalog_attributes"')) $this->assertSame([true], $bindings);
        }
    }

    public function test_effective_values_preserve_parent_inheritance_override_reset_and_block(): void
    {
        $connection = new class(function () { throw new \LogicException('Database access forbidden'); }) extends SQLiteConnection {
            public ?string $childMode = null;
            public bool $inheritable = true;
            public function select($query, $bindings = [], $useReadPdo = true)
            {
                if (str_contains($query, '"products"')) return [(object) ['id' => 1, 'parent_id' => null]];
                if (str_contains($query, '"catalog_attributes"')) return [(object) ['id' => 10, 'inherit_from_parent' => $this->inheritable]];
                if (str_contains($query, '"catalog_attribute_options"')) return [];
                if (str_contains($query, '"product_attribute_values"')) {
                    $parent = $bindings[0] === 1;
                    if (! $parent && $this->childMode === null) return [];
                    return [(object) ['id' => $parent ? 100 : 101, 'attribute_id' => 10,
                        'mode' => $parent ? 'value' : $this->childMode, 'boolean_value' => $parent,
                        'number_value' => null, 'source' => 'manual', 'revision' => 'test']];
                }
                throw new \LogicException('Unexpected query: '.$query);
            }
        };
        $resolver = new ConnectionResolver(['fake' => $connection]);
        $resolver->setDefaultConnection('fake');
        Model::setConnectionResolver($resolver);
        $service = new class extends CatalogAttributeService {
            public function enabledFor(Product $product): bool { return true; }
        };
        $child = (new Product)->forceFill(['id' => 2, 'parent_id' => 1]);
        $inherited = $service->effective($child)[10];
        $this->assertTrue($inherited['inherited']);
        $this->assertTrue($inherited['boolean']);
        $this->assertSame(1, $inherited['from_product_id']);
        $connection->childMode = 'value';
        $this->assertFalse($service->effective($child)[10]['boolean']);
        $this->assertFalse($service->effective($child)[10]['inherited']);
        $connection->childMode = 'inherit';
        $this->assertTrue($service->effective($child)[10]['inherited']);
        $connection->childMode = 'block';
        $this->assertSame('block', $service->effective($child)[10]['mode']);
        $connection->childMode = null;
        $connection->inheritable = false;
        $this->assertSame([], $service->effective($child));
        $this->assertSame([], $service->effective((new Product)->forceFill(['id' => 3, 'color_parent_id' => 1])));
    }

    public function test_sync_readiness_requires_only_four_tables_on_each_side(): void
    {
        Facade::setFacadeApplication(new Container);
        DB::swap(Mockery::mock());
        $schema = Mockery::mock();
        $peer = Mockery::mock(ConnectionInterface::class);
        DB::shouldReceive('getSchemaBuilder')->times(4)->andReturn($schema);
        $peer->shouldReceive('getSchemaBuilder')->times(4)->andReturn($schema);
        foreach (['catalog_attributes', 'catalog_attribute_options', 'product_attribute_values', 'product_attribute_value_options'] as $table) {
            $schema->shouldReceive('hasTable')->with($table)->twice()->andReturn(true);
        }
        $this->assertTrue((new CatalogAttributeSyncService)->ready($peer));
    }
}
