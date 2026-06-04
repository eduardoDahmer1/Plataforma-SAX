@php
    // O index centralizou as traduções. Aqui resolvemos apenas o título e o array de sliders.
    $heroTitle = $translation->section_one_title ?? $institucional->section_one_title;
    
    // Garante que o slider superior seja interpretado como array para o loop de forma segura
    $sliders = is_array($institucional->top_sliders) 
        ? $institucional->top_sliders 
        : json_decode($institucional->top_sliders, true);
@endphp

<section class="hero-slider">
    <div class="swiper mainSwiper">
        <div class="swiper-wrapper">
            @if(!empty($sliders))
                @foreach($sliders as $slide)
                    {{-- Tornamos o slide clicável envolvendo o conteúdo em um link --}}
                    <a href="{{ route('categories.index') }}" class="swiper-slide">
                        <div class="hero-overlay"></div>
                        <img src="{{ asset('storage/' . $slide) }}" alt="SAX Experience">
                        
                        <div class="hero-content text-center">
                            <div class="container">
                                <span class="hero-subtitle" data-aos="fade-up">
                                    {{ __('messages.exclusive_experience_subtitle') ?? 'Exclusive Experience' }}
                                </span>
                                
                                <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">
                                    {{ $heroTitle ?? 'SAX Department' }}
                                </h1>
                                
                                <div class="hero-line" data-aos="zoom-in" data-aos-delay="400"></div>
                                
                                <p class="hero-text" data-aos="fade-up" data-aos-delay="600">
                                    {{ __('messages.hero_luxury_text') ?? 'Onde o luxo encontra a exclusividade e o design define o estilo.' }}
                                </p>
                                
                                <div class="hero-btn-wrapper" data-aos="fade-up" data-aos-delay="800">
                                    <span class="btn-discover">
                                        {{ __('messages.descobrir_colecao_btn') ?? 'Descobrir Coleção' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            @else
                {{-- Fallback elegante caso não existam imagens cadastradas no slider --}}
                <div class="swiper-slide">
                    <div class="hero-overlay"></div>
                    <img src="https://placehold.co/1920x1080" alt="SAX Experience">
                    <div class="hero-content text-center">
                        <div class="container">
                            <h1 class="hero-title">SAX Department</h1>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Controles elegantes --}}
        <div class="swiper-nav-wrapper d-none d-md-flex">
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>