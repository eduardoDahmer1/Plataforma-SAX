<?php

namespace App\Http\Middleware;

use App\Services\StoreControlService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreFeatureEnabled
{
    public function __construct(private StoreControlService $controls)
    {
    }

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if ($this->controls->enabled($feature)) {
            return $next($request);
        }

        $messageKey = match ($feature) {
            'checkout' => 'store_checkout_disabled_message',
            'add_to_cart' => 'store_add_to_cart_disabled_message',
            'pix', 'bancard', 'deposit' => 'store_payment_disabled_message',
            default => 'store_cart_disabled_message',
        };
        $message = __('messages.'.$messageKey);
        $titleKey = match ($feature) {
            'pix', 'bancard', 'deposit' => 'store_payment_disabled_title',
            'checkout' => 'store_checkout_paused_button',
            default => 'store_cart_disabled_title',
        };
        $title = __('messages.'.$titleKey);

        if ($request->expectsJson()) {
            $payload = [
                'message' => $message,
                'code' => 'store_feature_disabled',
                'feature' => $feature,
            ];

            if (in_array($feature, ['pix', 'bancard', 'deposit'], true) && auth()->check()) {
                $order = $request->route('order');
                $orderId = is_object($order) ? $order->id : $order;
                if (is_numeric($orderId)) {
                    $payload['redirect_url'] = route('user.orders.show', $orderId);
                }
            }

            return response()->json($payload, 503);
        }

        if (in_array($feature, ['pix', 'bancard', 'deposit'], true) && auth()->check()) {
            $order = $request->route('order');
            $orderId = is_object($order) ? $order->id : $order;

            if (is_numeric($orderId)) {
                return redirect()->route('user.orders.show', $orderId)
                    ->with('store_feature_blocked', $message)
                    ->with('store_feature_blocked_title', $title);
            }

            return redirect()->route('user.orders')
                ->with('store_feature_blocked', $message)
                ->with('store_feature_blocked_title', $title);
        }

        $route = $feature === 'checkout' && $this->controls->enabled('cart') ? 'cart.view' : 'home';

        return redirect()->route($route)
            ->with('store_feature_blocked', $message)
            ->with('store_feature_blocked_title', $title);
    }
}
