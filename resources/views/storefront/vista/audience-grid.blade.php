@php
    $taxonomyItems = app(\App\Services\OpticalNavigationService::class)->items();
    $selectedKeys = collect($section['optical_item_keys'] ?? [])->filter()->values();
    $itemsByKey = $taxonomyItems->keyBy(fn (array $item) => $item['type'].':'.$item['id']);
    if ($selectedKeys->isNotEmpty()) {
        $audiences = $selectedKeys->map(fn (string $key) => $itemsByKey->get($key))->filter()->values();
    } else {
        $childItems = $taxonomyItems->where('type', 'childcategory')->values();
        $usedIds = collect();
        $pickItem = function (array $terms, ?callable $reject = null) use ($childItems, $taxonomyItems, $usedIds) {
            $matches = fn (array $item) => collect($terms)->contains(
                fn (string $term) => str_contains(\Illuminate\Support\Str::ascii(mb_strtolower($item['label'])), $term)
            );
            $item = $childItems->first(fn (array $candidate) =>
                ! $usedIds->contains($candidate['type'].':'.$candidate['id'])
                && $matches($candidate)
                && (! $reject || ! $reject($candidate))
            );
            $item ??= $taxonomyItems->first(fn (array $candidate) => ! $usedIds->contains($candidate['type'].':'.$candidate['id']));
            if ($item) {
                $usedIds->push($item['type'].':'.$item['id']);
            }

            return $item;
        };
        $audiences = collect([
            $pickItem(['femenino', 'feminino', 'mujer']),
            $pickItem(['masculino', 'homem', 'hombre'], fn (array $item) => str_contains(\Illuminate\Support\Str::ascii(mb_strtolower($item['label'])), 'infantil')),
            $pickItem(['infantil', 'nino', 'nina', 'crianca']),
        ])->filter()->values();
    }
    $layoutCount = $audiences->count() >= 5 ? 'many' : (string) max(1, $audiences->count());
@endphp

@if($audiences->isNotEmpty())
<section class="vista-audiences vista-audiences--{{ $layoutCount }}" aria-label="Categorias em destaque">
    @foreach($audiences as $item)
        @php
            // A foto vem de um produto real daquela categoria. Ela evita que
            // banners legados de outras áreas apareçam na vitrine óptica.
            $image = $item['photo'] ?: $item['banner'];
        @endphp
        <a href="{{ $item['url'] }}" class="vista-audience-card" aria-label="{{ $item['label'] }}">
            @if($image)
                <img src="{{ $image }}" alt="{{ $item['label'] }}" loading="lazy" decoding="async">
            @else
                <span class="vista-audience-card__placeholder"><i class="fa-solid fa-glasses"></i></span>
            @endif
            <strong>{{ $item['label'] }}</strong>
        </a>
    @endforeach
</section>
@endif
