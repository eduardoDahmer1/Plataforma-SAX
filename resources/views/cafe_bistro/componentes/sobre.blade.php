{{-- Tarefa 5: Sobre --}}
<section id="sobre" class="section-padding" style="background: var(--azul-navy);">
    <div class="container">
        <div class="row align-items-center gy-5">

            {{-- Foto --}}
            <div class="col-lg-7" data-reveal="left">
                @if($cafeBistro->sobre_imagen)
                    <img src="{{ asset('storage/'.$cafeBistro->sobre_imagen) }}"
                         alt="{{ __('messages.cafe_about_image_alt') }}"
                         class="w-100 rounded"
                         style="height: 31.25rem; object-fit: cover;">
                @else
                    <div class="img-placeholder rounded" style="height: 31.25rem;">
                        {{ __('messages.photo_placeholder') }}
                    </div>
                @endif
            </div>

            {{-- Texto --}}
            <div class="col-lg-5" data-reveal="right">
                <span class="eyebrow">{{ __('messages.sobre_nos') }}</span>
                <div class="divider"></div>
                <h2 class="section-title mb-4">{{ $t?->cafe_sobre_titulo ?? $cafeBistro->sobre_titulo ?? __('messages.cafe_about_title_fallback') }}</h2>
                <p class="sobre-texto">
                    {{ $t?->cafe_sobre_texto ?? $cafeBistro->sobre_texto ?? __('messages.cafe_about_text_fallback') }}
                </p>
            </div>

        </div>
    </div>
</section>
