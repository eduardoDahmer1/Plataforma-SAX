@props(['item'])

{{-- O formulário agora envolve o botão para que o clique funcione --}}
<form action="{{ route('user.preferences.toggle') }}" method="POST" class="card-favorite-form m-0 p-0">
    @csrf
    <input type="hidden" name="product_id" value="{{ $item->id }}">
    
    <button type="submit" class="btn-heart-luxury" aria-label="Adicionar aos favoritos">
        {{-- 'far' é o ícone de linha (Regular), 'fas' é o preenchido (Solid) --}}
        <i class="{{ $item->is_favorited ? 'fas' : 'far' }} fa-heart"></i>
    </button>
</form>
