<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\Dhl\DhlSettingsService;
use App\Services\StoreControlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StoreControlController extends Controller
{
    private const FIELDS = [
        'cart_enabled',
        'checkout_enabled',
        'add_to_cart_enabled',
        'deposit_enabled',
        'bancard_enabled',
        'pix_enabled',
        'whatsapp_enabled',
        'geonames_enabled',
        'header_categories_enabled', 'header_institucional_enabled', 'header_bridal_enabled',
        'header_palace_enabled', 'header_cafe_enabled', 'header_blog_enabled', 'header_contact_enabled',
        'header_guide_enabled',
        'footer_categories_enabled', 'footer_institucional_enabled', 'footer_bridal_enabled',
        'footer_palace_enabled', 'footer_cafe_enabled', 'footer_blog_enabled', 'footer_contact_enabled',
        'footer_guide_enabled',
    ];

    public function edit(StoreControlService $controls, DhlSettingsService $dhlSettings): View
    {
        $this->ensureMasterAdmin();

        return view('admin.store-controls.edit', [
            'controls' => array_merge($controls->manualSettings(), [
                'dhl_enabled' => (bool) ($dhlSettings->resolved()['enabled'] ?? false),
            ]),
            'temporaryTest' => $controls->temporaryPurchaseTestStatus(),
        ]);
    }

    public function update(
        Request $request,
        StoreControlService $controls,
        DhlSettingsService $dhlSettings,
    ): RedirectResponse {
        $this->ensureMasterAdmin();

        $request->validate(array_merge([
            'store_profile' => ['required', 'in:stage,sax,otica'],
            'dhl_enabled' => ['nullable', 'boolean'],
        ], collect(self::FIELDS)->mapWithKeys(
            fn (string $field): array => [$field => ['nullable', 'boolean']]
        )->all()));

        $dhl = $dhlSettings->current();
        $enableDhl = $request->boolean('dhl_enabled');

        if ($enableDhl && collect(['api_key', 'api_secret', 'account_number'])
            ->contains(fn (string $field): bool => blank($dhl->{$field}))) {
            throw ValidationException::withMessages([
                'dhl_enabled' => 'Para ativar a DHL, preencha chave da API, segredo e número da conta na página DHL Express.',
            ]);
        }

        DB::transaction(function () use ($request, $dhl, $enableDhl): void {
            $settings = SystemSetting::query()->firstOrCreate([], ['maintenance' => false]);
            $settings->fill(['store_profile' => $request->string('store_profile')->toString()] + collect(self::FIELDS)->mapWithKeys(
                fn (string $field): array => [$field => $request->boolean($field)]
            )->all())->save();

            $dhl->forceFill([
                'enabled' => $enableDhl,
                'updated_by' => auth()->id(),
            ])->save();
        });

        $controls->clearCache();
        $dhlSettings->clearCache();

        return back()->with('success', __('messages.store_controls_saved'));
    }

    public function activateTemporaryTest(StoreControlService $controls): RedirectResponse
    {
        $this->ensureMasterAdmin();

        $status = $controls->activateTemporaryPurchaseTest(auth()->id());

        return back()->with('success', sprintf(
            'Janela de testes ativada por %d minutos, até %s.',
            StoreControlService::TEMPORARY_TEST_MINUTES,
            $status['expires_at']->format('H:i:s')
        ));
    }

    public function deactivateTemporaryTest(StoreControlService $controls): RedirectResponse
    {
        $this->ensureMasterAdmin();
        $controls->deactivateTemporaryPurchaseTest();

        return back()->with('success', 'Janela temporária encerrada. Os bloqueios normais voltaram a valer.');
    }

    private function ensureMasterAdmin(): void
    {
        abort_unless(auth()->user()?->isMasterAdmin(), 403);
    }
}
