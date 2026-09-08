<?php

namespace App\Http\Controllers;

use App\Models\ContactGuideEntry;
use App\Models\ContactGuideLocation;
use App\Services\StoreControlService;
use Illuminate\View\View;

class ContactGuideController extends Controller
{
    public function show(StoreControlService $storeControls): View
    {
        $guideVisible = $storeControls->navigationVisible('header', 'guide')
            || $storeControls->navigationVisible('footer', 'guide');
        abort_unless($guideVisible || auth()->user()?->isAdmin(), 404);

        $isOtica = $storeControls->isOtica();
        $entryFilter = static function ($query) use ($isOtica): void {
            $query->where('active', true);

            if ($isOtica) {
                $query->where('is_optical', true);
            }
        };

        $locations = ContactGuideLocation::query()
            ->where('active', true)
            ->whereHas('entries', $entryFilter)
            ->with(['entries' => $entryFilter])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $brandCount = $locations->flatMap->entries
            ->flatMap(fn (ContactGuideEntry $entry) => $entry->brands ?? [])
            ->filter()
            ->unique(fn (string $brand) => mb_strtolower($brand))
            ->count();
        $sectorCount = $locations->sum(fn (ContactGuideLocation $location) => $location->entries->count());

        return view('contact.guide', compact('locations', 'isOtica', 'brandCount', 'sectorCount'));
    }
}
