@forelse ($paginated as $item)
    @php
        $translation = $item->translations->first();
        $displayName = filled($translation?->name) ? $translation->name : $item->name;
    @endphp
    <x-product-card :item="$item" :displayName="$displayName" gridClass="col-6 col-md-4 col-lg-3 col-xl-2" />
@empty
    <div class="col-12 text-center py-5 my-4">
        <p class="text-muted small mb-3">{{ __('messages.nenhum_item_encontrado') }}</p>
    </div>
@endforelse
