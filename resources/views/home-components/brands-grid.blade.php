@if (isset($brands) && $brands->count() > 0)
    <section class="sax-brands-section">
        <h2 class="sax-main-title">{{ __('messages.marcas_recomendadas') }}</h2>

        <div class="sax-carousel-master">
            <div class="sax-carousel-3d" 
                 id="brandsCarousel" 
                 data-storage-base="{{ asset('storage') }}" 
                 data-marcas-url="{{ url('marcas') }}"
                 data-fallback-banner="{{ asset('storage/uploads/banner_horizontal.webp') }}">
                {{-- Injetado via JS --}}
            </div>

            {{-- Overlay de Nome e Navegação --}}
            <div class="sax-carousel-footer">
                <div id="saxBrandName" class="sax-brand-label"></div>

                <div class="sax-controls">
                    <button type="button" id="saxPrev" class="sax-nav-btn">←</button>
                    <div class="sax-indicators" id="saxDots"></div>
                    <button type="button" id="saxNext" class="sax-nav-btn">→</button>
                </div>
            </div>
        </div>
    </section>

    {{-- Datos del server para el carrusel 3D (lógica en home.js) --}}
    <script>
        window.saxBrandsData = {!! $brands->toJson() !!};
    </script>
@endif