@props(['item', 'cartItems'])

<div class="col-6 col-md- col-lg-2 mb-1 g-1"> {{-- Espaçamento 'g-1' para grid colado como na imagem --}}
    <a href="{{ route('produto.show', $item->slug) }}" class="text-decoration-none text-dark">
        <div class="card h-100 border-0 rounded-0 jw-product-card">
            
            {{-- Área da Imagem com fundo cinza JW --}}
            <div class="jw-img-container position-relative">
                <img src="{{ $item->photo_url }}" 
                     class="card-img-top img-fluid rounded-0" 
                     alt="{{ $item->external_name }}">

                {{-- Ícone de Favorito - Estilo Outline Fino --}}
                <div class="position-absolute top-0 end-0 p-3">
                    @auth
                        <x-product-favorite-button :item="$item" />
                    @endauth
                </div>
            </div>

            {{-- Info do Produto - Tipografia Alinhada à Esquerda --}}
            <div class="card-body px-3 py-4">
                {{-- Marca: Bold e Curta --}}
                <div class="jw-brand fw-bold text-uppercase mb-1">
                    {{ $item->brand->name ?? 'JW PEI' }}
                </div>

                {{-- Nome: Cinza e Regular --}}
                <div class="jw-product-name text-muted mb-2">
                    {{ $item->name ?? $item->external_name }}
                </div>

                {{-- Preço: Bold e Direto --}}
                <div class="jw-price fw-bold">
                    {{ isset($item->price) ? number_format($item->price, 2, ',', '.') : '0,00' }} USD
                </div>
            </div>
        </div>
    </a>
</div>

<style>
    /* Fundo cinza claro idêntico à imagem enviada */
    .jw-product-card {
        background-color: #f2f2f2 !important; 
        transition: opacity 0.3s ease;
    }

    .jw-product-card:hover {
        opacity: 0.9;
    }

    .jw-img-container {
        aspect-ratio: 4 / 5; /* Proporção vertical elegante */
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .jw-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Ou 'contain' se as bolsas tiverem muita margem branca */
    }

    /* Tipografia JW PEI */
    .jw-brand {
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #000;
    }

    .jw-product-name {
        font-size: 0.8rem;
        letter-spacing: 0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .jw-price {
        font-size: 0.85rem;
        color: #000;
    }

    /* Botão de favorito transparente */
    .btn-favorite-guest {
        background: transparent;
        border: none;
        color: #000;
        padding: 0;
        transition: transform 0.2s ease;
    }

    .btn-favorite-guest:hover {
        transform: scale(1.1);
    }

    /* Ajuste para o grid colado da imagem */
    .g-1 {
        padding-right: 2px !important;
        padding-left: 2px !important;
    }
</style>