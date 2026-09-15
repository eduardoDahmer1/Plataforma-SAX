<?php

namespace Tests\Unit;

use App\Http\Middleware\SetLocale;
use Tests\TestCase;

class DefaultLocaleTest extends TestCase
{
    public function test_spanish_is_the_default_locale_for_new_sessions(): void
    {
        $this->assertSame('es', SetLocale::DEFAULT_LOCALE);
        $this->assertSame('es', config('app.locale'));
    }
}
