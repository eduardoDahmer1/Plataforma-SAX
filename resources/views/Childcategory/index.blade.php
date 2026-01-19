@extends('layout.layout')

@section('content')
    <div class="child-categories-wrapper py-5">
        <div class="container">
            {{-- Cabeçalho Minimalista --}}
            <div class="text-center mb-5">
                <h1 class="sax-title">Tipos y Modelos</h1>
                <div class="sax-divider mx-auto"></div>
                <p class="text-muted small text-uppercase tracking-widest mt-3">Refine su búsqueda por estilo y línea</p>
            </div>

            {{-- Busca Elegante --}}
            <div class="search-container mb-5">
                <form method="GET" class="mx-auto" style="max-width: 600px;">
                    <div class="sax-search-input">
                        <input type="text" name="search" placeholder="¿Qué estilo buscas?" value="{{ request('search') }}">
                        <button type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Listagem em Grid de Luxo (Padrão Marcas) --}}
            <div class="row g-4">
                @forelse ($childcategories as $child)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ route('childcategories.show', $child->slug) }}" class="sax-luxury-card">
                            {{-- Área da Imagem com efeito Grayscale --}}
                            <div class="card-img-box">
                                @if($child->photo && Storage::disk('public')->exists($child->photo))
                                    <img src="{{ Storage::url($child->photo) }}" alt="{{ $child->name }}" loading="lazy">
                                @else
                                    <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Sem imagem">
                                @endif
                            </div>

                            {{-- Informações --}}
                            <div class="card-info">
                                <span class="parent-label">
                                    {{ $child->subcategory->name ?? '' }}
                                </span>
                                <h5 class="card-item-name">{{ $child->name ?? $child->slug }}</h5>
                                <span class="product-count">{{ $child->products_count ?? $child->products->count() ?? 0 }} artículos</span>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12 py-5 text-center">
                        <div class="no-results">
                            <i class="fas fa-search mb-3"></i>
                            <p>No se encontraron estilos que coincidan con su búsqueda.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Paginação Customizada --}}
            <div class="sax-pagination mt-5">
                {{ $childcategories->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection
<style>
    /* Container Geral */
.child-categories-wrapper {
    background-color: #ffffff;
    font-family: 'Montserrat', sans-serif;
}

/* Tipografia SAX */
.sax-title {
    font-size: 2.5rem;
    font-weight: 300;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #000;
}

.sax-divider {
    width: 60px;
    height: 2px;
    background-color: #000;
    margin-top: 15px;
}

.tracking-widest {
    letter-spacing: 0.15em;
}

/* Busca Sax Style */
.sax-search-input {
    display: flex;
    border-bottom: 1px solid #e0e0e0;
    transition: border-color 0.3s ease;
}

.sax-search-input:focus-within {
    border-color: #000;
}

.sax-search-input input {
    width: 100%;
    border: none;
    padding: 12px 0;
    font-size: 1rem;
    font-weight: 300;
    background: transparent;
    outline: none;
    text-align: center;
}

.sax-search-input button {
    background: none;
    border: none;
    padding: 10px;
    color: #000;
}

/* Card de Luxo (O mesmo usado em Marcas) */
.sax-luxury-card {
    display: block;
    text-decoration: none !important;
    text-align: center;
    padding: 25px 15px;
    border: 1px solid #f2f2f2;
    background-color: #fff;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    height: 100%;
}

.sax-luxury-card:hover {
    border-color: #000;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transform: translateY(-5px);
}

.card-img-box {
    height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.card-img-box img {
    max-width: 80%;
    max-height: 80%;
    object-fit: contain;
    filter: grayscale(100%);
    transition: all 0.4s ease;
}

.sax-luxury-card:hover img {
    filter: grayscale(0%);
    transform: scale(1.05);
}

/* Info Labels */
.parent-label {
    display: block;
    font-size: 0.65rem;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}

.card-item-name {
    color: #000;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}

.product-count {
    color: #bbb;
    font-size: 0.7rem;
    text-transform: uppercase;
}

/* Paginação */
.sax-pagination .pagination {
    justify-content: center;
}
.sax-pagination .page-link {
    border: none;
    color: #000;
    background: none;
    margin: 0 5px;
}
.sax-pagination .page-item.active .page-link {
    background-color: #000;
    border-radius: 50%;
    color: #fff !important;
}
</style>