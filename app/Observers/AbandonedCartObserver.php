<?php

namespace App\Observers;

use App\Models\AbandonedCart;
use App\Services\AdminNotificationService;

class AbandonedCartObserver
{
    private const HIGH_VALUE_LIMIT = 200;

    public function __construct(private AdminNotificationService $notifications) {}

    public function created(AbandonedCart $cart): void
    {
        if ((float) $cart->total < self::HIGH_VALUE_LIMIT) {
            return;
        }

        $displayTotal = 'US$ '.number_format((float) $cart->total, 2, ',', '.');
        if ($cart->currency_sign && $cart->currency_sign !== 'US$') {
            $convertedTotal = (float) $cart->total * ((float) $cart->currency_value ?: 1);
            $displayTotal .= " ({$cart->currency_sign} ".number_format($convertedTotal, 2, ',', '.').')';
        }

        $this->notifications->notifyAdmins(
            'high_value_abandoned_cart',
            'Carrinho importante abandonado',
            "Um carrinho de {$displayTotal} foi abandonado.",
            "/admin/abandoned-carts/{$cart->getKey()}",
            ['abandoned_cart_id' => $cart->getKey(), 'total' => (float) $cart->total],
        );
    }

    public function updated(AbandonedCart $cart): void
    {
        if (! $cart->wasChanged('feedback_at') || ! $cart->feedback_at) {
            return;
        }

        $this->notifications->notifyAdmins(
            'abandoned_cart_feedback',
            'Cliente respondeu sobre o carrinho',
            "Foi recebida uma resposta sobre o carrinho abandonado #{$cart->getKey()}.",
            "/admin/abandoned-carts/{$cart->getKey()}",
            ['abandoned_cart_id' => $cart->getKey(), 'reason' => $cart->feedback_reason],
        );
    }
}
