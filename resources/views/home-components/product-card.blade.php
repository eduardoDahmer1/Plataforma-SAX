@php
    $currentQty = $cartItems[$item->id] ?? 0;
    $isOutOfStock = $item->stock <= 0;
@endphp

<div class="swiper-slide h-auto">
    <a href="{{ route('produto.show', $item->id) }}" class="text-decoration-none text-dark d-block h-100">
        <div class="card h-100 border-0 rounded-0 sax-product-card {{ $isOutOfStock ? 'sax-out-of-stock' : '' }}">

            <div class="sax-img-container position-relative">
                <img src="{{ $item->photo_url ?? 'https://placehold.co/400x533/f5f5f5/999?text=SAX' }}"
                    class="card-img-top img-fluid rounded-0" alt="{{ $item->name ?? $item->external_name }}"
                    onerror="this.src='https://placehold.co/400x533/f5f5f5/999?text=No+Image'">

                @if ($isOutOfStock)
                    <div class="sax-stock-overlay">AGOTADO</div>
                @endif

                <div class="position-absolute top-0 end-0 p-3"> @auth
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
                        {{ isset($item->price) ? number_format($item->price, 2, ',', '.') : '0,00' }} USD
                    </div>
                    <div class="sax-sku text-muted">
                        SKU: {{ $item->sku ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>

<style>
    .sax-product-card {
        background-color: transparent !important;
        height: 100%;
    }

    .sax-img-container {
        aspect-ratio: 3 / 4;
        background-color: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        width: 100%;
    }

    .card-body {
        background-color: #f5f5f5;
    }

    .sax-img-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: transform 0.5s ease;
    }

    .sax-product-card:hover img {
        transform: scale(1.05);
    }

    .sax-brand {
        font-size: 0.8rem;
        color: #000;
        letter-spacing: 0.5px;
    }

    .sax-product-name {
        font-size: 0.7rem;
        height: 2.4em;
        line-height: 1.2;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        text-transform: uppercase;
    }

    .sax-price {
        font-size: 0.9rem;
    }

    .sax-sku {
        font-size: 0.6rem;
    }

    .sax-stock-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        z-index: 2;
    }
</style>
