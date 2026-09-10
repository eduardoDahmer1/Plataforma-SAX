@php
    $items = collect($section['items'] ?? \App\Models\Generalsetting::HOME_HELP_ITEMS);
    $icons = [
        ['field' => 'icon_info', 'fallback' => 'fa-shield-halved'],
        ['field' => 'icon_cabide', 'fallback' => 'fa-bag-shopping'],
        ['field' => 'icon_help', 'fallback' => 'fa-truck'],
    ];
@endphp

<section class="vista-benefits" aria-label="Beneficios de compra">
    @foreach($icons as $index => $icon)
        @php
            $iconFile = $attribute?->{$icon['field']} ?? null;
            $label = \App\Models\Generalsetting::helpItemLabelForLocale($items->get($index, []));
        @endphp
        <div>
            @if(filled($iconFile))
                <img src="{{ asset('storage/uploads/'.ltrim($iconFile, '/')) }}" alt="" aria-hidden="true">
            @else
                <i class="fa-solid {{ $icon['fallback'] }}" aria-hidden="true"></i>
            @endif
            <span>{{ $label }}</span>
        </div>
    @endforeach
</section>
