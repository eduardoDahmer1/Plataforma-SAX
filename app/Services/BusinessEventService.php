<?php

namespace App\Services;

use App\Models\BusinessEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class BusinessEventService
{
    public function record(
        string $category,
        string $title,
        ?string $message = null,
        string $severity = 'warning',
        ?int $userId = null,
        ?int $orderId = null,
        ?string $reference = null,
    ): void {
        try {
            if (!Schema::hasTable('business_events')) return;

            BusinessEvent::create([
                'category' => mb_substr($category, 0, 30),
                'severity' => mb_substr($severity, 0, 15),
                'user_id' => $userId,
                'order_id' => $orderId,
                'title' => mb_substr($title, 0, 160),
                'message' => $message ? mb_substr($message, 0, 500) : null,
                'reference' => $reference ? mb_substr($reference, 0, 100) : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Não foi possível registrar evento de negócio', ['message' => $e->getMessage()]);
        }
    }
}
