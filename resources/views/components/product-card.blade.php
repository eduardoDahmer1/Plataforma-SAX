@props(['item', 'cartItems'])

<div class="col-6 col-md-4 col-lg-3 mb-4">
    <div class="card h-100 shadow-sm border-0 position-relative">
        <img src="{{ $item->photo_url }}" class="card-img-top img-fluid rounded-top" alt="{{ $item->external_name }}"
            style="max-height: 200px; object-fit: scale-down;">

        {{-- Botões --}}
        @auth
            @php $currentQty = $cartItems[$item->id] ?? 0; @endphp

            <x-product-favorite-button :item="$item" />
            <x-product-cart-button :item="$item" :currentQty="$currentQty" />
        @endauth

        <div class="card-body d-flex flex-column">
            <h6 class="card-title mb-2">
                <a href="{{ route('produto.show', $item->id) }}" class="text-decoration-none">
                    <i class="fas fa-tag me-1"></i>
                    {{ $item->name ?? ($item->external_name ?? 'Sem nome') }}
                </a>
            </h6>

            <p class="card-text small text-muted mb-2">
                <i class="fas fa-industry me-1"></i> {{ $item->brand->name ?? 'Sem marca' }}<br>
                <i class="fas fa-barcode me-1"></i> {{ $item->sku ?? 'Sem SKU' }}<br>
                <i class="fas fa-dollar-sign me-1"></i>
                {{ isset($item->price) ? currency_format($item->price) : 'Não informado' }}<br>

                <x-stock-badge :stock="$item->stock" />
            </p>

            <div class="mt-auto d-flex flex-column">
                <a href="{{ route('produto.show', $item->id) }}" class="btn btn-sm btn-info mb-2">
                    <i class="fas fa-eye me-1"></i> Ver Detalhes
                </a>

                @auth
                    @if (in_array(auth()->user()->user_type, [0, 1, 2]))
                        <x-product-buy-now :item="$item" />
                    @endif
                @else
                    <a href="#" class="btn btn-sm btn-warning mt-2" data-bs-toggle="modal"
                        data-bs-target="#loginModal">
                        <i class="fas fa-sign-in-alt me-1"></i> Login para Favoritar
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
