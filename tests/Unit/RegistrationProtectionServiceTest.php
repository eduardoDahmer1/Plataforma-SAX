<?php

namespace Tests\Unit;

use App\Services\RegistrationProtectionService;
use Illuminate\Http\Request;
use Tests\TestCase;

class RegistrationProtectionServiceTest extends TestCase
{
    public function test_it_recognizes_the_spam_patterns_seen_in_fake_accounts(): void
    {
        $service = app(RegistrationProtectionService::class);

        $this->assertNotNull($service->suspiciousNameReason('ACCOUNT ALERT: claim $3000 at https://spam.example'));
        $this->assertNotNull($service->suspiciousNameReason('*** welcome bonus promo code ***'));
        $this->assertNull($service->suspiciousNameReason('Maria da Silva'));
    }

    public function test_form_token_is_required_and_honeypot_is_rejected(): void
    {
        config()->set('services.turnstile.enabled', false);
        config()->set('services.turnstile.minimum_fill_seconds', 0);

        $service = app(RegistrationProtectionService::class);
        $session = app('session')->driver();
        $displayRequest = Request::create('/register', 'GET');
        $displayRequest->setLaravelSession($session);
        $token = $service->issue($displayRequest);

        $validRequest = Request::create('/register', 'POST', [
            '_registration_token' => $token,
            'company_website' => '',
        ]);
        $validRequest->setLaravelSession($session);
        $this->assertNull($service->validationError($validRequest));

        $missingToken = Request::create('/register', 'POST');
        $missingToken->setLaravelSession($session);
        $this->assertNotNull($service->validationError($missingToken));

        $honeypot = Request::create('/register', 'POST', [
            '_registration_token' => $token,
            'company_website' => 'https://bot.example',
        ]);
        $honeypot->setLaravelSession($session);
        $this->assertNotNull($service->validationError($honeypot));
    }
}
