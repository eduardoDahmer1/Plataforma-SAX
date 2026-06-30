@forelse ($paginated as $item)
    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
        @include('home-components.product-card', ['item' => $item])
    </div>
@empty
    <div class="col-12 text-center py-5 my-4">
        <p class="text-muted small mb-3">{{ __('messages.nenhum_item_encontrado') }}</p>
    </div>
@endforelse
