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
        return $closest ? ($closest . ' ' . __('messages.aprox_suffix')) : $normalized;
    };

    $sizeOptions = isset($siblings) && $siblings->count() > 0
        ? $siblings->values()
        : collect([$product]);
    $visibleSizeOptions = $sizeOptions->take(4);
    $hasHiddenSizes = $sizeOptions->count() > 4;

    $taxonomy = collect([
        $product->category->name ?? null,
        $product->category->slug ?? null,
        $product->subcategory->name ?? null,
        $product->subcategory->slug ?? null,
        $product->categoriasfilhas->name ?? null,
        $product->categoriasfilhas->slug ?? null,
    ])->filter()->map(fn ($value) => \Str::slug($value))->implode(' ');

    $taxonomyContains = fn (array $terms) => collect($terms)
        ->contains(fn ($term) => str_contains($taxonomy, $term));

    $isBeverage = $taxonomyContains(['bebida', 'vinho', 'whisky', 'vodka', 'gin', 'licor', 'cerveja', 'champagne', 'espumante', 'cognac', 'tequila']);
    $isFragrance = $taxonomyContains(['perfume', 'perfumaria', 'fragancia', 'cosmetico', 'beleza']);
    $isWearable = $taxonomyContains(['feminino', 'masculino', 'infantil', 'roupa', 'vestuario', 'camisa', 'camiseta', 'blusa', 'calca', 'vestido', 'jaqueta', 'casaco', 'bermuda', 'short', 'calcado', 'sapato', 'tenis', 'sandalia']);
    $hasDefinedSize = $sizeOptions->contains(fn ($option) => filled(trim((string) $option->size)));

    if (!$hasDefinedSize) {
        $sizeOptions = collect([$product]);
        $visibleSizeOptions = $sizeOptions;
        $hasHiddenSizes = false;
    }

    $sizeSectionLabel = __('messages.tamanho');
    $missingSizeLabel = __('messages.product_size_one_size');
    $missingSizeNote = __('messages.product_size_no_variation');

    if (!$hasDefinedSize && $isBeverage) {
        $sizeSectionLabel = __('messages.product_size_presentation');
        $missingSizeLabel = __('messages.product_size_unit');
        $missingSizeNote = __('messages.product_size_beverage_note');
    } elseif (!$hasDefinedSize && $isFragrance) {
        $sizeSectionLabel = __('messages.product_size_presentation');
        $missingSizeLabel = __('messages.product_size_bottle');
        $missingSizeNote = __('messages.product_size_fragrance_note');
    } elseif (!$hasDefinedSize && !$isWearable) {
        $sizeSectionLabel = __('messages.product_size_option');
        $missingSizeLabel = __('messages.product_size_single_model');
        $missingSizeNote = __('messages.product_size_no_variation');
    }

    $showSizeGuide = $isWearable && $hasDefinedSize;

    $sizeCatalog = json_decode(file_get_contents(public_path('data/tamanho.json')), true) ?? [];

    $currentLocale = app()->getLocale();
    $translation   = $product->translations->where('locale', $currentLocale)->first();
    $displayName   = $translation && !empty($translation->name)    ? $translation->name    : $product->name;
    $displayDesc   = $translation && !empty($translation->details) ? $translation->details : $product->description;
@endphp

