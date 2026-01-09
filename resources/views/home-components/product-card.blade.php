{{-- Componente de Card de Produto - Layout Original --}}
@php 
    // Recupera a quantidade atual no carrinho para validar estoque
    $currentQty = $cartItems[$item->id] ?? 0; 
@endphp

<div class="card h-100 shadow-sm border-0 position-relative">
    {{-- Imagem do Produto --}}
    <img src="{{ $item->photo_url }}" class="card-img-top"
        alt="{{ $item->name ?? ($item->external_name ?? 'Sem nome') }}"
        style="max-height:150px; object-fit:scale-down;">

    {{-- Botões Ocultos (Favoritos e Adicionar ao Carrinho via JS/Externo) --}}
    @auth
        <form action="{{ route('user.preferences.toggle') }}" method="POST"
            class="card-favorite-form d-none">
            @csrf
            <input type="hidden" name="product_id" value="{{ $item->id }}">
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-heart"></i></button>
        </form>

        <form action="{{ route('cart.add') }}" method="POST" class="card-add-form d-none">
            @csrf
            <input type="hidden" name="product_id" value="{{ $item->id }}">
            <button type="submit" class="btn btn-success"
                {{ $currentQty >= $item->stock ? 'disabled' : '' }}>
                <i class="fas fa-cart-plus"></i>
            </button>
        </form>
    @endauth

    <div class="card-body p-2 d-flex flex-column">
        {{-- Título --}}
        <h6 class="card-title mb-2">
            <a href="{{ route('produto.show', $item->id) }}"
                class="text-decoration-none">{{ $item->name ?? ($item->external_name ?? 'Sem nome') }}</a>
        </h6>

        {{-- Informações Técnicas e Preço --}}
        <p class="small text-muted mb-2">
            {{ $item->brand->name ?? 'Sem marca' }}<br>
            SKU: {{ $item->sku ?? 'N/A' }}<br>
            {{ isset($item->price) ? currency_format((float) $item->price) : 'Não informado' }}
        </p>

        {{-- Status de Estoque --}}
        @if ($item->stock > 0)
            <span class="badge bg-success"><i
                    class="fas fa-box me-1"></i>{{ $item->stock }} em estoque</span>
        @else
            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Sem
                estoque</span>
        @endif

        {{-- Ações Inferiores --}}
        <div class="mt-auto d-flex flex-column">
            <a href="{{ route('produto.show', $item->id) }}"
                class="btn btn-sm btn-info mt-2 mb-2">
                <i class="fas fa-eye me-1"></i> Ver Detalhes
            </a>
            
            @auth
                @if (in_array(auth()->user()->user_type, [0, 1, 2]))
                    <form action="{{ route('cart.addAndCheckout') }}" method="POST"
                        class="d-flex">
                        @csrf
                        <input type="hidden" name="product_id"
                            value="{{ $item->id }}">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                            <i class="fas fa-bolt me-1"></i> Comprar Agora
                        </button>
                    </form>
                @endif
            @endauth
        </div>
    </div>
</div>