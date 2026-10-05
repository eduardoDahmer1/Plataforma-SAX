<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LanguageSwitchTest extends TestCase
{
    public function test_language_is_changed_only_by_post(): void
    {
        $this->withoutMiddleware([
            PreventRequestsDuringMaintenance::class,
            SetLocale::class,
        ]);

        $this->from('/guia-de-atendimento')
            ->post(route('lang.switch', 'pt_BR'))
            ->assertRedirect('/guia-de-atendimento')
            ->assertSessionHas('locale', 'pt_BR');

        $this->assertSame(['POST'], Route::getRoutes()->getByName('lang.switch')->methods());
    }

    public function test_invalid_language_does_not_replace_the_session_locale(): void
    {
        $this->withoutMiddleware([
            PreventRequestsDuringMaintenance::class,
            SetLocale::class,
        ]);

        $this->withSession(['locale' => 'pt_BR'])
            ->from('/guia-de-atendimento')
            ->post('/lang/fr')
            ->assertRedirect('/guia-de-atendimento')
            ->assertSessionHas('locale', 'pt_BR');
    }
}
