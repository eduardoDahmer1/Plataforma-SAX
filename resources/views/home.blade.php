@extends('layout.layout')

@section('content')
    <div>

        <h2 class="mb-4"><i class="fas fa-home me-2"></i> Bem-vindo à Página Inicial</h2>
        <p class="text-muted">Confira os produtos mais recentes em nosso catálogo.</p>

        {{-- Alertas --}}
        <x-alert type="success" :message="session('success')" />

        @php
            // Configuração dos Títulos das Seções
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

            // Array de Banners (Mapeamento explícito para evitar erro de escopo)
            $banners = [
                $banner1 ?? null, $banner2 ?? null, $banner3 ?? null, $banner4 ?? null, $banner5 ?? null,
                $banner6 ?? null, $banner7 ?? null, $banner8 ?? null, $banner9 ?? null, $banner10 ?? null,
            ];

            $settings = $settings ?? \App\Models\Generalsetting::first();
            
            // Seções que utilizam Slider Swiper
            $sliderSections = ['destaque', 'melhores_avaliacoes', 'lancamentos', 'tendencias'];
        @endphp

        {{-- Loop de Categorias de Destaque --}}
        @foreach ($highlightTitles as $key => $title)
            @php
                $show = $settings->{'show_highlight_' . $key} ?? 0;
                $products = $highlights[$key] ?? collect();
            @endphp

            @if ($show && $products->isNotEmpty())
                <h4 class="mt-5 mb-3"><i class="fas fa-star me-2"></i> {{ $title }}</h4>

                @if (in_array($key, $sliderSections))
                    <div class="swiper mySwiper mb-4">
                        <div class="swiper-wrapper">
                            @foreach ($products as $item)
                                <div class="swiper-slide">
                                    @include('home-components.product-card', ['item' => $item])
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                @else
                    <div class="row mb-4">
                        @foreach ($products as $item)
                            <div class="col-6 col-md-3 mb-4">
                                @include('home-components.product-card', ['item' => $item])
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Banner Publicitário da Seção --}}
                @if (isset($banners[$loop->index]) && $banners[$loop->index])
                    <div class="my-4 text-center">
                        <img src="{{ asset('storage/uploads/' . $banners[$loop->index]) }}"
                            alt="Banner {{ $title }}" class="img-fluid rounded banner-img">
                    </div>
                @endif
            @endif
        @endforeach

        {{-- Sessão de Blogs --}}
        @if (isset($blogs) && $blogs->isNotEmpty())
            @include('home-components.blog-section')
        @endif
        @include('home-components.form-home')

    </div>
@endsection