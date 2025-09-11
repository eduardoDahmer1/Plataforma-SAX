@props(['item'])

<form action="{{ route('user.preferences.toggle') }}" method="POST" class="card-favorite-form d-none">
    @csrf
    <input type="hidden" name="product_id" value="{{ $item->id }}">
    <button type="submit" class="btn btn-outline-danger">
        <i class="fas fa-heart"></i>
    </button>
</form>
