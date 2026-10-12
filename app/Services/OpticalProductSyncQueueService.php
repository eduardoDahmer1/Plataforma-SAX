<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class OpticalProductSyncQueueService
{
    private const TABLE = 'catalog_peer_sync_requests';

    public function __construct(
        private readonly OpticalProductSyncService $sync,
        private readonly AdminNotificationService $notifications,
    ) {}

    public function enqueue(int $productId): void
    {
        if ($productId <= 0 || ! $this->sync->enabled()) {
            return;
        }

        DB::table(self::TABLE)->updateOrInsert(
            ['product_id' => $productId],
            [
                'marker' => (string) Str::uuid(),
                'attempts' => 0,
                'available_at' => now()->addSeconds(max(0, (int) config('catalog-sync.debounce_seconds', 20))),
                'last_error' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function processPending(int $maxFamilies = 1, int $maxSeconds = 45): array
    {
        $maxFamilies = max(1, min($maxFamilies, 10));
        $maxSeconds = max(5, min($maxSeconds, 300));

        return Cache::lock('catalog_peer_sync_worker_v2', $maxSeconds + 30)->get(
            fn () => $this->processLocked($maxFamilies, $maxSeconds)
        ) ?? ['processed' => 0, 'failed' => 0, 'pending' => $this->pendingCount()];
    }

    public function pendingCount(): int
    {
        return DB::table(self::TABLE)->count();
    }

    private function processLocked(int $maxFamilies, int $maxSeconds): array
    {
        $startedAt = microtime(true);
        $processed = 0;
        $failed = 0;

        while ($processed + $failed < $maxFamilies && microtime(true) - $startedAt < $maxSeconds) {
            $request = DB::table(self::TABLE)
                ->where('available_at', '<=', now())
                ->orderBy('available_at')
                ->orderBy('id')
                ->first();

            if (! $request) {
                break;
            }

            $familyIds = $this->sync->familyProductIds((int) $request->product_id);
            if ($familyIds === []) {
                $this->deleteIfUnchanged([(int) $request->product_id => (string) $request->marker]);
                $processed++;
                continue;
            }

            $markers = DB::table(self::TABLE)
                ->whereIn('product_id', $familyIds)
                ->pluck('marker', 'product_id')
                ->mapWithKeys(fn ($marker, $id) => [(int) $id => (string) $marker])
                ->all();

            try {
                $this->sync->syncFamily((int) $request->product_id);
                $this->deleteIfUnchanged($markers);
                $processed++;
            } catch (Throwable $exception) {
                report($exception);
                $failed++;
                $attempts = (int) $request->attempts + 1;
                $delayMinutes = min(30, 2 ** min($attempts, 5));

                DB::table(self::TABLE)
                    ->where('product_id', $request->product_id)
                    ->where('marker', $request->marker)
                    ->update([
                        'attempts' => $attempts,
                        'available_at' => now()->addMinutes($delayMinutes),
                        'last_error' => mb_substr($exception->getMessage(), 0, 2000),
                        'updated_at' => now(),
                    ]);

                if (Cache::add("catalog_peer_sync_failure:{$request->product_id}", true, now()->addMinutes(15))) {
                    $this->notifications->notifyAdmins(
                        'catalog_peer_sync_failed',
                        'Falha ao sincronizar produto óptico',
                        "O produto #{$request->product_id} ficou pendente para nova tentativa automática.",
                        '/admin/produtos',
                        ['product_id' => $request->product_id, 'error' => mb_substr($exception->getMessage(), 0, 500)],
                    );
                }

                Log::warning('Optical catalog sync deferred after failure.', [
                    'product_id' => (int) $request->product_id,
                    'attempts' => $attempts,
                    'retry_in_minutes' => $delayMinutes,
                ]);
            }
        }

        return ['processed' => $processed, 'failed' => $failed, 'pending' => $this->pendingCount()];
    }

    private function deleteIfUnchanged(array $markers): void
    {
        foreach ($markers as $productId => $marker) {
            DB::table(self::TABLE)
                ->where('product_id', $productId)
                ->where('marker', $marker)
                ->delete();
        }
    }
}
