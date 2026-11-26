<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Dhl\DhlShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class CustomerDhlTrackingController extends Controller
{
    public function __invoke(Request $request, Order $order, DhlShipmentService $shipments)
    {
        abort_unless((int) $order->user_id === (int) $request->user()->getKey(), 404);

        try {
            $shipments->syncTracking($order);

            return back()->with('success', match (app()->getLocale()) {
                'es' => 'Seguimiento DHL actualizado.',
                'en' => 'DHL tracking updated.',
                default => 'Rastreamento DHL atualizado.',
            });
        } catch (Throwable $exception) {
            Log::warning('Customer DHL tracking refresh failed', [
                'order_id' => $order->getKey(),
                'user_id' => $request->user()->getKey(),
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', match (app()->getLocale()) {
                'es' => 'DHL aún no devolvió novedades para este envío.',
                'en' => 'DHL has not returned a new tracking update yet.',
                default => 'A DHL ainda não devolveu uma nova atualização para este envio.',
            });
        }
    }
}
