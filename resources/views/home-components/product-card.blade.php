@php
    $cartItems = $cartItems ?? [];
    $currentQty = $cartItems[$item->id] ?? 0;
    
    // Ajuste de segurança para o estoque (considerando que se for nulo, é 0)
    $isOutOfStock = ($item->stock ?? 0) <= 0;

    // Lógica para a URL da Foto:
    // Tenta photo_url (accessor), depois asset('storage/uploads/'.$item->photo), depois placeholder
    $fotoExibir = 'https://placehold.co/400x533/f5f5f5/999?text=No+Image';
    
    if (!empty($item->photo_url)) {
        $fotoExibir = $item->photo_url;
    } elseif (!empty($item->photo)) {
        // Se a foto for apenas o nome do arquivo, montamos o caminho completo
        $fotoExibir = asset('storage/uploads/products/' . $item->photo); 
        // Verifique se sua pasta é 'uploads/products' ou apenas 'uploads'
    }
@endphp

<div class="swiper-slide h-auto">
    <a href="{{ route('produto.show', $item->slug) }}" class="text-decoration-none text-dark d-block h-100">
        <div class="card h-100 border-0 rounded-0 sax-product-card {{ $isOutOfStock ? 'sax-out-of-stock' : '' }}">

            <div class="sax-img-container position-relative">
                {{-- Imagem principal corrigida --}}
                <img src="{{ $fotoExibir }}"
                    class="card-img-top img-fluid rounded-0" 
                    alt="{{ $item->name ?? $item->name }}"
                    loading="lazy"
                    onerror="this.src='https://placehold.co/400x533/f5f5f5/999?text=No+Image'">

                @if ($isOutOfStock)
                    <div class="sax-stock-overlay">ESGOTADO</div>
                @endif

                @if($showFavorite ?? true)
                    <div class="position-absolute top-0 end-0 p-3">
                        @auth
                            <x-product-favorite-button :item="$item" />
                        @endauth
                    </div>
                @endif
            </div>

            <div class="card-body px-2 py-3 d-flex flex-column">
                <div class="sax-brand fw-bold text-uppercase mb-1">
                    {{-- Acessando o relacionamento brand que carregamos no Controller --}}
                    {{ $item->brand->name ?? 'BRAND NAME' }}
                </div>

                <div class="sax-product-name text-muted mb-3 text-truncate" title="{{ $item->name ?? $item->name }}">
                    {{ $item->name ?? $item->name }}
                </div>

                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <div class="sax-price fw-bold text-dark">
                        @if($showPrice ?? true)
                            {{-- Usando price_final que calculamos no Controller ou o preço padrão --}}
                            {{ currency_format($item->price_final ?? $item->price, 2, ',', '.') }}
                        @endif
                    </div>
                    <div class="sax-sku text-muted small">
                        SKU: {{ $item->sku ?? 'N/A' }}
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>
