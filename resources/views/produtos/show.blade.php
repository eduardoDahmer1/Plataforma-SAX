@extends('layout.layout')

@section('content')

@php
    $rawGallery  = is_string($product->gallery)
        ? json_decode($product->gallery, true)
        : ($product->gallery ?: []);
    $gallery     = array_values(array_filter(array_merge([$product->photo], $rawGallery)));

    $colorMap = json_decode(file_get_contents(public_path('data/color.json')), true);

    $normalizeHex = function ($hex) {
        if (empty($hex)) {
            return null;
        }

        $value = strtoupper(trim((string) $hex));
        if (!str_starts_with($value, '#')) {
            $value = '#' . $value;
        }

        if (!preg_match('/^#[0-9A-F]{6}$/', $value)) {
            return null;
        }

        return $value;
    };

    $rgbFromHex = function (string $hex): array {
        return [
            hexdec(substr($hex, 1, 2)),
            hexdec(substr($hex, 3, 2)),
            hexdec(substr($hex, 5, 2)),
        ];
    };

    $nearestColorName = function ($hex) use ($colorMap, $normalizeHex, $rgbFromHex) {
        static $cache = [];

        $normalized = $normalizeHex($hex);
        if (!$normalized) {
            return null;
        }

        if (isset($cache[$normalized])) {
            return $cache[$normalized];
        }

        [$r1, $g1, $b1] = $rgbFromHex($normalized);
        $bestName = null;
        $bestDist = PHP_INT_MAX;

        foreach ($colorMap as $knownHex => $knownName) {
            $known = $normalizeHex($knownHex);
            if (!$known) {
                continue;
            }

            [$r2, $g2, $b2] = $rgbFromHex($known);
            $dist = (($r1 - $r2) ** 2) + (($g1 - $g2) ** 2) + (($b1 - $b2) ** 2);

            if ($dist < $bestDist) {
                $bestDist = $dist;
                $bestName = $knownName;
            }
        }

        $cache[$normalized] = $bestName;
        return $bestName;
    };

    $getColorName = function ($hex) use ($colorMap, $normalizeHex, $nearestColorName) {
        $normalized = $normalizeHex($hex);
        if (!$normalized) {
            return null;
        }

        if (isset($colorMap[$normalized])) {
            return $colorMap[$normalized];
        }

        $closest = $nearestColorName($normalized);
        return $closest ? ($closest . ' (aprox.)') : $normalized;
    };

    $sizeOptions = isset($siblings) && $siblings->count() > 0
        ? $siblings->values()
        : collect([$product]);
    $visibleSizeOptions = $sizeOptions->take(4);
    $hasHiddenSizes = $sizeOptions->count() > 4;

    $sizeCatalog = json_decode(file_get_contents(public_path('data/tamanho.json')), true) ?? [];

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
.size-selector { gap: .45rem !important; }
.product-page-wrapper .size-box { border: 1px solid #dadada; padding: 0 12px; height: 34px; min-width: 42px; text-align: center; font-size: .7rem; color: #4f4f4f; transition: all .2s; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; line-height: 1; width: auto !important; flex: 0 0 auto; border-radius: 2px; }
.product-page-wrapper .size-box:hover { border-color: #000; color: #000; }
.product-page-wrapper .size-box.active { background: #000; border-color: #000; color: #fff; }
.product-page-wrapper .size-box.disabled { opacity: .35; cursor: not-allowed; text-decoration: line-through; pointer-events: none; }
.extra-small { font-size: .65rem; letter-spacing: .5px; }
.breadcrumb-item + .breadcrumb-item::before { content: "/"; font-size: 10px; color: #ccc; }
.size-more-btn { border: 1px dashed #111; background: #fff; color: #111; height: 34px; padding: 0 12px; min-width: 88px; font-size: .66rem; text-transform: uppercase; letter-spacing: .5px; display: inline-flex; align-items: center; justify-content: center; }
.size-more-btn:hover { background: #111; color: #fff; }
.size-guide-table thead th { font-size: .68rem; letter-spacing: .6px; text-transform: uppercase; color: #666; border-bottom: 1px solid #e9e9e9; }
.size-guide-table tbody td { font-size: .75rem; color: #333; vertical-align: middle; }
.size-guide-badge { display: inline-block; font-size: .62rem; letter-spacing: .5px; text-transform: uppercase; color: #666; background: #f2f2f2; border: 1px solid #e5e5e5; padding: 4px 8px; margin: 0 6px 6px 0; }
.size-guide-note { font-size: .72rem; color: #666; line-height: 1.7; }
.buy-actions { gap: .5rem !important; }
.add-to-cart-btn,
.login-buy-btn,
.bridal-btn { min-height: 52px; padding: 10px 14px !important; font-size: .72rem !important; letter-spacing: .8px !important; }
.wishlist-btn { min-width: 52px !important; min-height: 52px; padding: 0 !important; display: inline-flex; align-items: center; justify-content: center; }
@media (max-width: 991px) {
    .product-sticky-info { padding-top: 1.5rem; }
    .product-page-wrapper .size-box { height: 32px; min-width: 40px; padding: 0 10px; font-size: .68rem; }
    .size-more-btn { height: 32px; min-width: 78px; padding: 0 10px; }
    .add-to-cart-btn,
    .login-buy-btn,
    .bridal-btn { min-height: 46px; font-size: .68rem !important; letter-spacing: .7px !important; }
    .wishlist-btn { min-width: 46px !important; min-height: 46px; }
}
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
                        <div class="d-flex flex-wrap size-selector">
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
                            @if ($sizeOptions->isNotEmpty())
                                @foreach ($visibleSizeOptions as $sib)
                                    @php
                                        $isCurrent = (int) $product->id === (int) $sib->id;
                                        $sizeLabel = $sib->size ?: __('messages.sem_tamanho_definido');
                                        $isOut = ($sib->stock ?? 0) <= 0;
                                    @endphp
                                    @if ($isOut)
                                        <span class="size-box {{ $isCurrent ? 'active' : '' }} disabled">{{ $sizeLabel }}</span>
                                    @else
                                        <a href="{{ route('produto.show', $sib->slug ?? $sib->id) }}"
                                           class="size-box {{ $isCurrent ? 'active' : '' }}">
                                            {{ $sizeLabel }}
                                        </a>
                                    @endif
                                @endforeach

                                @if ($hasHiddenSizes)
                                    <button type="button" class="size-more-btn" data-bs-toggle="modal" data-bs-target="#allSizesModal">
                                        Ver todos
                                    </button>
                                @endif
                            @else
                                <span class="extra-small text-muted">{{ __('messages.sem_tamanho_definido') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-5">
                        @if ($isBridal)
                            <a href="https://wa.me/{{ config('settings.whatsapp') }}?text={{ urlencode('Olá, gostaria de agendar uma consulta para o produto '.$product->name) }}"
                               target="_blank"
                               class="btn btn-outline-dark w-100 text-uppercase fw-bold rounded-0 bridal-btn">
                                <i class="fab fa-whatsapp me-2"></i>{{ __('messages.agendar_consulta_bridal') }}
                            </a>
                        @elseif (Auth::check())
                            <div class="d-flex buy-actions">
                                <form action="{{ route('cart.add') }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit"
                                            class="btn btn-dark w-100 text-uppercase fw-bold rounded-0 add-to-cart-btn"
                                            {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                        {{ $product->stock > 0 ? __('messages.adicionar_ao_carrinho') : __('messages.esgotado') }}
                                    </button>
                                </form>
                                <button class="btn btn-outline-dark rounded-0 wishlist-btn">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        @else
                            <a href="{{ route('login') }}"
                               class="btn btn-dark w-100 text-uppercase fw-bold rounded-0 js-requires-login login-buy-btn"
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

<div class="modal fade" id="sizeGuideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content rounded-0 border-0 shadow">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title text-uppercase mb-1" style="font-size:.95rem; letter-spacing:.9px;">Guia de medidas</h5>
                    <p class="mb-0 size-guide-note">Referência aproximada para ajudar na escolha do tamanho. Pode variar conforme modelagem e marca.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="row g-4">
                    <div class="col-12">
                        <h6 class="text-uppercase fw-bold mb-2" style="font-size:.78rem; letter-spacing:.8px;">Como medir em casa</h6>
                        <p class="size-guide-note mb-2"><strong>Peito/Busto:</strong> contorne a parte mais larga do tórax com fita métrica, sem apertar.</p>
                        <p class="size-guide-note mb-2"><strong>Cintura:</strong> meça a parte mais fina do abdômen, mantendo postura reta.</p>
                        <p class="size-guide-note mb-2"><strong>Quadril:</strong> meça na parte mais larga do quadril/glúteos.</p>
                        <p class="size-guide-note mb-2"><strong>Comprimento do braço:</strong> do ombro até o punho, com braço levemente flexionado.</p>
                        <p class="size-guide-note mb-0"><strong>Calçado:</strong> meça o comprimento do pé do calcanhar até o dedo mais longo.</p>
                    </div>

                    <div class="col-12 col-lg-6">
                        <h6 class="text-uppercase fw-bold mb-2" style="font-size:.78rem; letter-spacing:.8px;">Feminino (aprox.)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle size-guide-table">
                                <thead>
                                    <tr>
                                        <th>Tam</th>
                                        <th>Brasil</th>
                                        <th>Busto (cm)</th>
                                        <th>Cintura (cm)</th>
                                        <th>Quadril (cm)</th>
                                        <th>Braço (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>XS</td><td>34-36</td><td>80-84</td><td>60-66</td><td>86-92</td><td>56-58</td></tr>
                                    <tr><td>S</td><td>36-38</td><td>84-90</td><td>66-72</td><td>92-98</td><td>58-60</td></tr>
                                    <tr><td>M</td><td>40-42</td><td>90-98</td><td>72-80</td><td>98-106</td><td>60-62</td></tr>
                                    <tr><td>L</td><td>44-46</td><td>98-106</td><td>80-88</td><td>106-114</td><td>62-64</td></tr>
                                    <tr><td>XL/XXL</td><td>48-52</td><td>106-120</td><td>88-102</td><td>114-126</td><td>64-66</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <h6 class="text-uppercase fw-bold mb-2" style="font-size:.78rem; letter-spacing:.8px;">Masculino (aprox.)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle size-guide-table">
                                <thead>
                                    <tr>
                                        <th>Tam</th>
                                        <th>Brasil</th>
                                        <th>Peito (cm)</th>
                                        <th>Cintura (cm)</th>
                                        <th>Calça</th>
                                        <th>Braço (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>S</td><td>36-38</td><td>88-96</td><td>74-82</td><td>38-40</td><td>60-62</td></tr>
                                    <tr><td>M</td><td>40-42</td><td>96-104</td><td>82-90</td><td>42-44</td><td>62-64</td></tr>
                                    <tr><td>L</td><td>44-46</td><td>104-112</td><td>90-98</td><td>46-48</td><td>64-66</td></tr>
                                    <tr><td>XL</td><td>48-50</td><td>112-120</td><td>98-106</td><td>50-52</td><td>66-68</td></tr>
                                    <tr><td>XXL</td><td>52-56</td><td>120-132</td><td>106-118</td><td>54-58</td><td>68-70</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <h6 class="text-uppercase fw-bold mb-2" style="font-size:.78rem; letter-spacing:.8px;">Infantil (aprox.)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle size-guide-table">
                                <thead>
                                    <tr>
                                        <th>Tam</th>
                                        <th>Idade</th>
                                        <th>Altura (cm)</th>
                                        <th>Peito (cm)</th>
                                        <th>Cintura (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>RN</td><td>Recém-nascido</td><td>45-52</td><td>38-42</td><td>38-42</td></tr>
                                    <tr><td>3M-6M</td><td>3 a 6 meses</td><td>58-68</td><td>42-46</td><td>42-46</td></tr>
                                    <tr><td>9M-12M</td><td>9 a 12 meses</td><td>68-76</td><td>46-50</td><td>45-49</td></tr>
                                    <tr><td>18M-24M</td><td>18 a 24 meses</td><td>76-90</td><td>50-54</td><td>48-52</td></tr>
                                    <tr><td>2Y-4Y</td><td>2 a 4 anos</td><td>90-104</td><td>54-58</td><td>52-55</td></tr>
                                    <tr><td>5Y-8Y</td><td>5 a 8 anos</td><td>104-128</td><td>58-66</td><td>55-61</td></tr>
                                    <tr><td>10Y-14Y</td><td>10 a 14 anos</td><td>134-164</td><td>68-82</td><td>62-70</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <h6 class="text-uppercase fw-bold mb-2" style="font-size:.78rem; letter-spacing:.8px;">Calçados (aprox.)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle size-guide-table">
                                <thead>
                                    <tr>
                                        <th>BR</th>
                                        <th>Comprimento do pé (cm)</th>
                                        <th>US (ref.)</th>
                                        <th>EU (ref.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>33-34</td><td>21.5-22.5</td><td>4-5</td><td>35-36</td></tr>
                                    <tr><td>35-36</td><td>22.5-23.8</td><td>5.5-6.5</td><td>37-38</td></tr>
                                    <tr><td>37-38</td><td>23.8-25.0</td><td>7-8</td><td>39-40</td></tr>
                                    <tr><td>39-40</td><td>25.0-26.3</td><td>8.5-9.5</td><td>41-42</td></tr>
                                    <tr><td>41-42</td><td>26.3-27.5</td><td>10-11</td><td>43-44</td></tr>
                                    <tr><td>43-44</td><td>27.5-28.8</td><td>11.5-12.5</td><td>45-46</td></tr>
                                    <tr><td>45-46</td><td>28.8-30.0</td><td>13-14</td><td>47-48</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12">
                        <h6 class="text-uppercase fw-bold mb-2" style="font-size:.78rem; letter-spacing:.8px;">Padrões de tamanho usados na loja</h6>
                        <div class="mb-3">
                            @if (!empty($sizeCatalog['vestuario']))
                                <span class="size-guide-badge">Vestuário: {{ implode(', ', array_slice($sizeCatalog['vestuario'], 0, 12)) }}{{ count($sizeCatalog['vestuario']) > 12 ? '...' : '' }}</span>
                            @endif
                            @if (!empty($sizeCatalog['calcados']))
                                <span class="size-guide-badge">Calçados: {{ implode(', ', array_slice($sizeCatalog['calcados'], 0, 12)) }}{{ count($sizeCatalog['calcados']) > 12 ? '...' : '' }}</span>
                            @endif
                            @if (!empty($sizeCatalog['infantil']))
                                <span class="size-guide-badge">Infantil: {{ implode(', ', array_slice($sizeCatalog['infantil'], 0, 12)) }}{{ count($sizeCatalog['infantil']) > 12 ? '...' : '' }}</span>
                            @endif
                            @if (!empty($sizeCatalog['acessorios']))
                                <span class="size-guide-badge">Acessórios: {{ implode(', ', $sizeCatalog['acessorios']) }}</span>
                            @endif
                        </div>
                        <p class="size-guide-note mb-2"><strong>Importante:</strong> as medidas acima são estimativas. Peças slim, oversized ou tecidos com elastano podem vestir de forma diferente.</p>
                        <p class="size-guide-note mb-0">Em caso de dúvida entre dois tamanhos, escolha com base na medida que ficar mais confortável para o seu uso.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($hasHiddenSizes)
    <div class="modal fade" id="allSizesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-0">
                <div class="modal-header">
                    <h5 class="modal-title text-uppercase" style="font-size:.9rem; letter-spacing:.8px;">Todos os tamanhos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($sizeOptions as $sib)
                            @php
                                $isCurrent = (int) $product->id === (int) $sib->id;
                                $sizeLabel = $sib->size ?: __('messages.sem_tamanho_definido');
                                $isOut = ($sib->stock ?? 0) <= 0;
                            @endphp
                            @if ($isOut)
                                <span class="size-box {{ $isCurrent ? 'active' : '' }} disabled">{{ $sizeLabel }}</span>
                            @else
                                <a href="{{ route('produto.show', $sib->slug ?? $sib->id) }}"
                                   class="size-box {{ $isCurrent ? 'active' : '' }}">
                                    {{ $sizeLabel }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

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
