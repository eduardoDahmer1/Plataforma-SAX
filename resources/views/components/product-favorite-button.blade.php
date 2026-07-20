@props(['item'])

@php
    $favoriteIds = request()->attributes->get('sax_favorite_product_ids');

    if ($favoriteIds === null) {
        $favoriteIds = auth()->check()
            ? auth()->user()->favoriteProducts()->pluck('products.id')->map(fn ($id) => (int) $id)->all()
            : [];
        request()->attributes->set('sax_favorite_product_ids', $favoriteIds);
    }

    $isFavorited = in_array((int) $item->id, $favoriteIds, true);
@endphp

<form action="{{ route('user.preferences.toggle') }}"
      method="POST"
      class="card-favorite-form js-favorite-confirm-form m-0 p-0"
      data-is-favorited="{{ $isFavorited ? '1' : '0' }}">
    @csrf
    <input type="hidden" name="product_id" value="{{ $item->id }}">

    <button type="submit"
            class="btn-heart-luxury"
            aria-label="{{ $isFavorited ? __('messages.favorite_remove_label') : __('messages.favorite_add_label') }}"
            title="{{ $isFavorited ? __('messages.favorite_remove_label') : __('messages.favorite_add_label') }}">
        <i class="{{ $isFavorited ? 'fas' : 'far' }} fa-heart"></i>
    </button>
</form>
