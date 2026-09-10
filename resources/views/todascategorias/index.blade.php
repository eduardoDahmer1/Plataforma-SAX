@extends('layout.layout')

@section('content')
    <x-directory-hero eyebrow="SAX Selection"
        :title="__('messages.nossas_categorias')" :description="__('messages.encontre_por_departamento')" />

    <main class="catalog-directory-page catalog-directory-page--editorial">
        <div class="container py-4 py-lg-5">
            <section class="catalog-directory-tools">
                <a href="{{ route('categories.index') }}" class="catalog-directory-action">
                    {{ __('messages.ver_categorias_com_produtos') }}
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </section>

            <div class="sax-directory-grid sax-directory-grid--tree mt-3 mt-lg-4">
                @forelse ($categories as $category)
                    <article class="sax-directory-card sax-directory-card--tree">
                        <header class="sax-directory-card__body">
                            <a href="{{ route('categories.show', $category->slug) }}" class="sax-directory-card__copy">
                                <span>{{ __('messages.categoria') }}</span>
                                <h2>{{ $category->name }}</h2>
                            </a>
                            <a href="{{ route('categories.show', $category->slug) }}" class="sax-directory-card__arrow"
                                aria-label="{{ $category->name }}">
                                <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        </header>

                        <div class="catalog-tree-list">
                            @forelse ($category->subcategories as $subcategory)
                                <div class="catalog-tree-subcategory">
                                    <a href="{{ route('subcategories.show', $subcategory->slug ?? $subcategory->id) }}"
                                        class="catalog-tree-subtitle">
                                        <span>{{ $subcategory->name }}</span>
                                        @if ($subcategory->categoriasfilhas->isNotEmpty())
                                            <i class="fas fa-chevron-down" aria-hidden="true"></i>
                                        @else
                                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                        @endif
                                    </a>

                                    @if ($subcategory->categoriasfilhas->isNotEmpty())
                                        <div class="catalog-tree-children">
                                            @foreach ($subcategory->categoriasfilhas as $filha)
                                                <a href="{{ route('categorias-filhas.show', $filha->slug ?? $filha->id) }}">
                                                    {{ $filha->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="catalog-tree-empty">{{ __('messages.nenhuma_subcategoria') }}</p>
                            @endforelse
                        </div>
                    </article>
                @empty
                    <div class="catalog-directory-empty">
                        <i class="fas fa-layer-group" aria-hidden="true"></i>
                        <strong>{{ __('messages.nenhuma_categoria') }}</strong>
                    </div>
                @endforelse
            </div>
        </div>
    </main>
@endsection
