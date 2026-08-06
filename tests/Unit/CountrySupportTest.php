<?php

namespace Tests\Unit;

use App\Support\CountrySupport;
use App\Support\CountryCallingCodes;
use PHPUnit\Framework\TestCase;

class CountrySupportTest extends TestCase
{
    public function test_it_resolves_worldwide_calling_codes(): void
    {
        $this->assertSame('55', CountryCallingCodes::for('BR'));
        $this->assertSame('595', CountryCallingCodes::for('py'));
        $this->assertSame('54', CountryCallingCodes::for('AR'));
        $this->assertSame('1', CountryCallingCodes::for('US'));
        $this->assertSame('81', CountryCallingCodes::for('JP'));
        $this->assertSame('', CountryCallingCodes::for('invalid'));
    }

    public function test_preserves_legacy_brazil_and_paraguay_values(): void
    {
        $this->assertSame('brasil', CountrySupport::normalizeForStorage('BR'));
        $this->assertSame('paraguai', CountrySupport::normalizeForStorage('Paraguay'));
        $this->assertSame('BR', CountrySupport::iso2('brasil'));
        $this->assertSame('PY', CountrySupport::iso2('paraguai'));
    }

    public function test_worldwide_iso_countries_are_routed_to_dhl(): void
    {
        $this->assertSame('US', CountrySupport::normalizeForStorage('us'));
        $this->assertSame('DE', CountrySupport::normalizeForStorage('DE'));
        $this->assertTrue(CountrySupport::usesDhl('US'));
        $this->assertSame('dhl', CountrySupport::shippingProvider('DE'));
    }

    public function test_non_country_icu_regions_are_rejected(): void
    {
        $this->assertFalse(CountrySupport::isSupported('EU'));
        $this->assertFalse(CountrySupport::isSupported('ZZ'));
        $this->assertSame('', CountrySupport::normalizeForStorage('invalido'));
    }
}
