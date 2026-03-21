@props(['item', 'cartItems'])

<div class="col-6 col-md- col-lg-2 mb-1 g-1"> {{-- Espaçamento 'g-1' para grid colado como na imagem --}}
    <a href="{{ route('produto.show', $item->slug) }}" class="text-decoration-none text-dark">
        <div class="card h-100 border-0 rounded-0 jw-product-card">

            {{-- Área da Imagem com fundo cinza JW --}}
            <div class="jw-img-container position-relative">
                <img src="{{ $item->photo_url }}" class="card-img-top img-fluid rounded-0"
                    alt="{{ $item->external_name }}">

                {{-- Ícone de Favorito - Estilo Outline Fino --}}
                <div class="position-absolute top-0 end-0 p-3">
                    @auth
                        <x-product-favorite-button :item="$item" />
                    @endauth
                </div>
            </div>

            <div class="card-body px-2 py-3 d-flex flex-column">
                <div class="sax-brand fw-bold text-uppercase mb-1">
                    {{ $item->brand->name ?? 'BRAND NAME' }}
                </div>

                <div class="sax-product-name text-muted mb-3">
                    {{ $item->name ?? $item->external_name }}
                </div>

                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <div class="sax-price fw-bold text-dark">
                        {{ isset($item->price) ? currency_format($item->price, 2, ',', '.') : '0,00' }}
                    </div>
                    <div class="sax-sku text-muted">
                        SKU: {{ $item->sku ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>
