@props(['item'])

<form action="{{ route('cart.addAndCheckout') }}" method="POST" class="d-flex">
    @csrf
    <input type="hidden" name="product_id" value="{{ $item->id }}">
    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
        <i class="fas fa-bolt me-1"></i> Comprar Agora
    </button>
</form>
