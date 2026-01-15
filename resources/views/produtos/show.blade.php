@extends('layout.layout')

@section('content')
<div class="product-page-wrapper bg-white">
    <div class="container-fluid p-0">
        <div class="row g-0">
            
            {{-- LADO ESQUERDO: Galeria Vertical --}}
            <div class="col-lg-7 col-xl-8 bg-light">
                @php
                    $mainImage = $product->photo ? Storage::url($product->photo) : asset('storage/uploads/noimage.webp');
                    $gallery = is_string($product->gallery) ? json_decode($product->gallery, true) : ($product->gallery ?: []);
                @endphp

                <div class="product-gallery-vertical">
                    <div class="gallery-item mb-2 shadow-sm">
                        <img src="{{ $mainImage }}" class="img-fluid w-100 main-img-zoom" alt="{{ $product->external_name }}">
                    </div>

                    @foreach ($gallery as $img)
                        <div class="gallery-item mb-2">
                            <img src="{{ Storage::url($img) }}" class="img-fluid w-100" alt="Vista detalhada">
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- LADO DIREITO: Informações Fixas --}}
            <div class="col-lg-5 col-xl-4">
                <div class="product-info-sidebar p-4 p-md-5 sticky-top">
                    
                    <div class="mb-4">
                        <nav aria-label="breadcrumb" class="mb-2">
                            <ol class="breadcrumb x-small text-uppercase p-0 bg-transparent">
                                <li class="breadcrumb-item"><a href="/" class="text-muted">Home</a></li>
                                @if($product->category) <li class="breadcrumb-item active">{{ $product->category->name }}</li> @endif
                                @if($product->subcategory) <li class="breadcrumb-item active text-muted">{{ $product->subcategory->name }}</li> @endif
                            </ol>
                        </nav>
                        <h4 class="text-uppercase fw-light letter-spacing-2 mb-1 text-muted">{{ $product->brand->name ?? 'Luxury' }}</h4>
                        <h1 class="h2 fw-bold text-uppercase">{{ $product->external_name }}</h1>
                        <div class="d-flex gap-3 x-small text-muted mt-2">
                            <span>REF: {{ $product->ref_code ?? $product->sku }}</span>
                            <span>SKU: {{ $product->sku }}</span>
                        </div>
                    </div>

                    {{-- Preço e Badge de Desconto --}}
                    <div class="mb-4 border-top border-bottom py-3">
                        @if($product->previous_price > $product->price)
                            <span class="text-decoration-line-through text-muted small d-block">De: {{ currency_format($product->previous_price) }}</span>
                        @endif
                        <div class="d-flex align-items-center gap-3">
                            <span class="h3 fw-bold m-0">Por: {{ currency_format($product->price) }}</span>
                            @if($product->previous_price > $product->price)
                                <span class="badge bg-danger rounded-0">-{{ round((1 - ($product->price / $product->previous_price)) * 100) }}%</span>
                            @endif
                        </div>
                        <p class="x-small text-success mt-2 fw-bold"><i class="fas fa-truck me-1"></i> Disponível para envio imediato</p>
                    </div>

                    {{-- Seletor de Cores --}}
                    <div class="mb-4">
                        <label class="x-small fw-bold text-uppercase mb-3 d-block">Cor Selecionada</label>
                        <div class="d-flex flex-wrap gap-3">
                            {{-- Cor Atual --}}
                            <div class="color-swatch active" style="background-color: {{ $product->color ?? '#000' }}; shadow: inset 0 0 5px rgba(0,0,0,0.2);"></div>
                            
                            {{-- Outras cores (Cores Relacionadas) --}}
                            @isset($coresRelacionadas)
                                @foreach($coresRelacionadas as $corProd)
                                    @if($corProd->id != $product->id)
                                        <a href="{{ route('produto.show', $corProd->slug ?? $corProd->id) }}" 
                                           class="color-swatch" style="background-color: {{ $corProd->color ?? '#eee' }};"></a>
                                    @endif
                                @endforeach
                            @endisset
                        </div>
                    </div>

                    {{-- Seletor de Tamanhos --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between x-small fw-bold text-uppercase mb-3">
                            <span>Tamanho: {{ $product->size ?? 'U' }}</span>
                        </div>
                        @isset($siblings)
                        <div class="row g-2">
                            @foreach($siblings as $sib)
                                <div class="col-3">
                                    <a href="{{ route('produto.show', $sib->slug ?? $sib->id) }}" 
                                       class="size-option {{ $product->id == $sib->id ? 'active' : '' }}">
                                        {{ $sib->size ?? 'U' }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        @endisset
                    </div>

                    {{-- Botões de Compra --}}
                    <div class="d-grid gap-2 mt-4">
                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="btn btn-dark btn-lg w-100 py-3 rounded-0 text-uppercase fw-bold btn-buy" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                {{ $product->stock > 0 ? 'Adicionar à Sacola' : 'Esgotado' }}
                            </button>
                        </form>
                    </div>

                    {{-- Accordion: Detalhes Técnicos e Atributos --}}
                    <div class="accordion accordion-flush mt-5 border-top" id="productSpecs">
                        {{-- Descrição --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed text-uppercase x-small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#descContent">
                                    Descrição e Detalhes
                                </button>
                            </h2>
                            <div id="descContent" class="accordion-collapse collapse show" data-bs-parent="#productSpecs">
                                <div class="accordion-body x-small text-muted lh-base">
                                    {!! nl2br(e($product->description)) !!}
                                </div>
                            </div>
                        </div>

                        {{-- Atributos dinâmicos do Banco --}}
                        @if($product->attributes)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed text-uppercase x-small fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#attrContent">
                                    Especificações Técnicas
                                </button>
                            </h2>
                            <div id="attrContent" class="accordion-collapse collapse" data-bs-parent="#productSpecs">
                                <div class="accordion-body p-0">
                                    <table class="table table-sm table-borderless x-small m-0">
                                        @foreach(json_decode($product->attributes, true) as $key => $value)
                                            <tr class="border-bottom">
                                                <td class="fw-bold text-uppercase py-2">{{ $key }}</td>
                                                <td class="text-end text-muted py-2">{{ $value }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Tags --}}
                        @if($product->tags)
                        <div class="mt-4">
                            <label class="x-small fw-bold text-uppercase d-block mb-2">Tags</label>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach(explode(',', $product->tags) as $tag)
                                    <span class="badge border text-dark fw-light x-small">{{ trim($tag) }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilização Extra para Refinamento SAX */
.x-small { font-size: 0.7rem; letter-spacing: 0.8px; }
.letter-spacing-2 { letter-spacing: 2px; }

@media (min-width: 992px) {
    .product-info-sidebar { height: 100vh; overflow-y: auto; scrollbar-width: none; }
    .product-info-sidebar::-webkit-scrollbar { display: none; }
}

.color-swatch { width: 35px; height: 35px; border-radius: 50%; border: 1px solid #eee; transition: 0.3s; cursor: pointer; position: relative; }
.color-swatch.active::after { content: ''; position: absolute; top: -4px; left: -4px; right: -4px; bottom: -4px; border: 1px solid #000; border-radius: 50%; }

.size-option { display: block; text-align: center; padding: 12px; border: 1px solid #eee; color: #333; font-size: 0.8rem; transition: 0.3s; text-decoration: none; }
.size-option:hover { border-color: #000; }
.size-option.active { background: #000; color: #fff; border-color: #000; }

.btn-buy { letter-spacing: 2px; transition: 0.4s; }
.btn-buy:hover { background-color: #333; transform: translateY(-2px); }

.accordion-button:not(.collapsed) { background-color: transparent; color: #000; box-shadow: none; }
.accordion-button::after { background-size: 10px; }
</style>
@endsection