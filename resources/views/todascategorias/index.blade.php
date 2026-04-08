@extends('layout.layout')

@section('content')
<div class="all-categories-wrapper bg-white">
    {{-- Header da Página --}}
    <div class="container pt-5 pb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item">
                    <a href="/" class="text-decoration-none text-muted small uppercase tracking-2">
                        {{ __('messages.inicio') }}
                    </a>
                </li>
                <li class="breadcrumb-item active small uppercase tracking-2" aria-current="page">
                    {{ __('messages.categorias') }}
                </li>
            </ol>
        </nav>
        <h1 class="display-5 fw-light text-uppercase tracking-5 mb-0">
            {{ __('messages.nossas_categorias') }}
        </h1>
        <div class="title-separator mt-3"></div>
    </div>

    <div class="container pb-5">
        @foreach($categories as $category)
            <section class="category-section mb-5">
                {{-- Título da Categoria Pai --}}
                <div class="category-header d-flex align-items-center mb-4">
                    <a href="{{ route('categories.show', $category->slug) }}">
                        <h2 class="fw-bold text-uppercase m-0 h4 tracking-2">{{ $category->name }}</h2>
                    </a>
                    <div class="flex-grow-1 ms-4 border-bottom opacity-10"></div>
                </div>

                <div class="row g-4">
                    @foreach($category->subcategories as $subcategory)
                        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                            <div class="subcategory-card h-100 p-3">
                                {{-- Subcategoria --}}
                                <h3 class="h6 fw-bold mb-3 text-uppercase">
                                    <a href="{{ route('subcategories.show', $subcategory->slug ?? $subcategory->id) }}" class="category-main-link">
                                        {{ $subcategory->name }}
                                    </a>
                                </h3>

                                {{-- Lista de Categorias Filhas --}}
                                <ul class="list-unstyled mb-0">
                                    @foreach($subcategory->categoriasfilhas as $filha)
                                        <li class="mb-2">
                                            <a href="{{ route('categorias-filhas.show', $filha->slug ?? $filha->id) }}" class="child-item-link">
                                                {{ $filha->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection

<style>
    /* Configurações de Tipografia e Espaçamento */
    .tracking-2 { letter-spacing: 2px; }
    .tracking-5 { letter-spacing: 5px; }
    .uppercase { text-transform: uppercase; }
    
    .all-categories-wrapper {
        min-height: 100vh;
    }
    a{
        text-decoration: none !important;
    }
    .category-header h2{
        color: #000
    }

    .title-separator {
        width: 60px;
        height: 3px;
        background-color: #000;
    }

    /* Estilização dos Links */
    .category-main-link {
        color: #000;
        text-decoration: none;
        position: relative;
        transition: color 0.3s ease;
    }

    .category-main-link:hover {
        color: #555;
    }

    .child-item-link {
        color: #888;
        text-decoration: none;
        font-size: 0.85rem;
        display: block;
        transition: all 0.3s ease;
        font-weight: 300;
    }

    .child-item-link:hover {
        color: #000;
        transform: translateX(5px);
    }

    /* Card Sutil */
    .subcategory-card {
        border-left: 1px solid #f0f0f0;
        transition: border-color 0.3s ease;
    }

    .subcategory-card:hover {
        border-left-color: #000;
    }

    /* Ajustes de Responsividade Extra */
    @media (max-width: 768px) {
        .category-header h2 {
            font-size: 1.1rem;
        }
        .display-5 {
            font-size: 1.8rem;
        }
        .subcategory-card {
            border-left: none;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 1.5rem;
        }
    }
</style>