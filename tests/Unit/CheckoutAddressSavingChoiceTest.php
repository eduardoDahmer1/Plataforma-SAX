<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CheckoutAddressSavingChoiceTest extends TestCase
{
    public function test_checkout_offers_and_honors_the_choice_not_to_save_a_new_address(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/components/checkout/step3-shipping.blade.php');
        $javascript = file_get_contents(__DIR__.'/../../public/js/app-custom-checkout.js');
        $controller = file_get_contents(__DIR__.'/../../app/Http/Controllers/CheckoutController.php');

        $this->assertStringContainsString('name="save_address"', $view);
        $this->assertStringContainsString('id="continueWithoutSavingAddress"', $view);
        $this->assertStringContainsString('Continuar sem salvar', $view);

        $this->assertStringContainsString("continueAfterAddressChoice(false)", $javascript);
        $this->assertStringContainsString("saveAddressInput.value = shouldSave ? '1' : '0'", $javascript);

        $this->assertStringContainsString("'save_address' => 'nullable|boolean'", $controller);
        $this->assertStringContainsString("if (\$request->boolean('save_address'))", $controller);
        $this->assertStringContainsString("'street' => \$request->input('street')", $controller);
    }
}
