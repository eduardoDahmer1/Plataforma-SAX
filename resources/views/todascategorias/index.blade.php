@extends('layout.layout')

@section('content')

<style>
.all-cats-wrapper {
    background: #fff;
    font-family: 'Helvetica Neue', Arial, sans-serif;
}

.all-cats-hero {
    border-bottom: 1px solid #f0f0f0;
    padding: 3rem 0 2rem;
}

.all-cats-hero .breadcrumb {
    margin-bottom: 1rem;
    gap: 0;
}

.all-cats-hero h1 {
    font-size: clamp(1.6rem, 4vw, 2.8rem);
    font-weight: 300;
    letter-spacing: .2em;
    text-transform: uppercase;
    color: #1a1a1a;
    margin: 0;
}

.cats-divider {
    width: 40px;
    height: 2px;
    background: #1a1a1a;
    margin-top: .75rem;
}

.cat-section {
    padding: 2.5rem 0;
    border-bottom: 1px solid #f5f5f5;
}

.cat-section:last-child {
    border-bottom: none;
}

.cat-section-title {
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: #1a1a1a;
    text-decoration: none;
    display: block;
}

.cat-section-title:hover {
    color: #555;
}

.cat-divider-line {
    height: 1px;
    background: #e8e8e8;
    flex: 1;
    margin-left: 1.5rem;
}

.subcat-card {
    padding: 1.25rem 1rem;
    border: 1px solid #f0f0f0;
    height: 100%;
    transition: border-color .2s, box-shadow .2s;
}

.subcat-card:hover {
    border-color: #d0d0d0;
    box-shadow: 0 4px 16px rgba(0,0,0,.04);
}

.subcat-name {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #1a1a1a;
    text-decoration: none;
    display: block;
    margin-bottom: .875rem;
    padding-bottom: .625rem;
    border-bottom: 1px solid #f0f0f0;
}

.subcat-name:hover { color: #555; }

.child-link {
    font-size: .7rem;
    color: #777;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: .4rem;
    padding: .2rem 0;
    transition: color .15s, padding-left .15s;
}

.child-link::before {
    content: '';
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
    opacity: .4;
}

.child-link:hover {
    color: #1a1a1a;
    padding-left: .25rem;
}

.empty-subcat {
    font-size: .68rem;
    color: #bbb;
    text-transform: uppercase;
    letter-spacing: .08em;
}

@media (max-width: 767px) {
    .all-cats-hero { padding: 2rem 0 1.5rem; }
    .cat-section   { padding: 1.75rem 0; }
    .subcat-card   { padding: .875rem .75rem; }
}
</style>

<div class="all-cats-wrapper">

    <div class="container all-cats-hero">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb x-small text-uppercase mb-2">
                <li class="breadcrumb-item">
                    <a href="/" class="text-muted text-decoration-none tracking-widest">{{ __('messages.inicio') }}</a>
                </li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">
                    {{ __('messages.categorias') }}
                </li>
            </ol>
        </nav>
        <h1>{{ __('messages.nossas_categorias') }}</h1>
        <div class="cats-divider"></div>
    </div>

    <div class="container pb-5">
        @forelse ($categories as $category)
            <section class="cat-section">

                <div class="d-flex align-items-center mb-4">
                    <a href="{{ route('categories.show', $category->slug) }}" class="cat-section-title">
                        {{ $category->name }}
                    </a>
                    <div class="cat-divider-line"></div>
                </div>

                @if ($category->subcategories->isNotEmpty())
                    <div class="row g-3">
                        @foreach ($category->subcategories as $subcategory)
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                <div class="subcat-card">
                                    <a href="{{ route('subcategories.show', $subcategory->slug ?? $subcategory->id) }}"
                                       class="subcat-name">
                                        {{ $subcategory->name }}
                                    </a>
                                    @if ($subcategory->categoriasfilhas->isNotEmpty())
                                        <ul class="list-unstyled mb-0">
                                            @foreach ($subcategory->categoriasfilhas as $filha)
                                                <li>
                                                    <a href="{{ route('categorias-filhas.show', $filha->slug ?? $filha->id) }}"
                                                       class="child-link">
                                                        {{ $filha->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="empty-subcat">{{ __('messages.nenhuma_subcategoria') }}</p>
                @endif

            </section>
        @empty
            <div class="text-center py-5">
                <p class="text-muted small">{{ __('messages.nenhuma_categoria') }}</p>
            </div>
        @endforelse
    </div>

</div>

@endsection
