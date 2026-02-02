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

<style>
    .btn-heart-luxury {
        background: none;
        border: none;
        padding: 5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease, opacity 0.2s ease;
        outline: none !important;
    }

    .btn-heart-luxury i {
        /* Cor preta sólida e tamanho refinado */
        color: #000000;
        font-size: 1.3rem;
        /* Se usar FontAwesome 5/6, o weight 300 deixa mais fino */
        font-weight: 300; 
    }

    .btn-heart-luxury:hover {
        transform: scale(1.1);
        opacity: 0.7;
    }

    .btn-heart-luxury:active {
        transform: scale(0.9);
    }

    /* Garante que o ícone preenchido também seja preto se favoritado */
    .btn-heart-luxury .fas.fa-heart {
        color: #000000;
    }
</style>