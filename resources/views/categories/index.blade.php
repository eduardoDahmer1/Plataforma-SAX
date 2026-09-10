@extends('layout.layout')

@section('content')
    <x-directory-hero eyebrow="SAX Selection"
        :title="__('messages.categorias')" :description="__('messages.explore_colecoes')" />

    <main class="catalog-directory-page catalog-directory-page--editorial">
        <div class="container py-4 py-lg-5">
            <section class="catalog-directory-tools">
                <x-directory-search :action="route('categories.index')" :placeholder="__('messages.busca_colecao')"
                    :value="request('search', '')" :clear-url="route('categories.index')" />
            </section>

            <div class="sax-directory-grid mt-3 mt-lg-4">
                @forelse ($categories as $category)
                    @if (($category->products_count ?? 0) > 0)
                        <a href="{{ route('categories.show', $category->slug) }}" class="sax-directory-card">
                            <div class="sax-directory-card__media">
                                @if ($category->photo && Storage::disk('public')->exists($category->photo))
                                    <img src="{{ Storage::url($category->photo) }}" alt="{{ $category->name }}" loading="lazy">
                                @else
                                    <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="{{ __('messages.sem_imagem') }}" loading="lazy">
                                @endif
                            </div>
                            <div class="sax-directory-card__body">
                                <div class="sax-directory-card__copy">
                                    <span>{{ __('messages.colecao') }}</span>
                                    <h2>{{ $category->name ?? $category->slug }}</h2>
                                    <small>{{ trans_choice('messages.produtos_disponiveis', $category->products_count, ['count' => $category->products_count]) }}</small>
                                </div>
                                <span class="sax-directory-card__arrow"><i class="fas fa-arrow-right" aria-hidden="true"></i></span>
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
