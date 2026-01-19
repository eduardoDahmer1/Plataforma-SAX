@extends('layout.layout')

@section('content')
    <div class="brands-page-wrapper py-5">
        <div class="container">
            {{-- Cabeçalho Minimalista --}}
            <div class="text-center mb-5">
                <h1 class="sax-title">Nuestras Marcas</h1>
                <div class="sax-divider mx-auto"></div>
                <p class="text-muted small text-uppercase tracking-widest mt-3">Excelencia y exclusividad en cada detalle</p>
            </div>

            {{-- Busca Elegante --}}
            <div class="search-container mb-5">
                <form method="GET" class="mx-auto" style="max-width: 600px;">
                    <div class="sax-search-input">
                        <input type="text" name="search" placeholder="¿Qué marca estás buscando?" value="{{ request('search') }}">
                        <button type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Listagem em Grid de Luxo --}}
            <div class="row g-4">
                @forelse ($brands as $brand)
                    @php
                        $imagemInvalida = empty($brand->image) || 
                                          str_contains($brand->image, 'noimage') || 
                                          !Storage::disk('public')->exists($brand->image);
                    @endphp

                    @if (($brand->products_count ?? 0) > 0 && !$imagemInvalida)
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="{{ route('brands.show', $brand->slug) }}" class="brand-sax-card">
                                <div class="brand-img-box">
                                    <img src="{{ Storage::url($brand->image) }}" alt="{{ $brand->name }}" loading="lazy">
                                </div>
                                <div class="brand-info">
                                    <h5 class="brand-name">{{ $brand->name ?? $brand->slug }}</h5>
                                    <span class="product-count">{{ $brand->products_count }} articulos</span>
                                </div>
                            </a>
                        </div>
                    @endif
                @empty
                    <div class="col-12 py-5 text-center">
                        <div class="no-results">
                            <i class="fas fa-search mb-3"></i>
                            <p>No se encontraron marcas que coincidan con su búsqueda.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Paginação Customizada --}}
            <div class="sax-pagination mt-5">
                {{ $brands->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection
<style>
    /* Configurações Gerais */
.brands-page-wrapper {
    background-color: #ffffff;
    font-family: 'Montserrat', sans-serif; /* Certifique-se de carregar esta fonte */
}

/* Título e Divisor */
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

/* Barra de Busca Premium */
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
}

.sax-search-input button {
    background: none;
    border: none;
    padding: 10px;
    color: #000;
    cursor: pointer;
}

/* Cards das Marcas */
.brand-sax-card {
    display: block;
    text-decoration: none !important;
    text-align: center;
    padding: 20px;
    border: 1px solid #f2f2f2;
    background-color: #fff;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.brand-sax-card:hover {
    border-color: #000;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transform: translateY(-5px);
}

.brand-img-box {
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.brand-img-box img {
    max-width: 80%;
    max-height: 80%;
    object-fit: contain;
    filter: grayscale(100%); /* Mantém o padrão minimalista preto e branco */
    transition: filter 0.3s ease;
}

.brand-sax-card:hover img {
    filter: grayscale(0%);
}

.brand-name {
    color: #000;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}

.product-count {
    color: #888;
    font-size: 0.75rem;
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
    color: #fff;
}

/* No Results */
.no-results {
    color: #bbb;
}

.no-results i {
    font-size: 3rem;
}
</style>