<?php

namespace App\Services;

use App\Models\ContactGuideEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ContactGuideDirectory
{
    public function build(Collection $locations): array
    {
        return $locations->map(function ($location) {
            $floors = $location->entries->groupBy(fn ($entry) => $this->key($entry->floor ?? ''));
            $isFlagship = Str::contains(Str::lower(Str::ascii((string) $location->city)), 'ciudad del este')
                && Str::lower(Str::ascii((string) $location->name)) === 'sax department store';

            return [
                'id' => (string) $location->id,
                'name' => $location->translated('name'),
                'city' => $location->translated('city'),
                'hours' => $location->translated('service_hours'),
                'heroImage' => $isFlagship ? asset('images/contact-guide/sax-banner-1600x300.png') : null,
                'floors' => $floors->map(function ($entries) {
                    $groups = $entries->groupBy(fn ($entry) => $this->key($entry->sector ?? ''))
                        ->map(function ($sectorEntries) {
                            $entry = $sectorEntries->first();
                            $brands = $sectorEntries->flatMap(fn ($item) => $item->brands ?? [])
                                ->filter(fn ($brand) => is_string($brand) && trim($brand) !== '')
                                ->map(fn ($brand) => trim($brand))
                                ->unique(fn ($brand) => $this->key($brand))
                                ->values()->all();

                            return [
                                'id' => 'sector-'.$entry->id,
                                'name' => $entry->translated('sector'),
                                'description' => $entry->translated('description'),
                                'brands' => $brands,
                                'contacts' => $sectorEntries->map(fn ($item) => $this->contact($item))->filter()->unique('key')->values()->all(),
                            ];
                        })->values();

                    // A repeated destination is shown once, explicitly naming its sectors.
                    // There is no separate floor/location contact field in the existing schema.
                    $contacts = [];
                    foreach ($groups as $group) {
                        foreach ($group['contacts'] as $contact) {
                            $key = $contact['key'];
                            $contacts[$key] ??= ['url' => $contact['url'], 'groups' => []];
                            $contacts[$key]['groups'][$group['id']] = $group['name'];
                        }
                    }
                    $shared = [];
                    $groups = $groups->map(function ($group) use ($contacts) {
                        $group['contacts'] = array_values(array_filter($group['contacts'], fn ($contact) => count($contacts[$contact['key']]['groups']) === 1));
                        return $group;
                    });
                    foreach ($contacts as $contact) {
                        if (count($contact['groups']) > 1) {
                            $shared[] = ['url' => $contact['url'], 'label' => implode(' · ', $contact['groups'])];
                        }
                    }

                    return [
                        'id' => 'floor-'.$entries->first()->id,
                        'name' => $entries->first()->translated('floor') ?: __('messages.contact_guide_v2_floor_unspecified'),
                        'searchName' => $entries->first()->floor,
                        'groups' => $groups->all(),
                        'contacts' => $shared,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    private function key(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value)));
    }

    private function contact(ContactGuideEntry $entry): ?array
    {
        $url = $entry->whatsappUrl();
        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        if (($parts['scheme'] ?? '') !== 'https' || ! in_array($host, ['wa.me', 'api.whatsapp.com', 'web.whatsapp.com', 'www.whatsapp.com', 'chat.whatsapp.com'], true)) {
            return null;
        }
        parse_str($parts['query'] ?? '', $query);
        $phone = $host === 'wa.me' ? trim($parts['path'] ?? '', '/') : ($query['phone'] ?? null);
        if ($phone !== null && ! preg_match('/^\+?[1-9][0-9]{7,14}$/', $phone)) {
            // WhatsApp also supports opaque /message/ links.
            if (! ($host === 'wa.me' && preg_match('#^message/[A-Za-z0-9]+$#', $phone))) {
                return null;
            }
        } elseif ($phone === null && ! ($host === 'chat.whatsapp.com' && preg_match('#^/[A-Za-z0-9]+$#', $parts['path'] ?? ''))) {
            return null;
        }

        return ['key' => $phone ? ltrim($phone, '+') : $url, 'url' => $url];
    }
}
