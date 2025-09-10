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
            {{-- Sidebar --}}
            <x-sidebar-filters :brands="$brands" :categories="$categories" :subcategories="$subcategories" :childcategories="$childcategories" />

            {{-- Lista de produtos --}}
            <div class="col-md-9">
                <h4 class="mb-3"><i class="fas fa-box-open me-2"></i> Produtos</h4>

                {{-- Dropdowns de Ordenar e Mostrar --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <form id="sortForm" method="GET">
                        {{-- Mantém todos os filtros do sidebar --}}
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="brand" value="{{ request('brand') }}">
                        <input type="hidden" name="category" value="{{ request('category') }}">
                        <input type="hidden" name="subcategory" value="{{ request('subcategory') }}">
                        <input type="hidden" name="childcategory" value="{{ request('childcategory') }}">
                        <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                        <input type="hidden" name="max_price" value="{{ request('max_price') }}">

                        <label for="sort_by" class="me-2 fw-bold">Ordenar por:</label>
                        <select name="sort_by" id="sort_by" class="form-select d-inline-block w-auto"
                            onchange="this.form.submit()">
                            <option value="">Padrão</option>
                            <option value="latest" @selected(request('sort_by') == 'latest')>Último produto</option>
                            <option value="oldest" @selected(request('sort_by') == 'oldest')>Produto mais antigo</option>
                            <option value="name_az" @selected(request('sort_by') == 'name_az')>Nome (A-Z)</option>
                            <option value="name_za" @selected(request('sort_by') == 'name_za')>Nome (Z-A)</option>
                            <option value="price_low" @selected(request('sort_by') == 'price_low')>Menor preço</option>
                            <option value="price_high" @selected(request('sort_by') == 'price_high')>Maior preço</option>
                            <option value="in_stock" @selected(request('sort_by') == 'in_stock')>Disponibilidade</option>
                        </select>

                        <label for="per_page" class="ms-3 me-2 fw-bold">Mostrar:</label>
                        <select name="per_page" id="per_page" class="form-select d-inline-block w-auto"
                            onchange="this.form.submit()">
                            <option value="25" @selected(request('per_page') == 25)>25</option>
                            <option value="35" @selected(request('per_page') == 35)>35</option>
                            <option value="45" @selected(request('per_page') == 45)>45</option>
                            <option value="55" @selected(request('per_page') == 55)>55</option>
                        </select>
                    </form>
                </div>

                @if ($paginated->count())
                    <div class="row">
                        @foreach ($paginated as $item)
                            <div class="col-6 col-md-4 col-lg-3 mb-4">
                                <div class="card h-100 shadow-sm border-0 position-relative">
                                    {{-- Imagem do produto --}}
                                    <img src="{{ $item->photo_url }}" class="card-img-top img-fluid rounded-top"
                                        alt="{{ $item->external_name }}" style="max-height: 200px; object-fit: scale-down;">

                                    {{-- Botão de favorito (aparece no hover) --}}
                                    @auth
                                        @php
                                            // Pega a quantidade do produto específico no carrinho
                                            $currentQty = $cartItems[$item->id] ?? 0;
                                        @endphp

                                        {{-- Botão de favorito --}}
                                        <form action="{{ route('user.preferences.toggle') }}" method="POST"
                                            class="card-favorite-form d-none">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item->id }}">
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                        </form>

                                        {{-- Botão de adicionar ao carrinho --}}
                                        <form action="{{ route('cart.add') }}" method="POST" class="card-add-form d-none">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item->id }}">
                                            <button type="submit" class="btn btn-success"
                                                {{ $currentQty >= $item->stock ? 'disabled' : '' }}>
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </form>
                                    @endauth

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
                                                    <form action="{{ route('checkout.index') }}" method="GET"
                                                        class="d-flex">
                                                        <input type="hidden" name="product_id" value="{{ $item->id }}">
                                                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                                            <i class="fas fa-bolt me-1"></i> Comprar Agora
                                                        </button>
                                                    </form>
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
