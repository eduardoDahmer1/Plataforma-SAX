<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\Dhl\DhlShipmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncDhlTracking extends Command
{
    protected $signature = 'dhl:sync-tracking {--limit=50 : Maximum number of active shipments per run}';

    protected $description = 'Refreshes active DHL shipment checkpoints and delivery status';

    public function handle(DhlShipmentService $shipments): int
    {
        $limit = min(200, max(1, (int) $this->option('limit')));
        $updated = 0;
        $failed = 0;

        Order::query()
            ->where('shipping_provider', 'dhl')
            ->whereNotNull('dhl_tracking_number')
            ->whereNotIn('dhl_shipment_status', ['delivered', 'returned'])
            ->where(function ($query): void {
                $query->whereNull('dhl_last_tracking_at')
                    ->orWhere('dhl_last_tracking_at', '<=', now()->subMinutes(20));
            })
            ->oldest('dhl_last_tracking_at')
            ->limit($limit)
            ->get()
            ->each(function (Order $order) use ($shipments, &$updated, &$failed): void {
                try {
                    $shipments->syncTracking($order);
                    $updated++;
                } catch (Throwable $exception) {
                    $failed++;
                    Log::warning('Scheduled DHL tracking refresh failed', [
                        'order_id' => $order->getKey(),
                        'tracking_number' => $order->dhl_tracking_number,
                        'message' => $exception->getMessage(),
                    ]);
                }
            });

        $this->info("DHL tracking: {$updated} updated, {$failed} failed.");

        return self::SUCCESS;
    }
}
