<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappContact;
use App\Models\WhatsappWidgetSetting;
use App\Services\WhatsappWidgetService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WhatsappWidgetController extends Controller
{
    public function edit(WhatsappWidgetService $widget): View
    {
        $this->ensureMasterAdmin();

        return view('admin.whatsapp.edit', [
            'settings' => WhatsappWidgetSetting::query()->firstOrCreate([], [
                'title' => 'Concierge Digital SAX',
                'subtitle' => 'Escolha uma opção e fale com nosso time.',
                'enabled' => true,
            ]),
            'contacts' => WhatsappContact::query()->orderBy('sort_order')->orderBy('id')->get(),
            'pageContexts' => $widget::contextOptions(),
            'iconOptions' => self::iconOptions(),
        ]);
    }

    public function updateSettings(Request $request, WhatsappWidgetService $widget): RedirectResponse
    {
        $this->ensureMasterAdmin();

        $validated = $request->validate([
            'widget_title' => ['required', 'string', 'max:100'],
            'widget_title_en' => ['nullable', 'string', 'max:100'],
            'widget_title_es' => ['nullable', 'string', 'max:100'],
            'widget_subtitle' => ['required', 'string', 'max:180'],
            'widget_subtitle_en' => ['nullable', 'string', 'max:180'],
            'widget_subtitle_es' => ['nullable', 'string', 'max:180'],
            'widget_enabled' => ['nullable', 'boolean'],
        ]);

        WhatsappWidgetSetting::query()->firstOrCreate()->update([
            'title' => $validated['widget_title'],
            'title_en' => $validated['widget_title_en'] ?? null,
            'title_es' => $validated['widget_title_es'] ?? null,
            'subtitle' => $validated['widget_subtitle'],
            'subtitle_en' => $validated['widget_subtitle_en'] ?? null,
            'subtitle_es' => $validated['widget_subtitle_es'] ?? null,
            'enabled' => $request->boolean('widget_enabled'),
        ]);
        $widget->clearCache();

        return back()->with('success', __('messages.whatsapp_admin_success_settings'));
    }

    public function storeContact(Request $request, WhatsappWidgetService $widget): RedirectResponse
    {
        $this->ensureMasterAdmin();

        $validated = $request->validate($this->contactRules());
        $validated['active'] = $request->boolean('active');
        $validated['show_on_contact_page'] = $request->boolean('show_on_contact_page');
        $validated['sort_order'] = $validated['sort_order']
            ?? ((int) WhatsappContact::query()->max('sort_order') + 10);

        WhatsappContact::query()->create($validated);
        $widget->clearCache();

        return back()->with('success', __('messages.whatsapp_admin_success_created'));
    }

    public function updateContact(
        Request $request,
        WhatsappContact $contact,
        WhatsappWidgetService $widget
    ): RedirectResponse {
        $this->ensureMasterAdmin();

        $validated = $request->validate($this->contactRules());
        $validated['active'] = $request->boolean('active');
        $validated['show_on_contact_page'] = $request->boolean('show_on_contact_page');
        $contact->update($validated);
        $widget->clearCache();

        return back()->with('success', __('messages.whatsapp_admin_success_updated'));
    }

    public function destroyContact(
        WhatsappContact $contact,
        WhatsappWidgetService $widget
    ): RedirectResponse {
        $this->ensureMasterAdmin();

        $contact->delete();
        $widget->clearCache();

        return back()->with('success', __('messages.whatsapp_admin_success_deleted'));
    }

    private function contactRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'title_en' => ['nullable', 'string', 'max:120'],
            'title_es' => ['nullable', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:80'],
            'category_en' => ['nullable', 'string', 'max:80'],
            'category_es' => ['nullable', 'string', 'max:80'],
            'icon' => ['required', Rule::in(array_keys($this->iconOptions()))],
            'phone' => ['required', 'string', 'max:30', $this->validWhatsappNumber()],
            'message' => ['nullable', 'string', 'max:1000'],
            'message_en' => ['nullable', 'string', 'max:1000'],
            'message_es' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:240'],
            'description_en' => ['nullable', 'string', 'max:240'],
            'description_es' => ['nullable', 'string', 'max:240'],
            'page_contexts' => ['required', 'array', 'min:1'],
            'page_contexts.*' => ['required', Rule::in(array_keys(WhatsappWidgetService::contextOptions()))],
            'active' => ['nullable', 'boolean'],
            'show_on_contact_page' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    public static function iconOptions(): array
    {
        return [
            'fa-headset' => __('messages.whatsapp_icon_service'),
            'fa-building' => __('messages.whatsapp_icon_floor'),
            'fa-store' => __('messages.whatsapp_icon_store'),
            'fa-gem' => __('messages.whatsapp_icon_luxury'),
            'fa-shirt' => __('messages.whatsapp_icon_fashion'),
            'fa-glasses' => __('messages.whatsapp_icon_optical'),
            'fa-bag-shopping' => __('messages.whatsapp_icon_shopping'),
            'fa-utensils' => __('messages.whatsapp_icon_food'),
            'fa-truck-fast' => __('messages.whatsapp_icon_delivery'),
            'fa-location-dot' => __('messages.whatsapp_icon_location'),
            'fa-rotate-left' => __('messages.whatsapp_icon_returns'),
            'fa-circle-info' => __('messages.whatsapp_icon_information'),
        ];
    }

    private function validWhatsappNumber(): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail): void {
            $digits = preg_replace('/\D+/', '', (string) $value);

            if (strlen($digits) < 8 || strlen($digits) > 15) {
                $fail(__('messages.whatsapp_admin_invalid_phone'));
            }
        };
    }

    private function ensureMasterAdmin(): void
    {
        abort_unless(auth()->user()?->isMasterAdmin(), 403);
    }
}