<div class="product-page-wrapper">
    <div class="container-fluid product-page-container px-3 px-lg-5 py-3 py-lg-4">

        <nav aria-label="breadcrumb" class="product-breadcrumb mb-3 mb-lg-4">
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

        <div class="row g-4 g-xl-5 align-items-start product-hero">

            <div class="col-lg-7 col-xl-7">
                <div class="product-gallery-shell">
                <div class="swiper productMainSwiper">
                    <div class="swiper-wrapper">
                        @foreach ($gallery as $img)
                            <div class="swiper-slide">
                                <div class="swiper-zoom-container">
                                    <img src="{{ Storage::url($img) }}" alt="{{ $displayName }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" data-product-image>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="product-gallery-counter"><span data-gallery-current>1</span> / {{ count($gallery) }}</div>
                    <button type="button" class="product-gallery-expand" data-open-product-zoom aria-label="Ampliar imagem">
                        <i class="fa-solid fa-expand"></i><span>Ampliar</span>
                    </button>
                </div>

                @php $thumbs = array_slice($gallery, 0, 6); @endphp
                @if (count($thumbs) > 1)
                    <div class="product-thumbnails" role="tablist" aria-label="Imagens do produto">
                        @foreach ($thumbs as $i => $img)
                                <button type="button" class="thumb-item {{ $i === 0 ? 'active' : '' }}" data-gallery-index="{{ $i }}" aria-label="{{ __('messages.thumb_prefix') }} {{ $i + 1 }}">
                                    <img src="{{ Storage::url($img) }}" alt="{{ __('messages.thumb_prefix') }} {{ $i + 1 }}">
                                </button>
                        @endforeach
                    </div>
                @endif
                <p class="product-gallery-hint"><i class="fa-solid fa-magnifying-glass-plus"></i> Clique ou toque na imagem para ampliar</p>
                </div>
            </div>

            <div class="col-lg-5 col-xl-5">
                <div class="product-sticky-info">

                    <div class="product-meta-row d-flex justify-content-between align-items-start mb-3">
                        <a href="{{ $product->brand ? route('brands.show', $product->brand->slug) : '#' }}" class="text-decoration-none">
                            <span class="product-brand-name">
                                {{ $product->brand->name ?? __('messages.luxury_selection') }}
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
                                    <i class="fa fa-edit me-1"></i>{{ __('messages.editar_no_admin') }}
                                </a>
                            </div>
                        @endif
                    @endauth

                    @php
                        $categoryBadgeMap = ['infantil' => 'cat-badge--infantil'];
                        $catKey = \Str::slug($product->category->name ?? '');
                        $catBadgeModifier = $categoryBadgeMap[$catKey] ?? '';
                    @endphp
                    <div class="product-heading d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h1 class="product-display-title mb-0">{{ $displayName }}</h1>
                        @if ($product->category)
                            <span class="cat-badge {{ $catBadgeModifier }}">{{ \Str::upper($product->category->name) }}</span>
                        @endif
                    </div>
                    <p class="product-reference mb-3">{{ __('messages.ref_prefix') }} {{ $product->sku }}</p>

                    <div class="product-price-block mb-4">
                        @if ($product->previous_price > $product->price)
                            <span class="text-muted text-decoration-line-through me-2 small fw-light">{{ currency_format($product->previous_price) }}</span>
                        @endif
                        <span class="product-current-price">{{ currency_format($product->price) }}</span>

                        <x-cupon-selo :product="$product" variante="produto" />
                    </div>

                    <div class="product-option-group mb-4">
                        <p class="extra-small fw-bold text-uppercase mb-2">
                            {{ __('messages.cor') }}:
                            <span class="fw-normal text-muted">{{ count($product->product_colors) ? collect($product->product_colors)->map($getColorName)->filter()->implode(' + ') : __('messages.sem_cor_definida') }}</span>
                        </p>
                        <div class="d-flex flex-wrap size-selector">
                            @if (isset($colorSiblings) && $colorSiblings->count() > 0)
                                @foreach ($colorSiblings as $sib)
                                    <a href="{{ route('produto.show', $sib->slug ?? $sib->id) }}"
                                       class="color-box-link {{ $product->id == $sib->id ? 'active' : '' }}"
                                       title="{{ count($sib->product_colors) ? collect($sib->product_colors)->map($getColorName)->filter()->implode(' + ') : __('messages.sem_cor_definida') }}">
                                        <div class="color-dot {{ empty($sib->color) ? 'bg-secondary' : '' }}"
                                             style="{{ $sib->color_swatch_style }}"></div>
                                    </a>
                                @endforeach
                            @else
                                <div class="color-box-link active">
                                    <div class="color-dot {{ empty($product->color) ? 'bg-secondary' : '' }}"
                                         style="{{ $product->color_swatch_style }}"></div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="product-option-group mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <p class="extra-small fw-bold text-uppercase mb-0">{{ $sizeSectionLabel }}</p>
                            @if($showSizeGuide)
                                <a href="#" class="extra-small text-muted text-decoration-underline"
                                   data-bs-toggle="modal" data-bs-target="#sizeGuideModal">
                                    {{ __('messages.guia_de_medidas') }}
                                </a>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if ($sizeOptions->isNotEmpty())
                                @foreach ($visibleSizeOptions as $sib)
                                    @php
                                        $isCurrent = (int) $product->id === (int) $sib->id;
                                        $sizeLabel = $sib->size ?: $missingSizeLabel;
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
                                        {{ __('messages.ver_todos') }}
                                    </button>
                                @endif
                            @else
                                <span class="size-box active">{{ $missingSizeLabel }}</span>
                            @endif
                        </div>
                        @if(!$hasDefinedSize)
                            <p class="product-size-context mb-0">{{ $missingSizeNote }}</p>
                        @endif
                    </div>

                    <div class="product-purchase mb-4 mb-lg-5">
                        @if ($isBridal)
                                     <a href="https://wa.me/595984167575?text={{ urlencode(__('messages.whatsapp_schedule_product_prefix') . $displayName) }}"
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
                                @if (Auth::user()->user_type != 1)
                                    <div class="wishlist-btn">
                                        <x-product-favorite-button :item="$product" />
                                    </div>
                                @endif
                            </div>
                        @else
                            <a href="{{ route('login') }}"
                               class="btn btn-dark w-100 text-uppercase fw-bold rounded-0 js-requires-login login-buy-btn"
                               data-redirect-to="{{ url()->current() }}">
                                {{ __('messages.login_para_comprar') }}
                            </a>
                        @endif
                    </div>

                    <div class="product-information">
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
                                    <div class="accordion-content pb-3">
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

