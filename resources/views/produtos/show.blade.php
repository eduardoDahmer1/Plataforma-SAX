@extends('layout.layout')

@section('content')

@php
    $rawGallery  = is_string($product->gallery)
        ? json_decode($product->gallery, true)
        : ($product->gallery ?: []);
    $gallery     = array_values(array_filter(array_merge([$product->photo], $rawGallery)));

    $colorMap      = json_decode(file_get_contents(public_path('data/color.json')), true);
    $getColorName  = fn($hex) => $colorMap[strtoupper(trim($hex))] ?? $hex;

    $currentLocale = app()->getLocale();
    $translation   = $product->translations->where('locale', $currentLocale)->first();
    $displayName   = $translation && !empty($translation->name)    ? $translation->name    : $product->name;
    $displayDesc   = $translation && !empty($translation->details) ? $translation->details : $product->description;
@endphp

<style>
.productMainSwiper { width: 100%; aspect-ratio: 3/4; background: #f7f7f7; border: 1px solid #eee; }
.productMainSwiper .swiper-slide { display: flex; align-items: center; justify-content: center; }
.productMainSwiper img { width: 100%; height: 100%; object-fit: contain; cursor: zoom-in; }
.swiper-button-next, .swiper-button-prev { color: #000 !important; }
.swiper-button-next::after, .swiper-button-prev::after { font-size: 16px !important; }
.thumb-item { cursor: pointer; border: 1px solid #e0e0e0; aspect-ratio: 1; overflow: hidden; transition: border-color .2s; }
.thumb-item:hover { border-color: #000; }
.thumb-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.color-box-link { width: 30px; height: 30px; border-radius: 50%; border: 1px solid #ddd; padding: 2px; transition: border-color .2s; display: inline-block; }
.color-box-link.active { border-color: #000; border-width: 2px; }
.color-dot { width: 100%; height: 100%; border-radius: 50%; }
.size-box { border: 1px solid #e0e0e0; padding: 7px 14px; min-width: 44px; text-align: center; font-size: .72rem; color: #555; transition: all .2s; cursor: pointer; text-decoration: none; display: inline-block; }
.size-box:hover { border-color: #000; color: #000; }
.size-box.active { background: #000; border-color: #000; color: #fff; }
.size-box.disabled { opacity: .3; cursor: not-allowed; text-decoration: line-through; pointer-events: none; }
.extra-small { font-size: .65rem; letter-spacing: .5px; }
.breadcrumb-item + .breadcrumb-item::before { content: "/"; font-size: 10px; color: #ccc; }
@media (max-width: 991px) { .product-sticky-info { padding-top: 1.5rem; } }
</style>

<div class="product-page-wrapper">
    <div class="container-fluid px-3 px-lg-5 py-4">

        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb x-small text-uppercase mb-0">
                <li class="breadcrumb-item">
                    <a href="/" class="text-muted text-decoration-none">{{ __('messages.home') }}</a>
                </li>
                @if ($product->category)
                    <li class="breadcrumb-item">
                        <a href="{{ route('categories.show', $product->category->slug) }}" class="text-muted text-decoration-none">{{ $product->category->name }}</a>
                    </li>
                @endif
                @if ($product->subcategory)
                    <li class="breadcrumb-item">
                        <a href="{{ route('subcategories.show', $product->subcategory->slug) }}" class="text-muted text-decoration-none">{{ $product->subcategory->name }}</a>
                    </li>
                @endif
                <li class="breadcrumb-item active fw-semibold" aria-current="page">{{ $displayName }}</li>
            </ol>
        </nav>

        <div class="row g-4 g-lg-5">

            <div class="col-lg-6">
                <div class="swiper productMainSwiper mb-2">
                    <div class="swiper-wrapper">
                        @foreach ($gallery as $img)
                            <div class="swiper-slide">
                                <div class="swiper-zoom-container">
                                    <img src="{{ Storage::url($img) }}" alt="{{ $product->name }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>

                @php $thumbs = array_slice($gallery, 0, 4); @endphp
                @if (count($thumbs) > 1)
                    <div class="row g-2">
                        @foreach ($thumbs as $i => $img)
                            <div class="col-3">
                                <div class="thumb-item" onclick="window.mySwiper?.slideToLoop({{ $i }})">
                                    <img src="{{ Storage::url($img) }}" alt="Thumb {{ $i + 1 }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-lg-6">
                <div class="product-sticky-info ps-lg-3">

                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <a href="{{ route('brands.show', $product->brand->slug) }}" class="text-decoration-none">
                            <span class="text-uppercase fw-bold text-muted" style="letter-spacing: 2px; font-size: .8rem;">
                                {{ $product->brand->name ?? 'Luxury Selection' }}
                            </span>
                        </a>
                        @if ($product->stock > 0 && $product->stock <= 5)
                            <span class="text-danger extra-small fw-bold text-uppercase">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ __('messages.unicas_unidades', ['count' => $product->stock]) }}
                            </span>
                        @elseif ($product->stock > 5)
                            <span class="text-success extra-small fw-bold text-uppercase">
                                <i class="fas fa-check me-1"></i>{{ __('messages.em_estoque') }}
                            </span>
                        @endif
                    </div>

                    @auth
                        @if (auth()->user()->user_type == 1)
                            <div class="mb-2">
                                <a href="{{ route('admin.products.edit', ['product' => $product->id, 'return_to' => request()->fullUrl()]) }}"
                                   class="btn btn-sm btn-outline-warning rounded-0" style="font-size: .7rem;">
                                    <i class="fa fa-edit me-1"></i>Editar no Admin
                                </a>
                            </div>
                        @endif
                    @endauth

                    <h1 class="h4 text-uppercase fw-light mb-1" style="letter-spacing: 1px; line-height: 1.3;">{{ $product->name }}</h1>
                    <p class="text-muted extra-small mb-3">REF: {{ $product->sku }}</p>

                    <div class="mb-4">
                        @if ($product->previous_price > $product->price)
                            <span class="text-muted text-decoration-line-through me-2 small fw-light">{{ currency_format($product->previous_price) }}</span>
                        @endif
                        <span class="h4 fw-bold text-dark">{{ currency_format($product->price) }}</span>
                    </div>

                    <div class="mb-4">
                        <p class="extra-small fw-bold text-uppercase mb-2">
                            {{ __('messages.cor') }}:
                            <span class="fw-normal text-muted">{{ $product->color ? $getColorName($product->color) : __('messages.sem_cor_definida') }}</span>
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            @if (isset($colorSiblings) && $colorSiblings->count() > 0)
                                @foreach ($colorSiblings as $sib)
                                    <a href="{{ route('produto.show', $sib->slug ?? $sib->id) }}"
                                       class="color-box-link {{ $product->id == $sib->id ? 'active' : '' }}"
                                       title="{{ $sib->color ? $getColorName($sib->color) : __('messages.sem_cor_definida') }}">
                                        <div class="color-dot {{ empty($sib->color) ? 'bg-secondary' : '' }}"
                                             @if (!empty($sib->color)) style="background-color: {{ $sib->color }}" @endif></div>
                                    </a>
                                @endforeach
                            @else
                                <div class="color-box-link active">
                                    <div class="color-dot {{ empty($product->color) ? 'bg-secondary' : '' }}"
                                         @if (!empty($product->color)) style="background-color: {{ $product->color }}" @endif></div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <p class="extra-small fw-bold text-uppercase mb-0">Tamanho</p>
                            <a href="#" class="extra-small text-muted text-decoration-underline"
                               data-bs-toggle="modal" data-bs-target="#sizeGuideModal">
                                {{ __('messages.guia_de_medidas') }}
                            </a>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if (isset($siblings) && $siblings->count() > 0)
                                @foreach ($siblings as $sib)
                                    <a href="{{ route('produto.show', $sib->slug ?? $sib->id) }}"
                                       class="size-box {{ $product->id == $sib->id ? 'active' : '' }} {{ $sib->stock <= 0 ? 'disabled' : '' }}">
                                        {{ $sib->size ?: __('messages.sem_tamanho_definido') }}
                                    </a>
                                @endforeach
                            @elseif (!empty($product->size))
                                <div class="size-box active">{{ $product->size }}</div>
                            @else
                                <span class="extra-small text-muted">{{ __('messages.sem_tamanho_definido') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-5">
                        @if ($isBridal)
                            <a href="https://wa.me/{{ config('settings.whatsapp') }}?text={{ urlencode('Olá, gostaria de agendar uma consulta para o produto '.$product->name) }}"
                               target="_blank"
                               class="btn btn-outline-dark w-100 py-3 text-uppercase fw-bold rounded-0"
                               style="font-size: .75rem; letter-spacing: 1px;">
                                <i class="fab fa-whatsapp me-2"></i>{{ __('messages.agendar_consulta_bridal') }}
                            </a>
                        @elseif (Auth::check())
                            <div class="d-flex gap-2">
                                <form action="{{ route('cart.add') }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit"
                                            class="btn btn-dark w-100 py-3 text-uppercase fw-bold rounded-0"
                                            style="font-size: .75rem; letter-spacing: 1px;"
                                            {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                        {{ $product->stock > 0 ? __('messages.adicionar_ao_carrinho') : __('messages.esgotado') }}
                                    </button>
                                </form>
                                <button class="btn btn-outline-dark px-4 rounded-0" style="min-width: 56px;">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        @else
                            <a href="{{ route('login') }}"
                               class="btn btn-dark w-100 py-3 text-uppercase fw-bold rounded-0 js-requires-login"
                               style="font-size: .75rem; letter-spacing: 1px;"
                               data-redirect-to="{{ url()->current() }}">
                                {{ __('messages.login_para_comprar') }}
                            </a>
                        @endif
                    </div>

                    <div class="border-top">
                        <div class="accordion-item-sax">
                            <div class="accordion-trigger d-flex justify-content-between align-items-center py-3">
                                <span class="extra-small fw-bold text-uppercase" style="letter-spacing: 1px;">{{ __('messages.descricao_produto') }}</span>
                                <i class="fas fa-minus small text-muted"></i>
                            </div>
                            <div class="accordion-content show pb-3">
                                <div class="extra-small text-muted" style="line-height: 1.8;">{!! $displayDesc !!}</div>
                            </div>
                        </div>

                        @if ($product->attributes)
                            @php $attrs = json_decode($product->attributes, true); @endphp
                            @if (!empty($attrs))
                                <div class="accordion-item-sax border-top">
                                    <div class="accordion-trigger d-flex justify-content-between align-items-center py-3" style="cursor: pointer;">
                                        <span class="extra-small fw-bold text-uppercase" style="letter-spacing: 1px;">{{ __('messages.detalhes_tecnicos') }}</span>
                                        <i class="fas fa-plus small text-muted"></i>
                                    </div>
                                    <div class="accordion-content pb-3" style="display: none;">
                                        <table class="table table-sm table-borderless m-0 extra-small text-muted">
                                            @foreach ($attrs as $key => $value)
                                                <tr>
                                                    <td class="ps-0 fw-bold text-uppercase pe-4">{{ $key }}:</td>
                                                    <td class="text-end pe-0">{{ $value }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@if (isset($similares) && $similares->isNotEmpty())
    <section class="py-5 border-top">
        <div class="container-fluid px-3 px-lg-5">
            <h2 class="sax-section-title mb-4">{{ __('messages.artigos_similares') }}</h2>
            <div class="swiper productSwiper">
                <div class="swiper-wrapper">
                    @foreach ($similares as $item)
                        @include('home-components.product-card', ['item' => $item])
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif

@if (isset($mostViewed) && $mostViewed->isNotEmpty())
    <section class="py-5 border-top">
        <div class="container-fluid px-3 px-lg-5">
            <h2 class="sax-section-title mb-4">{{ __('messages.mais_vistos') }}</h2>
            <div class="swiper productSwiper">
                <div class="swiper-wrapper">
                    @foreach ($mostViewed as $item)
                        @include('home-components.product-card', ['item' => $item])
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif

@include('home-components.form-home')

<script>
document.addEventListener('DOMContentLoaded', function () {
    window.mySwiper = new Swiper('.productMainSwiper', {
        loop: true,
        zoom: { maxRatio: 2, toggle: true },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
});
</script>

@endsection
