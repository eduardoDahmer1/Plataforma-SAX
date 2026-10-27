<?php

namespace Tests\Unit;

use App\Services\BrazilLocationService;
use App\Services\GeoNamesService;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Mockery;
use Tests\TestCase;

class BrazilLocationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_it_provides_all_brazilian_states_without_an_external_request(): void
    {
        $geoNames = Mockery::mock(GeoNamesService::class);

        $states = (new BrazilLocationService($geoNames))->states();

        $this->assertCount(27, $states);
        $this->assertSame(
            ['id' => 41, 'code' => 'PR', 'name' => 'Paraná'],
            collect($states)->firstWhere('code', 'PR')
        );
    }

    public function test_it_normalizes_and_caches_cities_returned_by_geonames(): void
    {
        $geoNames = Mockery::mock(GeoNamesService::class);
        $geoNames->shouldReceive('secondLevelSubdivisions')->once()->with('BR', '18', 'pt')->andReturn([
            ['geoname_id' => 3464975, 'name' => 'Curitiba'],
            ['geoname_id' => 3463030, 'name' => 'Foz do Iguaçu'],
        ]);

        $service = new BrazilLocationService($geoNames);
        $first = $service->cities(41);
        $second = $service->cities(41);

        $this->assertSame($first, $second);
        $this->assertSame('Curitiba', $first[0]['name']);
        $this->assertSame('Foz do Iguaçu', $first[1]['name']);
    }

    public function test_it_rejects_an_unknown_state_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new BrazilLocationService(Mockery::mock(GeoNamesService::class)))->cities(999);
    }
}
