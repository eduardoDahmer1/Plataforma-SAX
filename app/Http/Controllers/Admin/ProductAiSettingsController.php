<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\ProductAiSettingsService;
use App\Services\StoreControlService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductAiSettingsController extends Controller
{
    public function edit(ProductAiSettingsService $ai, StoreControlService $store)
    {
        abort_unless(auth()->user()?->isMasterAdmin(), 403);
        $settings = $ai->settings();

        return view('admin.ai-settings.edit', [
            'enabled' => $settings?->ai_enabled ?? true,
            'keySource' => $settings?->ai_key_source ?? 'environment',
            'hasStoredKey' => filled($settings?->ai_api_key),
            'hasEnvironmentKey' => filled(config('services.openai.api_key')),
            'profile' => $store->storeProfile(),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()?->isMasterAdmin(), 403);
        $data = $request->validate([
            'ai_enabled' => ['required', 'boolean'],
            'ai_key_source' => ['required', 'in:environment,stored'],
            'ai_api_key' => ['nullable', 'string', 'max:512', 'regex:/^\S+$/'],
            'remove_api_key' => ['nullable', 'boolean'],
        ]);
        $settings = SystemSetting::query()->firstOrNew([], ['maintenance' => false]);
        $key = $request->boolean('remove_api_key') ? null : $settings->ai_api_key;
        if (! $request->boolean('remove_api_key') && filled($data['ai_api_key'] ?? null)) {
            $key = $data['ai_api_key'];
        }
        $effectiveKey = $data['ai_key_source'] === 'stored' ? $key : config('services.openai.api_key');
        if ($request->boolean('ai_enabled') && blank($effectiveKey)) {
            throw ValidationException::withMessages(['ai_key_source' => 'Agregá una clave API o elegí una fuente configurada antes de activar la IA.']);
        }
        $settings->fill([
            'ai_enabled' => $request->boolean('ai_enabled'),
            'ai_key_source' => $data['ai_key_source'],
            'ai_api_key' => $key,
        ])->save();

        return back()->with('success', 'Configuración de IA guardada para este sitio.');
    }
}
