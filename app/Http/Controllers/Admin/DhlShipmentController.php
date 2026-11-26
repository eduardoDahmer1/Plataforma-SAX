<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Dhl\DhlShipmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

final class DhlShipmentController extends Controller
{
    public function store(Request $request, Order $order, DhlShipmentService $shipments)
    {
        $validated = $request->validate([
            'product_code' => ['required', 'string', 'regex:/^[A-Za-z0-9]{1,3}$/'],
            'planned_shipping_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'planned_shipping_time' => ['required', 'date_format:H:i'],
            'request_pickup' => ['nullable', 'boolean'],
            'pickup_close_time' => [Rule::requiredIf($request->boolean('request_pickup')), 'nullable', 'date_format:H:i'],
            'pickup_location' => [Rule::requiredIf($request->boolean('request_pickup')), 'nullable', 'string', 'max:80'],
        ]);

        try {
            $plannedAt = Carbon::createFromFormat(
                'Y-m-d H:i',
                $validated['planned_shipping_date'].' '.$validated['planned_shipping_time'],
                config('app.timezone'),
            );

            Cache::lock('dhl:create-shipment:'.$order->getKey(), 90)->block(5, function () use ($order, $shipments, $validated, $plannedAt): void {
                $shipments->create($order->fresh(), [
                    'product_code' => $validated['product_code'],
                    'planned_at' => $plannedAt,
                    'request_pickup' => (bool) ($validated['request_pickup'] ?? false),
                    'pickup_close_time' => $validated['pickup_close_time'] ?? null,
                    'pickup_location' => $validated['pickup_location'] ?? null,
                ]);
            });

            return back()->with('success', 'Guia DHL criada. AWB, documentos e coleta foram registrados no pedido.');
        } catch (Throwable $exception) {
            Log::warning('DHL shipment creation failed', [
                'order_id' => $order->getKey(),
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->with('error', $exception->getMessage() ?: 'Não foi possível criar a guia DHL.');
        }
    }

    public function track(Order $order, DhlShipmentService $shipments)
    {
        try {
            $shipments->syncTracking($order);

            return back()->with('success', 'Rastreamento DHL atualizado.');
        } catch (Throwable $exception) {
            Log::warning('DHL tracking refresh failed', [
                'order_id' => $order->getKey(),
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', $exception->getMessage() ?: 'Não foi possível atualizar o rastreamento DHL.');
        }
    }

    public function document(Order $order, int $document)
    {
        $metadata = data_get((array) $order->dhl_documents, $document);
        abort_unless(is_array($metadata), 404);

        $path = (string) ($metadata['path'] ?? '');
        $expectedPrefix = 'dhl/shipments/'.$order->getKey().'/';
        abort_unless($path !== '' && str_starts_with($path, $expectedPrefix), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        $extension = strtolower((string) ($metadata['format'] ?? 'pdf'));
        $filename = str_replace(' ', '-', (string) ($metadata['name'] ?? 'documento-dhl')).'.'.$extension;

        return Storage::disk('local')->download($path, $filename);
    }

    public function proofOfDelivery(Order $order, DhlShipmentService $shipments)
    {
        try {
            $shipments->fetchProofOfDelivery($order);

            return back()->with('success', 'Comprovante de entrega DHL salvo nos documentos do pedido.');
        } catch (Throwable $exception) {
            Log::warning('DHL proof of delivery download failed', [
                'order_id' => $order->getKey(),
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', $exception->getMessage() ?: 'Não foi possível obter o comprovante de entrega DHL.');
        }
    }
}
