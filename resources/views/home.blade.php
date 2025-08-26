@extends('layout.layout')

@section('content')
<div class="container py-4">

    <h2 class="mb-4"><i class="fas fa-home me-2"></i> Bem-vindo à Página Inicial</h2>
    <p class="text-muted">Confira os produtos mais recentes em nosso catálogo.</p>

    {{-- Alertas de sucesso --}}
    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
    </div>
    @endif

    @php
        $highlightTitles = [
            'destaque' => 'Exibir em Destaques',
            'mais_vendidos' => 'Exibir em Mais Vendidos',
            'melhores_avaliacoes' => 'Exibir em Melhores Avaliações',
            'super_desconto' => 'Exibir em Super Desconto',
            'famosos' => 'Exibir em Famosos',
            'lancamentos' => 'Exibir em Lançamentos',
            'tendencias' => 'Exibir em Tendências',
            'promocoes' => 'Exibir em Promoções',
            'ofertas_relampago' => 'Exibir em Ofertas Relâmpago',
            'navbar' => 'Exibir em Navbar',
        ];

        // Array dos banners
        $banners = [
            $banner1 ?? null,
            $banner2 ?? null,
            $banner3 ?? null,
            $banner4 ?? null,
            $banner5 ?? null,
            $banner6 ?? null,
            $banner7 ?? null,
            $banner8 ?? null,
            $banner9 ?? null,
            $banner10 ?? null,
        ];
    @endphp

    @foreach($highlightTitles as $key => $title)
        @php
            $products = $highlights[$key] ?? collect();
        @endphp

        @if($products->isNotEmpty())
            <h4 class="mt-5 mb-3"><i class="fas fa-star me-2"></i> {{ $title }}</h4>

            @if(in_array($key, ['destaque','melhores_avaliacoes','lancamentos','tendencias']))
                {{-- Slider Bootstrap com swipe --}}
                <div id="slider-{{ $key }}" class="carousel slide mb-4" data-bs-ride="carousel" data-bs-touch="true">
                    <div class="carousel-inner">
                        @foreach($products->chunk(4) as $chunkIndex => $chunk)
                            <div class="carousel-item {{ $chunkIndex === 0 ? 'active' : '' }}">
                                <div class="row">
                                    @foreach($chunk as $item)
                                        <div class="col-6 col-md-3">
                                            <div class="card h-100 shadow-sm border-0">
                                                <img src="{{ $item->photo_url }}" class="card-img-top"
                                                    alt="{{ $item->external_name }}" style="max-height:150px; object-fit:cover;">
                                                <div class="card-body p-2 d-flex flex-column">
                                                    <h6 class="card-title mb-2">
                                                        <a href="{{ route('produto.show',$item->id) }}" class="text-decoration-none">
                                                            {{ $item->external_name }}
                                                        </a>
                                                    </h6>
                                                    <p class="small text-muted mb-2">
                                                        {{ $item->brand->name ?? 'Sem marca' }}<br>
                                                        SKU: {{ $item->sku ?? 'N/A' }}<br>
                                                        {{ isset($item->price) ? 'R$ ' . number_format($item->price,2,',','.') : 'Não informado' }}
                                                    </p>
                                                    <div class="mt-auto d-flex flex-column">
                                                        <a href="{{ route('produto.show',$item->id) }}" class="btn btn-sm btn-info mb-2">
                                                            <i class="fas fa-eye me-1"></i> Ver Detalhes
                                                        </a>

                                                        @auth
                                                            @php $currentQty = $cartItems[$item->id] ?? 0; @endphp
                                                            @if(in_array(auth()->user()->user_type,[0,1,2]))
                                                                <form action="{{ route('cart.add') }}" method="POST" class="d-flex mb-2">
                                                                    @csrf
                                                                    <input type="hidden" name="product_id" value="{{ $item->id }}">
                                                                    <button type="submit" class="btn btn-sm btn-success flex-grow-1"
                                                                        @if($currentQty >= $item->stock) disabled @endif>
                                                                        <i class="fas fa-cart-plus me-1"></i> Adicionar
                                                                    </button>
                                                                </form>
                                                                <form action="{{ route('checkout.index') }}" method="GET" class="d-flex">
                                                                    <input type="hidden" name="product_id" value="{{ $item->id }}">
                                                                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                                                        <i class="fas fa-bolt me-1"></i> Comprar Agora
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        @else
                                                            <a href="#" class="btn btn-sm btn-warning mt-2" data-bs-toggle="modal"
                                                                data-bs-target="#loginModal">
                                                                <i class="fas fa-sign-in-alt me-1"></i> Login para Comprar
                                                            </a>
                                                        @endauth
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    {{-- Setas pretas --}}
                    <button class="carousel-control-prev" type="button" data-bs-target="#slider-{{ $key }}" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon custom-arrow"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#slider-{{ $key }}" data-bs-slide="next">
                        <span class="carousel-control-next-icon custom-arrow"></span>
                    </button>
                </div>
            @else
                {{-- Grid 4x4 --}}
                <div class="row mb-4">
                    @foreach($products as $item)
                        <div class="col-6 col-md-3 mb-4">
                            <div class="card h-100 shadow-sm border-0">
                                <img src="{{ $item->photo_url }}" class="card-img-top" alt="{{ $item->external_name }}" style="max-height:150px; object-fit:cover;">
                                <div class="card-body p-2 d-flex flex-column">
                                    <h6 class="card-title mb-2">
                                        <a href="{{ route('produto.show',$item->id) }}" class="text-decoration-none">
                                            {{ $item->external_name }}
                                        </a>
                                    </h6>
                                    <p class="small text-muted mb-2">
                                        {{ $item->brand->name ?? 'Sem marca' }}<br>
                                        SKU: {{ $item->sku ?? 'N/A' }}<br>
                                        {{ isset($item->price) ? 'R$ ' . number_format($item->price,2,',','.') : 'Não informado' }}
                                    </p>
                                    <div class="mt-auto d-flex flex-column">
                                        <a href="{{ route('produto.show',$item->id) }}" class="btn btn-sm btn-info mb-2">
                                            <i class="fas fa-eye me-1"></i> Ver Detalhes
                                        </a>

                                        @auth
                                            @php $currentQty = $cartItems[$item->id] ?? 0; @endphp
                                            @if(in_array(auth()->user()->user_type,[0,1,2]))
                                                <form action="{{ route('cart.add') }}" method="POST" class="d-flex mb-2">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $item->id }}">
                                                    <button type="submit" class="btn btn-sm btn-success flex-grow-1"
                                                        @if($currentQty >= $item->stock) disabled @endif>
                                                        <i class="fas fa-cart-plus me-1"></i> Adicionar
                                                    </button>
                                                </form>
                                                <form action="{{ route('checkout.index') }}" method="GET" class="d-flex">
                                                    <input type="hidden" name="product_id" value="{{ $item->id }}">
                                                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                                        <i class="fas fa-bolt me-1"></i> Comprar Agora
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <a href="#" class="btn btn-sm btn-warning mt-2" data-bs-toggle="modal"
                                                data-bs-target="#loginModal">
                                                <i class="fas fa-sign-in-alt me-1"></i> Login para Comprar
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Banner entre seções --}}
            @if(isset($banners[$loop->index]) && $banners[$loop->index])
                <div class="my-4 text-center">
                    <img src="{{ asset('storage/uploads/' . $banners[$loop->index]) }}" 
                         alt="Banner {{ $loop->index + 1 }}" 
                         class="img-fluid rounded banner-img">
                </div>
            @endif

        @endif
    @endforeach

</div>

{{-- CSS extra para setas pretas e banner --}}
<style>
    .custom-arrow {
        background-color: black !important;
        background-size: 50%, 50%;
        border-radius: 50%;
    }
    .banner-img {
        width: 100%;
        max-height: 300px;
        object-fit: cover;
    }
</style>

{{-- JS para swipe com mouse/touch --}}
<script>
document.querySelectorAll('.carousel').forEach(carousel => {
    let startX = 0, endX = 0;
    carousel.addEventListener('touchstart', e => startX = e.touches[0].clientX);
    carousel.addEventListener('touchend', e => {
        endX = e.changedTouches[0].clientX;
        if (startX - endX > 50) bootstrap.Carousel.getInstance(carousel).next();
        if (endX - startX > 50) bootstrap.Carousel.getInstance(carousel).prev();
    });
});
</script>
@endsection
