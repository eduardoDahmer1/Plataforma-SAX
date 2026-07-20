@extends('layout.layout')

@section('content')
    <main class="catalog-directory-page">
        <div class="container py-4 py-lg-5">
            <section class="catalog-directory-hero">
                <div class="catalog-directory-heading">
                    <span class="catalog-directory-eyebrow">{{ __('messages.explore_catalogo') }}</span>
                    <h1>{{ __('messages.categorias') }}</h1>
                    <p>{{ __('messages.explore_colecoes') }}</p>
                </div>

                <form method="GET" action="{{ route('categories.index') }}" class="catalog-directory-search" role="search">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="search" name="search" placeholder="{{ __('messages.busca_colecao') }}"
                        value="{{ request('search') }}" aria-label="{{ __('messages.busca_colecao') }}">
                    @if (request('search'))
                        <a href="{{ route('categories.index') }}" aria-label="{{ __('messages.limpar_busca') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </a>
                    @endif
                    <button type="submit">{{ __('messages.buscar') }}</button>
                </form>
            </section>

            <div class="catalog-category-grid mt-3 mt-lg-4">
                @forelse ($categories as $category)
                    @if (($category->products_count ?? 0) > 0)
                        <a href="{{ route('categories.show', $category->slug) }}" class="catalog-category-card">
                            <div class="catalog-category-image">
                                @if ($category->photo && Storage::disk('public')->exists($category->photo))
                                    <img src="{{ Storage::url($category->photo) }}" alt="{{ $category->name }}" loading="lazy">
                                @else
                                    <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="{{ __('messages.sem_imagem') }}" loading="lazy">
                                @endif
                            </div>
                            <div class="catalog-category-info">
                                <div>
                                    <span>{{ __('messages.colecao') }}</span>
                                    <h2>{{ $category->name ?? $category->slug }}</h2>
                                    <small>{{ trans_choice('messages.produtos_disponiveis', $category->products_count, ['count' => $category->products_count]) }}</small>
                                </div>
                                <span class="catalog-category-arrow"><i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                            </div>
                        </a>
                    @endif
                @empty
                    <div class="catalog-directory-empty">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <strong>{{ __('messages.categorias_nao_encontradas') }}</strong>
                        <a href="{{ route('categories.index') }}">{{ __('messages.limpar_busca') }}</a>
                    </div>
                @endforelse
            </div>

            <div class="sax-pagination mt-4 mt-lg-5">
                {{ $categories->appends(request()->input())->links() }}
            </div>
        </div>
    </main>
@endsection
