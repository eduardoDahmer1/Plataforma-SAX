<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactGuideEntry;
use App\Models\ContactGuideLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactGuideController extends Controller
{
    public function index(): View
    {
        return view('admin.contact-guide.index', [
            'locations' => ContactGuideLocation::query()
                ->with('entries')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function storeLocation(Request $request): RedirectResponse
    {
        ContactGuideLocation::query()->create($this->locationData($request));

        return back()->with('success', __('messages.admin_guide_success_location_created'));
    }

    public function updateLocation(Request $request, ContactGuideLocation $location): RedirectResponse
    {
        $location->update($this->locationData($request));

        return back()->with('success', __('messages.admin_guide_success_location_updated'));
    }

    public function destroyLocation(ContactGuideLocation $location): RedirectResponse
    {
        $location->delete();

        return back()->with('success', __('messages.admin_guide_success_location_deleted'));
    }

    public function storeEntry(Request $request): RedirectResponse
    {
        ContactGuideEntry::query()->create($this->entryData($request));

        return back()->with('success', __('messages.admin_guide_success_sector_created'));
    }

    public function updateEntry(Request $request, ContactGuideEntry $entry): RedirectResponse
    {
        $entry->update($this->entryData($request));

        return back()->with('success', __('messages.admin_guide_success_sector_updated'));
    }

    public function destroyEntry(ContactGuideEntry $entry): RedirectResponse
    {
        $entry->delete();

        return back()->with('success', __('messages.admin_guide_success_sector_deleted'));
    }

    private function locationData(Request $request): array
    {
        $validated = $request->validate([
            'city' => ['required', 'string', 'max:100'],
            'city_en' => ['nullable', 'string', 'max:100'],
            'city_es' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:140'],
            'name_en' => ['nullable', 'string', 'max:140'],
            'name_es' => ['nullable', 'string', 'max:140'],
            'subtitle' => ['nullable', 'string', 'max:180'],
            'subtitle_en' => ['nullable', 'string', 'max:180'],
            'subtitle_es' => ['nullable', 'string', 'max:180'],
            'service_hours' => ['nullable', 'string', 'max:1000'],
            'service_hours_en' => ['nullable', 'string', 'max:1000'],
            'service_hours_es' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'active' => ['nullable', 'boolean'],
        ]);
        $validated['active'] = $request->boolean('active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        return $validated;
    }

    private function entryData(Request $request): array
    {
        $validated = $request->validate([
            'location_id' => ['required', Rule::exists('contact_guide_locations', 'id')],
            'floor' => ['required', 'string', 'max:80'],
            'floor_en' => ['nullable', 'string', 'max:80'],
            'floor_es' => ['nullable', 'string', 'max:80'],
            'sector' => ['required', 'string', 'max:160'],
            'sector_en' => ['nullable', 'string', 'max:160'],
            'sector_es' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:500'],
            'description_en' => ['nullable', 'string', 'max:500'],
            'description_es' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:40'],
            'whatsapp_url' => ['nullable', 'url', 'max:500'],
            'brands_text' => ['nullable', 'string', 'max:10000'],
            'is_optical' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'active' => ['nullable', 'boolean'],
        ]);

        $validated['brands'] = collect(preg_split('/[,\n;]+/', (string) ($validated['brands_text'] ?? '')))
            ->map(fn (string $brand) => trim($brand))
            ->filter()
            ->unique(fn (string $brand) => mb_strtolower($brand))
            ->values()
            ->all();
        unset($validated['brands_text']);
        $validated['active'] = $request->boolean('active');
        $validated['is_optical'] = $request->boolean('is_optical');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        return $validated;
    }
}
