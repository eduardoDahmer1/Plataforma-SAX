@extends('layout.layout')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4"><i class="fas fa-search me-2"></i> Veja os produtos encontrados no catálogo.</h2>

        {{-- Alertas de sucesso --}}
        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="row">
            <x-sidebar-filters :brands="$brands" :categories="$categories" :subcategories="$subcategories" :childcategories="$childcategories" />


            {{-- LISTA DE PRODUTOS --}}
            <div class="col-md-9">
                <h4 class="mb-3"><i class="fas fa-box-open me-2"></i> Produtos</h4>

                @if ($paginated->count())
                    <div class="row">
                        @foreach ($paginated as $item)
                            <div class="col-6 col-md-4 col-lg-3 mb-4">
                                <div class="card h-100 shadow-sm border-0">
                                    <img src="{{ $item->photo_url }}" class="card-img-top img-fluid rounded-top"
                                        alt="{{ $item->external_name }}" style="max-height: 200px; object-fit: scale-down;">

                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title mb-2">
                                            <a href="{{ route('produto.show', $item->id) }}" class="text-decoration-none">
                                                <i class="fas fa-tag me-1"></i> {{ $item->external_name ?? 'Sem nome' }}
                                            </a>
                                        </h6>

                                        <p class="card-text small text-muted mb-2">
                                            <i class="fas fa-industry me-1"></i>
                                            {{ $item->brand->name ?? 'Sem marca' }}<br>
                                            <i class="fas fa-barcode me-1"></i> {{ $item->sku ?? 'Sem SKU' }}<br>
                                            <i class="fas fa-dollar-sign me-1"></i>
                                            {{ isset($item->price) ? currency_format($item->price) : 'Não informado' }}<br>

                                            {{-- Estoque --}}
                                            @if ($item->stock > 0)
                                                <span class="badge bg-success"><i class="fas fa-box me-1"></i>
                                                    {{ $item->stock }} em estoque</span>
                                            @else
                                                <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Sem
                                                    estoque</span>
                                            @endif
                                        </p>


                                        <div class="mt-auto d-flex flex-column">
                                            <a href="{{ route('produto.show', $item->id) }}"
                                                class="btn btn-sm btn-info mb-2">
                                                <i class="fas fa-eye me-1"></i> Ver Detalhes
                                            </a>

                                            @auth
                                                @php $currentQty = $cartItems[$item->id] ?? 0; @endphp

                                                @if (in_array(auth()->user()->user_type, [0, 1, 2]))
                                                    <form action="{{ route('cart.add') }}" method="POST" class="d-flex mb-2">
                                                        @csrf
                                                        <input type="hidden" name="product_id" value="{{ $item->id }}">
                                                        <button type="submit" class="btn btn-sm btn-success flex-grow-1"
                                                            @if ($currentQty >= $item->stock) disabled @endif>
                                                            <i class="fas fa-cart-plus me-1"></i> Adicionar
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('checkout.index') }}" method="GET" class="d-flex">
                                                        <input type="hidden" name="product_id" value="{{ $item->id }}">
                                                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                                            <i class="fas fa-bolt me-1"></i> Comprar Agora
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <a href="#" class="btn btn-sm btn-warning mt-2" data-bs-toggle="modal"
                                                    data-bs-target="#loginModal">
                                                    <i class="fas fa-sign-in-alt me-1"></i> Login para Comprar
                                                </a>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $paginated->links('pagination::bootstrap-4') }}
                    </div>
                @else
                    <p class="text-muted">Nenhum produto encontrado para "{{ $query }}".</p>
                @endif
            </div>
        </div>
    </div>
@endsection
