@props(['selected' => null])
@php
    $selected = \App\Support\CountrySupport::normalizeForStorage($selected) ?: 'brasil';
    $countries = collect(\App\Support\CountrySupport::countries(app()->getLocale()));

    if (! app(\App\Services\StoreControlService::class)->enabled('geonames')) {
        $countries = $countries->whereIn('iso2', ['BR', 'PY']);
    }
@endphp
@foreach ($countries as $country)
    <option value="{{ $country['value'] }}"
            data-iso2="{{ $country['iso2'] }}"
            data-shipping-provider="{{ $country['shipping_provider'] }}"
            {{ (string) $selected === (string) $country['value'] ? 'selected' : '' }}>
        {{ $country['name'] }}{{ $country['shipping_provider'] === 'dhl' ? ' — DHL' : '' }}
    </option>
@endforeach
