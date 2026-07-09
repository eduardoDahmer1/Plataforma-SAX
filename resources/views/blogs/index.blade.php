@extends('layout.layout')

@section('title', 'SAX News')
@section('meta_description', 'Notícias, novidades e histórias do universo SAX em uma experiência editorial mais leve e elegante.')

@section('content')
@php
    $hasFilters = $search !== '' || filled($currentCategory);
@endphp

<div class="blog-page">
    <section class="blog-hero">
        <div class="container">
            <div class="blog-hero__top">
                <div>
                    <span class="blog-kicker">SAX News</span>
                    <h1>Conteúdo editorial com ritmo mais leve e visual limpo.</h1>
                    <p>Uma curadoria de histórias, novidades e bastidores pensada para leitura rápida, navegação simples e foco total no conteúdo.</p>
                </div>
                <form method="GET" class="blog-search-bar">
                    @if ($currentCategory)
                        <input type="hidden" name="category" value="{{ $currentCategory }}">
                    @endif
                    <input type="search" name="search" id="blog-live-search" value="{{ $search }}" placeholder="Buscar artigos" autocomplete="off">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="blog-filters">
                <a href="{{ route('blogs.index') }}" class="blog-filter-chip {{ !$currentCategory ? 'active' : '' }}">{{ __('messages.todos') }}</a>
                @foreach ($categories as $cat)
                    <a href="{{ route('blogs.index', ['category' => $cat->id]) }}" class="blog-filter-chip {{ (string) $currentCategory === (string) $cat->id ? 'active' : '' }}">
                        @if ($cat->banner)
                            <img src="{{ Storage::url($cat->banner) }}" alt="" class="blog-filter-chip__avatar">
                        @endif
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="blog-grid-section">
        <div class="container">
            <div id="blog-featured-wrapper">
                @include('blogs.partials.featured', ['featuredBlog' => $featuredBlog, 'hasFilters' => $hasFilters])
            </div>

            <div id="blog-cards-wrapper">
                @include('blogs.partials.cards', ['blogs' => $blogs, 'featuredBlog' => $featuredBlog, 'hasFilters' => $hasFilters])
            </div>

            <div id="blog-pagination-wrapper">
                @include('blogs.partials.pagination', ['blogs' => $blogs])
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('blog-live-search');
    const featuredWrapper = document.getElementById('blog-featured-wrapper');
    const cardsWrapper = document.getElementById('blog-cards-wrapper');
    const paginationWrapper = document.getElementById('blog-pagination-wrapper');
    const categoryLinks = document.querySelectorAll('.blog-filters .blog-filter-chip');
    const baseUrl = @json(route('blogs.ajax-search'));

    if (!searchInput || !cardsWrapper || !paginationWrapper) {
        return;
    }

    let debounceTimer = null;
    let activeController = null;
    let currentCategory = @json($currentCategory);

    const setBusyState = (busy) => {
        cardsWrapper.classList.toggle('is-loading', busy);
    };

    const updateUrl = (search, category, page = 1) => {
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (category) params.set('category', category);
        if (page > 1) params.set('page', String(page));
        const query = params.toString();
        const target = query ? `{{ route('blogs.index') }}?${query}` : `{{ route('blogs.index') }}`;
        window.history.replaceState({}, '', target);
    };

    const fetchBlogs = async ({ search = '', category = '', page = 1 } = {}) => {
        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (category) params.set('category', category);
        params.set('page', String(page));

        setBusyState(true);

        try {
            const response = await fetch(`${baseUrl}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                signal: activeController.signal,
            });

            if (!response.ok) {
                throw new Error('Falha na busca do blog.');
            }

            const payload = await response.json();

            featuredWrapper.innerHTML = payload.featured || '';
            cardsWrapper.innerHTML = payload.cards || '';
            paginationWrapper.innerHTML = payload.pagination || '';

            updateUrl(search, category, page);
            bindPaginationLinks();
            syncCategoryActiveState(category);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            setBusyState(false);
        }
    };

    const syncCategoryActiveState = (category) => {
        categoryLinks.forEach((link) => {
            const url = new URL(link.href);
            const linkCategory = url.searchParams.get('category');
            const isAll = !category && !linkCategory;
            const isCurrent = category && linkCategory === String(category);
            link.classList.toggle('active', Boolean(isAll || isCurrent));
        });
    };

    const bindPaginationLinks = () => {
        paginationWrapper.querySelectorAll('a.page-link').forEach((link) => {
            link.addEventListener('click', function (event) {
                event.preventDefault();

                const url = new URL(this.href);
                const page = Number(url.searchParams.get('page') || 1);
                fetchBlogs({
                    search: searchInput.value.trim(),
                    category: currentCategory || '',
                    page,
                });
            });
        });
    };

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchBlogs({
                search: searchInput.value.trim(),
                category: currentCategory || '',
                page: 1,
            });
        }, 250);
    });

    categoryLinks.forEach((link) => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            const url = new URL(this.href);
            currentCategory = url.searchParams.get('category') || '';

            fetchBlogs({
                search: searchInput.value.trim(),
                category: currentCategory,
                page: 1,
            });
        });
    });

    bindPaginationLinks();
});
</script>
@endpush