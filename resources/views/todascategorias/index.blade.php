@extends('layout.layout')

@section('content')
    <main class="catalog-directory-page">
        <div class="container py-4 py-lg-5">
            <section class="catalog-directory-hero catalog-directory-hero--general">
                <div class="catalog-directory-heading">
                    <span class="catalog-directory-eyebrow">{{ __('messages.explore_catalogo') }}</span>
                    <h1>{{ __('messages.nossas_categorias') }}</h1>
                    <p>{{ __('messages.encontre_por_departamento') }}</p>
                </div>
                <a href="{{ route('categories.index') }}" class="catalog-directory-action">
                    {{ __('messages.ver_categorias_com_produtos') }}
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </section>

            <div class="catalog-tree-grid mt-3 mt-lg-4">
                @forelse ($categories as $category)
                    <article class="catalog-tree-card">
                        <a href="{{ route('categories.show', $category->slug) }}" class="catalog-tree-title">
                            <span>{{ $category->name }}</span>
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>

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
