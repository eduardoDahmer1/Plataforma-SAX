@php
    // Pool de imagens disponíveis (banners + galeria + capa); pega uma diferente da usada no parallax da seção "Sobre"
    $sceneryUrls = collect($sceneryPool ?? [])->map(fn($path) => asset('storage/' . $path))->values();
    $statsImage = $sceneryUrls[1 % max($sceneryUrls->count(), 1)] ?? ($institucional->section_one_image ? asset('storage/' . $institucional->section_one_image) : 'https://placehold.co/1920x600');
@endphp

<section class="counter-section parallax-window" style="background-image: url('{{ $statsImage }}')" data-scenery-pool="{{ $sceneryUrls->toJson() }}">
    <div class="luxury-overlay"></div>
    <div class="container position-relative z-index-2">
        <div class="row text-center align-items-center">
            
            {{-- Item 01 - Marcas --}}
            <div class="col-md-4 mb-5 mb-md-0" data-aos="fade-up">
                <div class="counter-item">
                    <h2 class="counter-number text-gold">
                        <span class="counter" data-target="{{ $institucional->stat_brands_count ?? 0 }}">0</span>
                    </h2>
                    <div class="counter-line mx-auto"></div>
                    <p class="counter-label">
                        {!! __('messages.stats_marcas_label') ?? 'Marcas Internacionais' !!}
                    </p>
                </div>
            </div>

            {{-- Item 02 - Estrutura / M² --}}
            <div class="col-md-4 mb-5 mb-md-0" data-aos="fade-up" data-aos-delay="200">
                <div class="counter-item">
                    <h2 class="counter-number text-gold">
                        <span class="counter" data-target="{{ $institucional->stat_sqm_count ?? 0 }}">0</span><small class="unit">k</small>
                    </h2>
                    <div class="counter-line mx-auto"></div>
                    <p class="counter-label">
                        {!! __('messages.stats_area_label') ?? 'Mil M²<br>de Experiência' !!}
                    </p>
                </div>
            </div>

            {{-- Item 03 - Colaboradores --}}
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                <div class="counter-item">
                    <h2 class="counter-number text-gold">
                        <span class="counter" data-target="{{ $institucional->stat_employees_count ?? 0 }}">0</span><small class="unit">+</small>
                    </h2>
                    <div class="counter-line mx-auto"></div>
                    <p class="counter-label">
                        {!! __('messages.stats_colaboradores_label') ?? 'Colaboradores<br>Especializados' !!}
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>