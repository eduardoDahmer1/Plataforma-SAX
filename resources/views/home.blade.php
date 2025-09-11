@extends('layout.layout')

@section('content')
    <div class="container py-4">

        <h2 class="mb-4"><i class="fas fa-home me-2"></i> Bem-vindo à Página Inicial</h2>
        <p class="text-muted">Confira os produtos mais recentes em nosso catálogo.</p>

        {{-- Alertas de sucesso --}}
        @if (session('success'))
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

        @foreach ($highlightTitles as $key => $title)
            @php
                $products = $highlights[$key] ?? collect();
            @endphp

            @if ($products->isNotEmpty())
                <h4 class="mt-5 mb-3"><i class="fas fa-star me-2"></i> {{ $title }}</h4>

                @if (in_array($key, ['destaque', 'melhores_avaliacoes', 'lancamentos', 'tendencias']))
                    <!-- Slider de Produtos com Swiper -->
                    <div class="swiper mySwiper mb-4">
                        <div class="swiper-wrapper">
                            @foreach ($products as $item)
                                <div class="swiper-slide">
                                    <div class="card h-100 shadow-sm border-0 position-relative">
                                        {{-- Imagem --}}
                                        <img src="{{ $item->photo_url }}" class="card-img-top"
                                            alt="{{ $item->external_name }}"
                                            style="max-height:150px; object-fit:scale-down;">

                                        {{-- Botão de favorito (aparece no hover) --}}
                                        @auth
                                        @php
                                            // Pega a quantidade do produto específico no carrinho
                                            $currentQty = $cartItems[$item->id] ?? 0;
                                        @endphp
                                    
                                        {{-- Botão de favorito --}}
                                        <form action="{{ route('user.preferences.toggle') }}" method="POST" class="card-favorite-form d-none">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item->id }}">
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                        </form>
                                    
                                        {{-- Botão de adicionar ao carrinho --}}
                                        <form action="{{ route('cart.add') }}" method="POST" class="card-add-form d-none">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item->id }}">
                                            <button type="submit" class="btn btn-success" {{ $currentQty >= $item->stock ? 'disabled' : '' }}>
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </form>
                                    @endauth                                    

                                        <div class="card-body p-2 d-flex flex-column">
                                            <h6 class="card-title mb-2">
                                                <a href="{{ route('produto.show', $item->id) }}"
                                                    class="text-decoration-none">{{ $item->external_name }}</a>
                                            </h6>
                                            <p class="small text-muted mb-2">
                                                {{ $item->brand->name ?? 'Sem marca' }}<br>
                                                SKU: {{ $item->sku ?? 'N/A' }}<br>
                                                {{ isset($item->price) ? currency_format((float) $item->price) : 'Não informado' }}
                                            </p>

                                            {{-- Estoque --}}
                                            @if ($item->stock > 0)
                                                <span class="badge bg-success"><i
                                                        class="fas fa-box me-1"></i>
                                                    {{ $item->stock }} em estoque</span>
                                            @else
                                                <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Sem
                                                    estoque</span>
                                            @endif

                                            <div class="mt-auto d-flex flex-column">
                                                <a href="{{ route('produto.show', $item->id) }}"
                                                    class="btn btn-sm btn-info mt-2 mb-2">
                                                    <i class="fas fa-eye me-1"></i> Ver Detalhes
                                                </a>
                                                @auth
                                                    @php $currentQty = $cartItems[$item->id] ?? 0; @endphp
                                                    @if (in_array(auth()->user()->user_type, [0, 1, 2]))
                                                        <form action="{{ route('checkout.index') }}" method="GET"
                                                            class="d-flex">
                                                            <input type="hidden" name="product_id"
                                                                value="{{ $item->id }}">
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

                        <!-- Setas -->
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                @else
                    {{-- Grid 4x4 --}}
                    <div class="row mb-4">
                        @foreach ($products as $item)
                            <div class="col-6 col-md-3 mb-4">
                                <div class="card h-100 shadow-sm border-0 position-relative">
                                    {{-- Imagem do produto --}}
                                    <img src="{{ $item->photo_url }}" class="card-img-top"
                                        alt="{{ $item->external_name }}" style="max-height:150px; object-fit:scale-down;">

                                    {{-- Botão de favorito (aparece no hover) --}}
                                    @auth
                                    @php
                                        // Pega a quantidade do produto específico no carrinho
                                        $currentQty = $cartItems[$item->id] ?? 0;
                                    @endphp
                                
                                    {{-- Botão de favorito --}}
                                    <form action="{{ route('user.preferences.toggle') }}" method="POST" class="card-favorite-form d-none">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item->id }}">
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    </form>
                                
                                    {{-- Botão de adicionar ao carrinho --}}
                                    <form action="{{ route('cart.add') }}" method="POST" class="card-add-form d-none">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $item->id }}">
                                        <button type="submit" class="btn btn-success" {{ $currentQty >= $item->stock ? 'disabled' : '' }}>
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </form>
                                @endauth
                                
                                    <div class="card-body p-2 d-flex flex-column">
                                        <h6 class="card-title mb-2">
                                            <a href="{{ route('produto.show', $item->id) }}"
                                                class="text-decoration-none">{{ $item->external_name }}</a>
                                        </h6>
                                        <p class="small text-muted mb-2">
                                            {{ $item->brand->name ?? 'Sem marca' }}<br>
                                            SKU: {{ $item->sku ?? 'N/A' }}<br>
                                            {{ isset($item->price) ? currency_format((float) $item->price) : 'Não informado' }}
                                        </p>

                                        {{-- Estoque --}}
                                        @if ($item->stock > 0)
                                            <span class="badge bg-success"><i
                                                    class="fas fa-box me-1"></i>
                                                {{ $item->stock }} em estoque</span>
                                        @else
                                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Sem
                                                estoque</span>
                                        @endif

                                        <div class="mt-auto d-flex flex-column">
                                            <a href="{{ route('produto.show', $item->id) }}"
                                                class="btn btn-sm btn-info mt-2 mb-2">
                                                <i class="fas fa-eye me-1"></i> Ver Detalhes
                                            </a>

                                            @auth
                                                @php $currentQty = $cartItems[$item->id] ?? 0; @endphp
                                                @if (in_array(auth()->user()->user_type, [0, 1, 2]))
                                                    <form action="{{ route('checkout.index') }}" method="GET"
                                                        class="d-flex">
                                                        <input type="hidden" name="product_id" value="{{ $item->id }}">
                                                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                                            <i class="fas fa-bolt me-1"></i> Comprar Agora
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <a href="#" class="btn btn-sm btn-warning mt-2" data-bs-toggle="modal"
                                                    data-bs-target="#loginModal">
                                                    <i class="fas fa-sign-in-alt me-1"></i> Login para Favoritar
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
                @if (isset($banners[$loop->index]) && $banners[$loop->index])
                    <div class="my-4 text-center">
                        <img src="{{ asset('storage/uploads/' . $banners[$loop->index]) }}"
                            alt="Banner {{ $loop->index + 1 }}" class="img-fluid rounded banner-img">
                    </div>
                @endif
            @endif
        @endforeach
        {{-- Sessão de Blogs --}}
        @if (isset($blogs) && $blogs->isNotEmpty())
            <h4 class="mt-5 mb-3"><i class="fas fa-blog me-2"></i> Últimos Artigos do Blog</h4>

            <div class="swiper blogSwiper mb-4">
                <div class="swiper-wrapper">
                    @foreach ($blogs as $blog)
                        <div class="swiper-slide">
                            <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden">

                                {{-- Imagem --}}
                                <div class="ratio ratio-16x9 bg-light">
                                    @if ($blog->image && Storage::disk('public')->exists($blog->image))
                                        <img src="{{ Storage::url($blog->image) }}" alt="{{ $blog->title }}"
                                            class="img-fluid object-fit-coverr">
                                    @else
                                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão"
                                            class="img-fluid object-fit-coverr">
                                    @endif
                                </div>

                                {{-- Conteúdo --}}
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">{{ $blog->title }}</h6>
                                    <p class="card-text text-muted flex-grow-1">
                                        {{ Str::limit($blog->subtitle, 80) }}
                                    </p>

                                    <div>
                                        <small
                                            class="badge bg-secondary">{{ $blog->category->name ?? 'Sem categoria' }}</small>
                                    </div>

                                    <a href="{{ route('blogs.show', $blog->slug) }}"
                                        class="btn btn-sm btn-primary mt-3 w-100">
                                        <i class="fas fa-book-open me-1"></i> Leia mais
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        @endif
    </div>
@endsection
