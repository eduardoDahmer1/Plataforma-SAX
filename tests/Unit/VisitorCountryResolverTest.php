<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\VisitorCountryResolver;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class VisitorCountryResolverTest extends TestCase
{
    public function test_it_prefers_the_registered_users_country(): void
    {
        $request = Request::create('/analytics/event', 'POST', server: ['REMOTE_ADDR' => '8.8.8.8']);
        $request->setUserResolver(fn () => new User(['country' => 'Brasil']));

        $this->assertSame(
            ['code' => 'BR', 'name' => 'Brasil', 'source' => 'profile'],
            (new VisitorCountryResolver())->resolve($request)
        );
    }

    public function test_it_uses_a_trusted_country_header_without_an_external_request(): void
    {
        $request = Request::create('/analytics/event', 'POST', server: ['HTTP_CF_IPCOUNTRY' => 'PY']);

        $this->assertSame(
            ['code' => 'PY', 'name' => 'Paraguai', 'source' => 'header'],
            (new VisitorCountryResolver())->resolve($request)
        );
    }

    public function test_it_does_not_geolocate_private_ips(): void
    {
        $request = Request::create('/analytics/event', 'POST', server: ['REMOTE_ADDR' => '127.0.0.1']);

        $this->assertSame([], (new VisitorCountryResolver())->resolve($request));
    }
}