<div class="product-zoom-viewer" data-product-zoom-viewer role="dialog" aria-modal="true" aria-label="Imagem ampliada de {{ $displayName }}">
    <div class="product-zoom-stage" data-product-zoom-stage>
        <img class="product-zoom-image" data-product-zoom-image src="" alt="{{ $displayName }}">
        <div class="product-zoom-toolbar">
            <button type="button" data-zoom-out aria-label="Diminuir zoom"><i class="fa-solid fa-minus"></i></button>
            <button type="button" data-zoom-reset aria-label="Restaurar zoom"><i class="fa-solid fa-rotate-left"></i></button>
            <button type="button" data-zoom-in aria-label="Aumentar zoom"><i class="fa-solid fa-plus"></i></button>
            <button type="button" data-close-product-zoom aria-label="Fechar imagem ampliada"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <span class="product-zoom-caption">Use a roda do mouse ou os botões para aproximar</span>
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
                    <h5 class="modal-title text-uppercase mb-1" style="font-size:.95rem; letter-spacing:.9px;">{{ __('messages.guia_de_medidas') }}</h5>
                    <p class="mb-0 size-guide-note">{{ __('messages.size_guide_reference_note') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.fechar') }}"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="row g-4">
                    <div class="col-12">
                        <h6 class="text-uppercase fw-bold mb-2" style="font-size:.78rem; letter-spacing:.8px;">{{ __('messages.como_medir_em_casa') }}</h6>
                        <p class="size-guide-note mb-2"><strong>{{ __('messages.peito_busto_label') }}</strong> {{ __('messages.peito_busto_instruction') }}</p>
                        <p class="size-guide-note mb-2"><strong>{{ __('messages.cintura_label') }}</strong> {{ __('messages.cintura_instruction') }}</p>
                        <p class="size-guide-note mb-2"><strong>{{ __('messages.quadril_label') }}</strong> {{ __('messages.quadril_instruction') }}</p>
                        <p class="size-guide-note mb-2"><strong>{{ __('messages.comprimento_braco_label') }}</strong> {{ __('messages.comprimento_braco_instruction') }}</p>
                        <p class="size-guide-note mb-0"><strong>{{ __('messages.calcado_label') }}</strong> {{ __('messages.calcado_instruction') }}</p>
                    </div>

                    <div class="col-12 col-lg-6">
                        <h6 class="text-uppercase fw-bold mb-2" style="font-size:.78rem; letter-spacing:.8px;">{{ __('messages.feminino_aprox') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle size-guide-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.tam_abrev') }}</th>
                                        <th>{{ __('messages.brasil') }}</th>
                                        <th>{{ __('messages.busto_cm') }}</th>
                                        <th>{{ __('messages.cintura_cm') }}</th>
                                        <th>{{ __('messages.quadril_cm') }}</th>
                                        <th>{{ __('messages.braco_cm') }}</th>
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
                        <h6 class="text-uppercase fw-bold mb-2" style="font-size:.78rem; letter-spacing:.8px;">{{ __('messages.masculino_aprox') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle size-guide-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.tam_abrev') }}</th>
                                        <th>{{ __('messages.brasil') }}</th>
                                        <th>{{ __('messages.peito_cm') }}</th>
                                        <th>{{ __('messages.cintura_cm') }}</th>
                                        <th>{{ __('messages.calca') }}</th>
                                        <th>{{ __('messages.braco_cm') }}</th>
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
                        <h6 class="text-uppercase fw-bold mb-2" style="font-size:.78rem; letter-spacing:.8px;">{{ __('messages.infantil_aprox') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle size-guide-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('messages.tam_abrev') }}</th>
                                        <th>{{ __('messages.idade') }}</th>
                                        <th>{{ __('messages.altura_cm') }}</th>
                                        <th>{{ __('messages.peito_cm') }}</th>
                                        <th>{{ __('messages.cintura_cm') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>RN</td><td>{{ __('messages.recem_nascido') }}</td><td>45-52</td><td>38-42</td><td>38-42</td></tr>
                                    <tr><td>3M-6M</td><td>{{ __('messages.meses_3_a_6') }}</td><td>58-68</td><td>42-46</td><td>42-46</td></tr>
                                    <tr><td>9M-12M</td><td>{{ __('messages.meses_9_a_12') }}</td><td>68-76</td><td>46-50</td><td>45-49</td></tr>
                                    <tr><td>18M-24M</td><td>{{ __('messages.meses_18_a_24') }}</td><td>76-90</td><td>50-54</td><td>48-52</td></tr>
                                    <tr><td>2Y-4Y</td><td>{{ __('messages.anos_2_a_4') }}</td><td>90-104</td><td>54-58</td><td>52-55</td></tr>
                                    <tr><td>5Y-8Y</td><td>{{ __('messages.anos_5_a_8') }}</td><td>104-128</td><td>58-66</td><td>55-61</td></tr>
                                    <tr><td>10Y-14Y</td><td>{{ __('messages.anos_10_a_14') }}</td><td>134-164</td><td>68-82</td><td>62-70</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <h6 class="text-uppercase fw-bold mb-2" style="font-size:.78rem; letter-spacing:.8px;">{{ __('messages.calcados_aprox') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle size-guide-table">
                                <thead>
                                    <tr>
                                        <th>BR</th>
                                        <th>{{ __('messages.comprimento_pe_cm') }}</th>
                                        <th>{{ __('messages.us_ref') }}</th>
                                        <th>{{ __('messages.eu_ref') }}</th>
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
                        <h6 class="text-uppercase fw-bold mb-2" style="font-size:.78rem; letter-spacing:.8px;">{{ __('messages.padroes_tamanho_usados_loja') }}</h6>
                        <div class="mb-3">
                            @if (!empty($sizeCatalog['vestuario']))
                                <span class="size-guide-badge">{{ __('messages.vestuario_label') }} {{ implode(', ', array_slice($sizeCatalog['vestuario'], 0, 12)) }}{{ count($sizeCatalog['vestuario']) > 12 ? '...' : '' }}</span>
                            @endif
                            @if (!empty($sizeCatalog['calcados']))
                                <span class="size-guide-badge">{{ __('messages.calcados_label') }} {{ implode(', ', array_slice($sizeCatalog['calcados'], 0, 12)) }}{{ count($sizeCatalog['calcados']) > 12 ? '...' : '' }}</span>
                            @endif
                            @if (!empty($sizeCatalog['infantil']))
                                <span class="size-guide-badge">{{ __('messages.infantil_label') }} {{ implode(', ', array_slice($sizeCatalog['infantil'], 0, 12)) }}{{ count($sizeCatalog['infantil']) > 12 ? '...' : '' }}</span>
                            @endif
                            @if (!empty($sizeCatalog['acessorios']))
                                <span class="size-guide-badge">{{ __('messages.acessorios_label') }} {{ implode(', ', $sizeCatalog['acessorios']) }}</span>
                            @endif
                        </div>
                        <p class="size-guide-note mb-2"><strong>{{ __('messages.importante_label') }}</strong> {{ __('messages.size_guide_estimates_note') }}</p>
                        <p class="size-guide-note mb-0">{{ __('messages.size_guide_doubt_note') }}</p>
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
                    <h5 class="modal-title text-uppercase" style="font-size:.9rem; letter-spacing:.8px;">{{ __('messages.todos_os_tamanhos') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.fechar') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($sizeOptions as $sib)
                            @php
                                $isCurrent = (int) $product->id === (int) $sib->id;
                                $sizeLabel = $sib->size ?: $missingSizeLabel;
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
    const galleryImages = Array.from(document.querySelectorAll('[data-product-image]'));
    const thumbnails = Array.from(document.querySelectorAll('[data-gallery-index]'));
    const galleryCurrent = document.querySelector('[data-gallery-current]');
    const viewer = document.querySelector('[data-product-zoom-viewer]');
    const zoomStage = document.querySelector('[data-product-zoom-stage]');
    const zoomImage = document.querySelector('[data-product-zoom-image]');
    let zoomScale = 1;
    let zoomX = 0;
    let zoomY = 0;
    let dragging = false;
    let dragOrigin = null;
    const pointers = new Map();
    let pinchDistance = null;

    function renderZoom() {
        if (!zoomImage) return;
        zoomImage.style.transform = `translate3d(${zoomX}px, ${zoomY}px, 0) scale(${zoomScale})`;
        zoomImage.style.cursor = zoomScale > 1 ? (dragging ? 'grabbing' : 'grab') : 'zoom-in';
    }

    function setZoom(nextScale) {
        zoomScale = Math.min(4, Math.max(1, nextScale));
        if (zoomScale === 1) zoomX = zoomY = 0;
        renderZoom();
    }

    function resetZoom() {
        zoomScale = 1;
        zoomX = zoomY = 0;
        pinchDistance = null;
        renderZoom();
    }

    function activeImageSource() {
        const activeSlideImage = document.querySelector('.productMainSwiper .swiper-slide-active [data-product-image]');
        return activeSlideImage?.currentSrc || activeSlideImage?.src || galleryImages[window.mySwiper?.realIndex || 0]?.src;
    }

    function openViewer() {
        if (!viewer || !zoomImage) return;
        zoomImage.src = activeImageSource();
        resetZoom();
        viewer.classList.add('is-open');
        document.body.classList.add('product-zoom-open');
        viewer.querySelector('[data-close-product-zoom]')?.focus();
    }

    function closeViewer() {
        if (!viewer) return;
        viewer.classList.remove('is-open');
        document.body.classList.remove('product-zoom-open');
        pointers.clear();
        resetZoom();
    }

    window.mySwiper = new Swiper('.productMainSwiper', {
        loop: galleryImages.length > 1,
        zoom: { maxRatio: 3, toggle: false },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        keyboard: { enabled: true },
        on: {
            realIndexChange(swiper) {
                const index = swiper.realIndex || 0;
                if (galleryCurrent) galleryCurrent.textContent = index + 1;
                thumbnails.forEach((thumb, thumbIndex) => thumb.classList.toggle('active', thumbIndex === index));
            }
        }
    });

    thumbnails.forEach((thumb, index) => thumb.addEventListener('click', function () {
        window.mySwiper?.slideToLoop(index);
    }));
    galleryImages.forEach(image => image.addEventListener('click', openViewer));
    document.querySelector('[data-open-product-zoom]')?.addEventListener('click', openViewer);
    document.querySelector('[data-close-product-zoom]')?.addEventListener('click', closeViewer);
    document.querySelector('[data-zoom-in]')?.addEventListener('click', () => setZoom(zoomScale + .5));
    document.querySelector('[data-zoom-out]')?.addEventListener('click', () => setZoom(zoomScale - .5));
    document.querySelector('[data-zoom-reset]')?.addEventListener('click', resetZoom);

    zoomStage?.addEventListener('wheel', function (event) {
        event.preventDefault();
        setZoom(zoomScale + (event.deltaY < 0 ? .25 : -.25));
    }, { passive: false });
    zoomStage?.addEventListener('dblclick', () => setZoom(zoomScale > 1 ? 1 : 2.5));
    zoomStage?.addEventListener('pointerdown', function (event) {
        pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
        zoomStage.setPointerCapture(event.pointerId);
        dragging = zoomScale > 1 && pointers.size === 1;
        dragOrigin = { x: event.clientX - zoomX, y: event.clientY - zoomY };
        renderZoom();
    });
    zoomStage?.addEventListener('pointermove', function (event) {
        if (!pointers.has(event.pointerId)) return;
        pointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
        if (pointers.size === 2) {
            const [a, b] = Array.from(pointers.values());
            const distance = Math.hypot(a.x - b.x, a.y - b.y);
            if (pinchDistance) setZoom(zoomScale * (distance / pinchDistance));
            pinchDistance = distance;
        } else if (dragging && dragOrigin) {
            zoomX = event.clientX - dragOrigin.x;
            zoomY = event.clientY - dragOrigin.y;
            renderZoom();
        }
    });
    const releasePointer = function (event) {
        pointers.delete(event.pointerId);
        dragging = false;
        dragOrigin = null;
        if (pointers.size < 2) pinchDistance = null;
        renderZoom();
    };
    zoomStage?.addEventListener('pointerup', releasePointer);
    zoomStage?.addEventListener('pointercancel', releasePointer);
    viewer?.addEventListener('click', event => {
        if (event.target === zoomStage && zoomScale === 1) closeViewer();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && viewer?.classList.contains('is-open')) closeViewer();
    });
});
</script>

@endsection
