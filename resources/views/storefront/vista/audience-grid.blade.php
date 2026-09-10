@php
    $taxonomyItems = app(\App\Services\OpticalNavigationService::class)->items();
    $childItems = $taxonomyItems->where('type', 'childcategory')->values();
    $usedIds = collect();
    $pickItem = function (array $terms, ?callable $reject = null) use ($childItems, $taxonomyItems, $usedIds) {
        $matches = fn (array $item) => collect($terms)->contains(
            fn (string $term) => str_contains(\Illuminate\Support\Str::ascii(mb_strtolower($item['label'])), $term)
        );
        $item = $childItems->first(function (array $candidate) use ($matches, $reject, $usedIds) {
            return ! $usedIds->contains($candidate['type'].'-'.$candidate['id'])
                && $matches($candidate)
                && (! $reject || ! $reject($candidate));
        });
        $item ??= $taxonomyItems->first(fn (array $candidate) => ! $usedIds->contains($candidate['type'].'-'.$candidate['id']));
        if ($item) {
            $usedIds->push($item['type'].'-'.$item['id']);
        }
        return $item;
    };
    $audiences = collect([
        ['label' => 'Para mujer', 'item' => $pickItem(['femenino', 'feminino', 'mujer'])],
        ['label' => 'Para hombre', 'item' => $pickItem(['masculino', 'homem', 'hombre'], fn (array $item) => str_contains(\Illuminate\Support\Str::ascii(mb_strtolower($item['label'])), 'infantil'))],
        ['label' => 'Para niños', 'item' => $pickItem(['infantil', 'nino', 'nina', 'crianca'])],
    ])->filter(fn (array $audience) => $audience['item'] !== null);
@endphp

@if($audiences->isNotEmpty())
<section class="vista-audiences" aria-label="Comprar por público">
    @foreach($audiences as $audience)
        @php
            $item = $audience['item'];
            $image = $item['banner'] ?: $item['photo'];
        @endphp
        <a href="{{ $item['url'] }}" class="vista-audience-card" aria-label="{{ $audience['label'] }}: {{ $item['label'] }}">
            @if($image)
                <img src="{{ $image }}" alt="{{ $item['label'] }}" loading="lazy" decoding="async">
            @else
                <span class="vista-audience-card__placeholder"><i class="fa-solid fa-glasses"></i></span>
            @endif
            <strong>{{ $audience['label'] }}</strong>
        </a>
    @endforeach
</section>
@endif
