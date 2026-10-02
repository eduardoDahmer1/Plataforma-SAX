<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CommerceLayoutTest extends TestCase
{
    public function test_cart_and_payment_views_share_the_commerce_layout(): void
    {
        $views = [
            'resources/views/cart/view.blade.php',
            'resources/views/checkout/index.blade.php',
            'resources/views/checkout/success.blade.php',
            'resources/views/checkout/error.blade.php',
            'resources/views/payment/deposito.blade.php',
            'resources/views/payment/rendix-pix.blade.php',
            'resources/views/payment/bancard-v2.blade.php',
            'resources/views/payment/bancard-v2-success.blade.php',
            'resources/views/payment/bancard-v2-error.blade.php',
        ];

        foreach ($views as $view) {
            $this->assertStringContainsString(
                "@extends('layout.checkout')",
                file_get_contents(__DIR__.'/../../'.$view),
                $view
            );
        }
    }

    public function test_commerce_layout_is_focused_and_keeps_essential_actions(): void
    {
        $layout = file_get_contents(__DIR__.'/../../resources/views/layout/checkout.blade.php');

        $this->assertStringContainsString('sax-commerce-topbar', $layout);
        $this->assertStringContainsString('sax-commerce-secure', $layout);
        $this->assertStringContainsString('locale-currency-selector', $layout);
        $this->assertStringContainsString("route('home')", $layout);
        $this->assertStringContainsString("route('user.dashboard')", $layout);
        $this->assertStringNotContainsString('headerPartial', $layout);
        $this->assertStringNotContainsString("components.footer", $layout);
    }

    public function test_checkout_exposes_named_responsive_steps_and_neutral_tokens(): void
    {
        $checkout = file_get_contents(__DIR__.'/../../resources/views/checkout/index.blade.php');
        $styles = file_get_contents(__DIR__.'/../../public/css/checkout.css');

        foreach (['Itens', 'Identificação', 'Entrega', 'Pagamento'] as $step) {
            $this->assertStringContainsString("<span>{$step}</span>", $checkout);
        }

        $this->assertStringContainsString('Commerce workspace', $styles);
        $this->assertStringContainsString('--commerce-page: #f3f3f1', $styles);
        $this->assertStringContainsString('--commerce-border: #d7d8da', $styles);
        $this->assertStringContainsString('@media (max-width: 767.98px)', $styles);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $styles);
    }
}
