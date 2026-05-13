@extends('layout.layout')

@section('content')
    <div class="categories-page-wrapper py-5">
        <div class="container">
            {{-- Cabeçalho Minimalista Estilo SAX --}}
            <div class="text-center mb-5">
                <h1 class="sax-title">{{ __('messages.categorias') }}</h1>
                <div class="sax-divider mx-auto"></div>
                <p class="text-muted small text-uppercase tracking-widest mt-3">
                    {{ __('messages.explore_colecoes') }}
                </p>
            </div>

            {{-- Busca Elegante --}}
            <div class="search-container mb-5">
                <form method="GET" class="mx-auto" style="max-width: 600px;">
                    <div class="sax-search-input">
                        <input type="text" name="search" 
                               placeholder="{{ __('messages.busca_colecao') }}" 
                               value="{{ request('search') }}">
                        <button type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Grid de Categorias com Padrão de Luxo --}}
            <div class="row g-2">
                @forelse ($categories as $category)
                    @if (($category->products_count ?? 0) > 0)
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="{{ route('categories.show', $category->slug) }}" class="category-sax-card">
                                {{-- Área da Imagem --}}
                                <div class="category-img-box">
                                    @if ($category->photo && Storage::disk('public')->exists($category->photo))
                                        <img src="{{ Storage::url($category->photo) }}" alt="{{ $category->name }}" loading="lazy">
                                    @else
                                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="{{ __('messages.sem_imagem') }}">
                                    @endif
                                </div>

                                {{-- Info Centralizada --}}
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
                            <p>{{ __('messages.categorias_nao_encontradas') }}</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Paginação Customizada --}}
            <div class="sax-pagination mt-5">
                {{ $categories->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection
