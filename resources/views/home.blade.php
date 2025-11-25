@extends('layout.layout')

@section('content')
    <div class="container py-4">

        <h2 class="mb-4"><i class="fas fa-home me-2"></i> Bem-vindo à Página Inicial</h2>
        <p class="text-muted">Confira os produtos mais recentes em nosso catálogo.</p>

        {{-- Alertas --}}
        <x-alert type="success" :message="session('success')" />

        @php
            $highlightTitles = [
                'destaque' => 'Destaques',
                'mais_vendidos' => 'Mais Vendidos',
                'melhores_avaliacoes' => 'Melhores Avaliações',
                'super_desconto' => 'Super Desconto',
                'famosos' => 'Famosos',
                'lancamentos' => 'Lançamentos',
                'tendencias' => 'Tendências',
                'promocoes' => 'Promoções',
                'ofertas_relampago' => 'Ofertas Relâmpago',
                'navbar' => 'Navbar',
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

            // Flags de exibição
            $settings = $settings ?? \App\Models\Generalsetting::first();
        @endphp

        @foreach ($highlightTitles as $key => $title)
            @php
                $show = $settings->{'show_highlight_' . $key} ?? 0;
                $products = $highlights[$key] ?? collect();
            @endphp

            @if ($show && $products->isNotEmpty())
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

                                        {{-- Botões --}}
                                        @auth
                                            @php $currentQty = $cartItems[$item->id] ?? 0; @endphp

                                            <form action="{{ route('user.preferences.toggle') }}" method="POST"
                                                class="card-favorite-form d-none">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $item->id }}">
                                                <button type="submit" class="btn btn-outline-danger"><i
                                                        class="fas fa-heart"></i></button>
                                            </form>

                                            <form action="{{ route('cart.add') }}" method="POST" class="card-add-form d-none">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $item->id }}">
                                                <button type="submit" class="btn btn-success"
                                                    {{ $currentQty >= $item->stock ? 'disabled' : '' }}>
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

                                            @if ($item->stock > 0)
                                                <span class="badge bg-success"><i
                                                        class="fas fa-box me-1"></i>{{ $item->stock }} em estoque</span>
                                            @else
                                                <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Sem
                                                    estoque</span>
                                            @endif

                                            <div class="mt-auto d-flex flex-column">
                                                <a href="{{ route('produto.show', $item->id) }}"
                                                    class="btn btn-sm btn-info mt-2 mb-2">
                                                    <i class="fas fa-eye me-1"></i> Ver Detalhes
                                                </a>
                                                @auth
                                                    @if (in_array(auth()->user()->user_type, [0, 1, 2]))
                                                        <form action="{{ route('cart.addAndCheckout') }}" method="POST"
                                                            class="d-flex">
                                                            @csrf
                                                            <input type="hidden" name="product_id"
                                                                value="{{ $item->id }}">
                                                            <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                                                <i class="fas fa-bolt me-1"></i> Comprar Agora
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endauth
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                @else
                    {{-- Grid 4x4 --}}
                    <div class="row mb-4">
                        @foreach ($products as $item)
                            <div class="col-6 col-md-3 mb-4">
                                <div class="card h-100 shadow-sm border-0 position-relative">
                                    <img src="{{ $item->photo_url }}" class="card-img-top"
                                        alt="{{ $item->external_name }}" style="max-height:150px; object-fit:scale-down;">
                                    @auth
                                        @php $currentQty = $cartItems[$item->id] ?? 0; @endphp
                                        <form action="{{ route('user.preferences.toggle') }}" method="POST"
                                            class="card-favorite-form d-none">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item->id }}">
                                            <button type="submit" class="btn btn-outline-danger"><i
                                                    class="fas fa-heart"></i></button>
                                        </form>
                                        <form action="{{ route('cart.add') }}" method="POST" class="card-add-form d-none">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $item->id }}">
                                            <button type="submit" class="btn btn-success"
                                                {{ $currentQty >= $item->stock ? 'disabled' : '' }}>
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
                                        @if ($item->stock > 0)
                                            <span class="badge bg-success"><i
                                                    class="fas fa-box me-1"></i>{{ $item->stock }} em estoque</span>
                                        @else
                                            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Sem
                                                estoque</span>
                                        @endif
                                        <div class="mt-auto d-flex flex-column">
                                            <a href="{{ route('produto.show', $item->id) }}"
                                                class="btn btn-sm btn-info mt-2 mb-2">
                                                <i class="fas fa-eye me-1"></i> Ver Detalhes
                                            </a>
                                            @auth
                                                @if (in_array(auth()->user()->user_type, [0, 1, 2]))
                                                    <form action="{{ route('cart.addAndCheckout') }}" method="POST"
                                                        class="d-flex">
                                                        @csrf
                                                        <input type="hidden" name="product_id" value="{{ $item->id }}">
                                                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                                            <i class="fas fa-bolt me-1"></i> Comprar Agora
                                                        </button>
                                                    </form>
                                                @endif
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Banner só se a seção estiver ativa e existir --}}
                @if ($show && isset($banners[$loop->index]) && $banners[$loop->index])
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
                            <div class="card h-100 border-0 rounded-4 shadow-sm overflow-hidden">

                                <div class="ratio ratio-16x9">
                                    @if ($blog->image && Storage::disk('public')->exists($blog->image))
                                        <img src="{{ Storage::url($blog->image) }}" alt="{{ $blog->title }}"
                                            class="img-fluid object-fit-cover">
                                    @else
                                        <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão"
                                            class="img-fluid object-fit-cover">
                                    @endif
                                </div>

                                <div class="card-body d-flex flex-column px-3 py-3">

                                    <span class="text-uppercase small fw-semibold text-primary mb-2">
                                        {{ $blog->category->name ?? 'Sem categoria' }}
                                    </span>

                                    <h5 class="card-title fw-bold mb-2" style="line-height: 1.3">
                                        {{ Str::limit($blog->title, 60) }}
                                    </h5>

                                    <p class="card-text text-muted mb-3 flex-grow-1" style="font-size: 0.9rem;">
                                        {{ Str::limit($blog->subtitle, 90) }}
                                    </p>

                                    <a href="{{ route('blogs.show', $blog->slug) }}"
                                        class="btn btn-outline-primary btn-sm mt-auto fw-semibold rounded-3">
                                        <i class="fas fa-arrow-right me-1"></i> Ler artigo
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
