@php 
    $currentQty = $cartItems[$item->id] ?? 0; 
    $isOutOfStock = $item->stock <= 0;
@endphp

<div class="sax-grid-item">
    <a href="{{ route('produto.show', $item->id) }}" class="text-decoration-none text-dark">
        <div class="card h-100 border-0 rounded-0 sax-product-card {{ $isOutOfStock ? 'sax-out-of-stock' : '' }}">
            
            <div class="sax-img-container position-relative">
                {{-- Prioriza photo_url, se vazio usa placeholder --}}
                <img src="{{ $item->photo_url ?? 'https://placehold.co/400x533/f5f5f5/999?text=SAX' }}" 
                     class="card-img-top img-fluid rounded-0" 
                     alt="{{ $item->name ?? $item->external_name }}"
                     onerror="this.src='https://placehold.co/400x533/f5f5f5/999?text=No+Image'">

                @if($isOutOfStock)
                    <div class="sax-stock-overlay">AGOTADO</div>
                @endif

                <div class="position-absolute top-0 end-0 p-2 ">
                    @auth
                        <x-product-favorite-button :item="$item" />
                    @else
                        <button class="sax-btn-favorite-guest d-none" data-bs-toggle="modal" data-bs-target="#loginModal" onclick="event.preventDefault();">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l8.78-8.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </button>
                    @endauth
                </div>
            </div>

            <div class="card-body px-2 py-3">
                <div class="sax-brand fw-bold text-uppercase mb-1">
                    {{ $item->brand->name ?? 'SAX EXCLUSIVE' }}
                </div>

                <div class="sax-product-name text-muted mb-2">
                    {{ $item->name ?? $item->external_name }}
                </div>

                <div class="d-flex justify-content-between align-items-end">
                    <div class="sax-price fw-bold">
                        {{ isset($item->price) ? number_format($item->price, 2, ',', '.') : '0,00' }} USD
                    </div>
                    <div class="sax-sku d-none d-lg-block">
                        SKU: {{ $item->sku ?? 'N/A' }}
                    </div>
                </div>

                @auth
                    @if (!$isOutOfStock && in_array(auth()->user()->user_type, [0, 1, 2]))
                        <form action="{{ route('cart.addAndCheckout') }}" method="POST" class="mt-3 sax-quick-buy">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item->id }}">
                            {{-- <button type="submit" class="btn btn-dark btn-sm w-100 rounded-0 py-2">
                                COMPRAR AGORA
                            </button> --}}
                        </form>
                    @endif
                @endauth
            </div>
        </div>
    </a>
</div>

<style>
    /* Grade Flexível e Firme */
    .sax-product-grid {
        display: grid;
        gap: 10px; 
        grid-template-columns: repeat(2, 1fr);
    }

    @media (min-width: 768px) { .sax-product-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 992px) { .sax-product-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (min-width: 1400px) { .sax-product-grid { grid-template-columns: repeat(5, 1fr); } }

    /* Estilo do Card */
    .sax-product-card {
        background-color: #f5f5f5 !important;
        transition: transform 0.2s ease;
    }
    
    .sax-img-container {
        aspect-ratio: 3 / 4;
        background-color: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sax-img-container img {
        width: 100%;
        height: 100%;
        object-fit: contain; /* Não corta o produto */
        padding: 15px;
    }

    .sax-product-name {
        font-size: 0.75rem;
        height: 2.5em; /* Garante que todos os nomes ocupem o mesmo espaço */
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .sax-quick-buy {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .sax-product-card:hover .sax-quick-buy { opacity: 1; }
    
    @media (max-width: 991px) { .sax-quick-buy { opacity: 1; } }
</style>