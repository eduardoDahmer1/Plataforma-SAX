@props(['item', 'currentQty'])

@if(! auth()->check() || auth()->user()->canShop())
    <form action="{{ route('cart.add') }}" method="POST" class="card-add-form d-none">
        @csrf
        <input type="hidden" name="product_id" value="{{ $item->id }}">
        <button type="submit" class="btn btn-success" {{ $currentQty >= $item->stock ? 'disabled' : '' }}>
            <i class="fas fa-cart-plus"></i>
        </button>
    </form>
@endif
