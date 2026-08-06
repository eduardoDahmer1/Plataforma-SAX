<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\StoreControlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    ];

    public function edit(StoreControlService $controls): View
    {
        $this->ensureMasterAdmin();

        return view('admin.store-controls.edit', ['controls' => $controls->settings()]);
    }

    public function update(Request $request, StoreControlService $controls): RedirectResponse
    {
        $this->ensureMasterAdmin();

        $request->validate(collect(self::FIELDS)->mapWithKeys(
            fn (string $field): array => [$field => ['nullable', 'boolean']]
        )->all());

        $settings = SystemSetting::query()->firstOrCreate([], ['maintenance' => false]);
        $settings->fill(collect(self::FIELDS)->mapWithKeys(
            fn (string $field): array => [$field => $request->boolean($field)]
        )->all())->save();

        $controls->clearCache();

        return back()->with('success', __('messages.store_controls_saved'));
    }

    private function ensureMasterAdmin(): void
    {
        abort_unless(auth()->user()?->isMasterAdmin(), 403);
    }
}
