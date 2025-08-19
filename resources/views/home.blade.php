@extends('layout.layout')

@section('content')
<div class="container py-4">

    <h2 class="mb-4"><i class="fas fa-home me-2"></i> Bem-vindo à Página Inicial</h2>
    <p class="text-muted">Confira os produtos mais recentes em nosso catálogo.</p>

    {{-- Alertas de sucesso --}}
    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
    </div>
    @endif

    <!-- Filtros -->
    <form id="filterForm" action="{{ url('/') }}" method="GET" class="mb-4 card card-body shadow-sm">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control"
                    placeholder="🔎 Buscar por nome ou SKU" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="brand" class="form-select">
                    <option value="">🏷️ Marcas</option>
                    @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                        {{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="category" class="form-select">
                    <option value="">📂 Categorias</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="subcategory" class="form-select">
                    <option value="">📁 Subcategorias</option>
                    @foreach($subcategories as $subcategory)
                    <option value="{{ $subcategory->id }}" {{ request('subcategory') == $subcategory->id ? 'selected' : '' }}>
                        {{ $subcategory->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="childcategory" class="form-select">
                    <option value="">🗂️ Categorias filhas</option>
                    @foreach($childcategories as $childcategory)
                    <option value="{{ $childcategory->id }}" {{ request('childcategory') == $childcategory->id ? 'selected' : '' }}>
                        {{ $childcategory->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filtrar</button>
            </div>
        </div>
    </form>

    <h4 class="mt-4 mb-3"><i class="fas fa-box-open me-2"></i> Produtos</h4>

    <div class="row">
        @foreach($paginated as $item)
        <div class="col-6 col-md-4 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm border-0">

                {{-- Imagem do produto --}}
                <img src="{{ $item->photo_url }}" class="card-img-top img-fluid rounded-top"
                    alt="{{ $item->external_name }}" style="max-height: 200px; object-fit: cover;">

                <div class="card-body d-flex flex-column">
                    {{-- Nome --}}
                    <h6 class="card-title mb-2">
                        <a href="{{ route('produto.show', $item->id) }}" class="text-decoration-none">
                            <i class="fas fa-tag me-1"></i> {{ $item->external_name ?? 'Sem nome' }}
                        </a>
                    </h6>

                    {{-- Infos --}}
                    <p class="card-text small text-muted mb-2">
                        <i class="fas fa-industry me-1"></i> {{ $item->brand->name ?? 'Sem marca' }}<br>
                        <i class="fas fa-barcode me-1"></i> {{ $item->sku ?? 'Sem SKU' }}<br>
                        <i class="fas fa-dollar-sign me-1"></i>
                        {{ isset($item->price) ? 'R$ ' . number_format($item->price, 2, ',', '.') : 'Não informado' }}
                    </p>

                    {{-- Ações --}}
                    <div class="mt-auto d-flex flex-column">
                        <a href="{{ route('produto.show', $item->id) }}" class="btn btn-sm btn-info mb-2">
                            <i class="fas fa-eye me-1"></i> Ver Detalhes
                        </a>

                        @auth
                        @php $currentQty = $cartItems[$item->id] ?? 0; @endphp

                        @if(in_array(auth()->user()->user_type, [0,1,2]))
                            {{-- Adicionar ao carrinho --}}
                            <form action="{{ route('cart.add') }}" method="POST" class="d-flex mb-2">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->id }}">
                                <button type="submit" class="btn btn-sm btn-success flex-grow-1"
                                    @if($currentQty >= $item->stock) disabled @endif>
                                    <i class="fas fa-cart-plus me-1"></i> Adicionar
                                </button>
                            </form>

                            {{-- Comprar agora --}}
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

    {{-- Paginação --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $paginated->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
