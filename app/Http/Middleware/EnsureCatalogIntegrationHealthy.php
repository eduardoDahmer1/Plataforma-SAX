<?php

namespace App\Http\Middleware;

use App\Services\CatalogIntegrationAvailabilityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCatalogIntegrationHealthy
{
    public function __construct(private CatalogIntegrationAvailabilityService $availability)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->availability->isAvailable()) {
            return $next($request);
        }

        $message = __('messages.catalog_purchase_paused_message');
        $order = $request->route('order');
        $orderId = is_object($order) ? $order->id : $order;
        $isPaymentRoute = $request->routeIs(
            'checkout.bancard.v2',
            'checkout.rendix.pix',
            'checkout.rendix.pix.status',
            'checkout.rendix.pix.renew',
            'checkout.deposito',
            'checkout.deposito.submit',
            'orders.deposit.submit'
        );

        if ($request->expectsJson()) {
            $payload = [
                'message' => $message,
                'code' => 'catalog_integration_unavailable',
            ];

            if ($isPaymentRoute && auth()->check() && is_numeric($orderId)) {
                $payload['redirect_url'] = route('user.orders.show', $orderId);
            }

            return response()->json($payload, 503);
        }

        if ($request->routeIs('cart.add')) {
            return back()->with('catalog_purchase_blocked', $message);
        }

        if ($isPaymentRoute && auth()->check() && is_numeric($orderId)) {
            return redirect()
                ->route('user.orders.show', $orderId)
                ->with('catalog_purchase_blocked', $message);
        }

        return redirect()
            ->route('home')
            ->with('catalog_purchase_blocked', $message);
    }
}
