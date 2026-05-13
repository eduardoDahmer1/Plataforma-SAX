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
                @forelse ($categoriasfilhas as $child)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ route('categorias-filhas.show', $child->slug) }}" class="sax-luxury-card">
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
                {{ $categoriasfilhas->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection
