@extends('layout.layout')

@section('content')
    <div class="brand-detail-wrapper">
        {{-- Header da Marca --}}
        <div class="brand-header pt-4">
            <div class="container text-center">
                <a href="{{ route('brands.index') }}" class="back-link">
                    <i class="fas fa-chevron-left me-1"></i> VOLVER
                </a>
                
                {{-- Logo Centralizada --}}
                <div class="brand-logo-main mt-3 mb-4">
                    @if ($brand->image && Storage::disk('public')->exists($brand->image))
                        <img src="{{ Storage::url($brand->image) }}" alt="{{ $brand->name }}" class="main-logo-img">
                    @else
                        <h1 class="sax-brand-title">{{ $brand->name }}</h1>
                    @endif
                </div>
            </div>

            {{-- Banner 100% Width e Height Ajustado --}}
            @if($brand->internal_banner || $brand->banner)
                <div class="brand-banner-fullwidth">
                    {{-- Desktop: Prioriza Internal Banner --}}
                    <div class="d-none d-md-block">
                        <img src="{{ Storage::url($brand->internal_banner ?? $brand->banner) }}" 
                             class="banner-img-render" alt="{{ $brand->name }}">
                    </div>

                    {{-- Mobile: Prioriza Banner Normal --}}
                    <div class="d-block d-md-none">
                        <img src="{{ Storage::url($brand->banner ?? $brand->internal_banner) }}" 
                             class="banner-img-render" alt="{{ $brand->name }}">
                    </div>
                </div>
            @endif
        </div>

        {{-- Seção de Produtos --}}
        <div class="container-fluid px-1 py-4">
            @if ($products->count())
                <div class="row g-1">
                    @foreach ($products as $item)
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="{{ route('produto.show', $item->id) }}" class="text-decoration-none">
                                <div class="card h-100 border-0 rounded-0 jw-product-card">
                                    
                                    <div class="jw-img-container position-relative">
                                        <img src="{{ $item->photo_url }}" 
                                             class="card-img-top img-fluid rounded-0" 
                                             alt="{{ $item->external_name }}">

                                        <div class="position-absolute top-0 end-0 p-3">
                                            @auth
                                                <x-product-favorite-button :item="$item" />
                                            @endauth
                                        </div>
                                    </div>

                                    <div class="card-body px-3 py-4">
                                        <div class="jw-brand fw-bold text-uppercase mb-1">
                                            {{ $brand->name }}
                                        </div>
                                        <div class="jw-product-name text-muted mb-2">
                                            {{ Str::limit($item->external_name, 35) }}
                                        </div>
                                        <div class="jw-price fw-bold text-dark">
                                            {{ isset($item->price) ? currency_format($item->price) : '0,00' }}
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-5">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <p class="text-muted">No se encontraron productos para esta marca.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

<style>
    .brand-detail-wrapper { background-color: #fff; overflow-x: hidden; }

    /* Logo Ajustes */
    .main-logo-img { max-height: 7em; width: auto; object-fit: contain; }
    .back-link {
        color: #888;
        font-size: 0.65rem;
        letter-spacing: 2px;
        text-decoration: none;
        text-transform: uppercase;
    }

    /* BANNER AJUSTADO: 100% largura e Altura Controlada */
    .brand-banner-fullwidth {
        width: 100% !important;
        margin: 0;
        padding: 0;
        line-height: 0; /* Remove espaços fantasmas abaixo da imagem */
    }

    .banner-img-render {
        width: 100% !important;
        height: 350px !important; /* Ajuste aqui a altura desejada para desktop */
        object-fit: cover; /* Faz a imagem preencher o espaço sem esticar */
        object-position: center; /* Centraliza o foco da imagem */
        display: block;
    }

    /* Grid de Produtos estilo "Colado" */
    .container-fluid {
        padding-left: 2px !important;
        padding-right: 2px !important;
    }

    .jw-product-card {
        background-color: #f8f8f8 !important;
        transition: opacity 0.3s;
    }

    .jw-product-card:hover { opacity: 0.9; }

    .jw-img-container {
        aspect-ratio: 3 / 4;
        background-color: #f8f8f8;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .jw-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Tipografia */
    .jw-brand { font-size: 0.7rem; letter-spacing: 1px; color: #000; }
    .jw-product-name { font-size: 0.75rem; color: #666 !important; }
    .jw-price { font-size: 0.85rem; color: #000; margin-top: 4px; }

    /* Responsividade para Mobile */
    @media (max-width: 768px) {
        .banner-img-render {
            height: 200px !important; /* Altura menor no celular */
        }
        
        .g-1 > div {
            padding: 1px !important; /* Espaçamento mínimo entre cards no mobile */
        }
    }
</style>