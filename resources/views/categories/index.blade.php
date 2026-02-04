@extends('layout.layout')

@section('content')
    <div class="categories-page-wrapper py-5">
        <div class="container">
            {{-- Cabeçalho Minimalista Estilo SAX --}}
            <div class="text-center mb-5">
                <h1 class="sax-title">Categorías</h1>
                <div class="sax-divider mx-auto"></div>
                <p class="text-muted small text-uppercase tracking-widest mt-3">Explore nuestras colecciones exclusivas</p>
            </div>

            {{-- Busca Elegante --}}
            <div class="search-container mb-5">
                <form method="GET" class="mx-auto" style="max-width: 600px;">
                    <div class="sax-search-input">
                        <input type="text" name="search" placeholder="¿Qué colección buscas?" value="{{ request('search') }}">
                        <button type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Grid de Categorias com Padrão de Luxo --}}
            <div class="row g-2"> {{-- g-2 para um respiro elegante entre blocos cinzas --}}
                @forelse ($categories as $category)
                    @if (($category->products_count ?? 0) > 0)
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="{{ route('categories.show', $category->slug) }}" class="category-sax-card">
                                {{-- Área da Imagem com o mesmo efeito das Marcas --}}
                                <div class="category-img-box">
                                    @if ($category->photo && Storage::disk('public')->exists($category->photo))
                                        <img src="{{ Storage::url($category->photo) }}" alt="{{ $category->name }}" loading="lazy">
                                    @else
                                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Sin imagen">
                                    @endif
                                </div>

                                {{-- Info Centralizada e Minimalista --}}
                                <div class="category-info">
                                    <h5 class="category-name">{{ $category->name ?? $category->slug }}</h5>
                                </div>
                            </a>
                        </div>
                    @endif
                @empty
                    <div class="col-12 py-5 text-center">
                        <div class="no-results">
                            <i class="fas fa-search mb-3"></i>
                            <p>No se encontraron categorías que coincidan con su búsqueda.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Paginação Customizada Padrão Marcas --}}
            <div class="sax-pagination mt-5">
                {{ $categories->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection
<style>
    /* Configurações Gerais - IDÊNTICO A MARCAS */
.categories-page-wrapper {
    background-color: #ffffff;
    font-family: 'Montserrat', sans-serif;
}

/* Título e Divisor - IDÊNTICO A MARCAS */
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
    text-align: center;
}

.sax-search-input button {
    background: none;
    border: none;
    padding: 10px;
    color: #000;
}

/* Cards das Categorias - Seguindo o estilo das Marcas */
.category-sax-card {
    display: block;
    text-decoration: none !important;
    text-align: center;
    padding: 30px 20px;
    border: 1px solid #f2f2f2;
    background-color: #fff;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    height: 100%;
}

.category-sax-card:hover {
    border-color: #000;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transform: translateY(-5px);
}

.category-img-box {
    height: 150px; /* Um pouco maior que marcas para categorias */
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    background-color: #fcfcfc; /* Fundo muito leve para as fotos */
    overflow: hidden;
}

.category-img-box img {
    max-width: 85%;
    max-height: 85%;
    object-fit: contain;
    filter: grayscale(100%); /* Efeito Premium unificado */
    transition: all 0.4s ease;
}

.category-sax-card:hover img {
    filter: grayscale(0%);
    transform: scale(1.05);
}

.category-name {
    color: #000;
    font-size: 0.95rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 5px;
}

.product-count {
    color: #888;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Paginação Unificada */
.sax-pagination .pagination {
    justify-content: center;
    border: none;
}

.sax-pagination .page-link {
    border: none;
    color: #000;
    background: none;
    margin: 0 5px;
    font-weight: 300;
}

.sax-pagination .page-item.active .page-link {
    background-color: #000;
    border-radius: 50%;
    color: #fff !important;
}

/* No Results */
.no-results {
    color: #bbb;
}
.no-results i {
    font-size: 3rem;
}
</style>