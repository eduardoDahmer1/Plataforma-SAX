@php
    $cartItems = $cartItems ?? [];
    $currentQty = $cartItems[$item->id] ?? 0;

    $isOutOfStock = ($item->stock ?? 0) <= 0;

    $fotoExibir = 'https://placehold.co/400x533/f5f5f5/999?text=No+Image';

    if (!empty($item->photo_url)) {
        $fotoExibir = $item->photo_url;
    } elseif (!empty($item->photo)) {
        $fotoExibir = asset('storage/uploads/products/' . $item->photo);
    }

    $rawColors = $item->card_colors ?? ($item->resolved_card_colors ?? ($item->colors ?? null));
    if (is_string($rawColors)) {
        $decoded = json_decode($rawColors, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $rawColors = $decoded;
        } else {
            $rawColors = array_filter(array_map('trim', explode(',', $rawColors)));
        }
    }
    if (!is_array($rawColors)) {
        $rawColors = [];
    }

    if (!empty($item->color)) {
        $rawColors[] = $item->color;
    }

    $colorDots = collect($rawColors)
        ->map(function ($color) {
            $c = strtoupper(trim((string) $color));
            if ($c === '') {
                return null;
            }
            if (!str_starts_with($c, '#')) {
                $c = '#' . $c;
            }
            return preg_match('/^#[0-9A-F]{6}$/', $c) ? $c : null;
        })
        ->filter()
        ->unique()
        ->take(6)
        ->values();

    $productSize = trim((string) ($item->size ?? ''));
@endphp

<div class="swiper-slide h-auto">
    <a href="{{ route('produto.show', $item->slug ?? $item->id) }}" class="text-decoration-none text-dark d-block h-100">
        <div class="card h-100 border-0 rounded-0 product-card-standard {{ $isOutOfStock ? 'sax-out-of-stock' : '' }}">

            <div class="product-card-standard__media sax-img-container position-relative">
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

            <div class="card-body product-card-standard__body d-flex flex-column">
                <div class="product-card-standard__brand">
                    {{ $item->brand->name ?? 'BRAND NAME' }}
                </div>

                <div class="product-card-standard__name" title="{{ $item->name ?? $item->name }}">
                    {{ $item->name ?? $item->name }}
                </div>

                <div class="product-card-standard__meta mt-auto">
                    <div class="product-card-standard__price">
                        @if ($showPrice ?? true)
                            {{ currency_format($item->price_final ?? $item->price, 2, ',', '.') }}
                        @endif
                    </div>
                    <div class="product-card-standard__sku">
                        SKU: {{ $item->sku ?? 'N/A' }}
                    </div>
                </div>

                <div class="product-card-standard__variants">
                    @if ($colorDots->isNotEmpty())
                        <div class="product-card-standard__colors" aria-label="Cores disponíveis">
                            @foreach ($colorDots as $hex)
                                <span class="product-card-standard__color-dot" style="background-color: {{ $hex }};" title="{{ $hex }}"></span>
                            @endforeach
                        </div>
                    @endif

                    @if ($productSize !== '')
                        <span class="product-card-standard__size">Tam: {{ $productSize }}</span>
                    @endif
                </div>
            </div>
        </div>
    </a>
</div>
