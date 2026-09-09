<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ProductAiSettingsService
{
    // Read on each operation so long-running workers observe changes immediately.
    public function settings(): ?SystemSetting
    {
        return Schema::hasColumn('system_settings', 'ai_enabled')
            ? SystemSetting::query()->first()
            : null;
    }

    public function apiKey(): string
    {
        $settings = $this->settings();

        return trim((string) ($settings?->ai_key_source === 'stored'
            ? $settings->ai_api_key
            : config('services.openai.api_key')));
    }

    public function unavailableReason(): ?string
    {
        if ($this->settings()?->ai_enabled === false) {
            return 'La IA está desactivada para este sitio. Los lotes pendientes quedan en pausa.';
        }

        return $this->apiKey() === '' ? 'Configurá una clave API para utilizar la IA en este sitio.' : null;
    }

    public function ensureAvailable(): void
    {
        if ($reason = $this->unavailableReason()) {
            throw ValidationException::withMessages(['ai' => $reason]);
        }
    }
}
