<section class="counter-section parallax-window" style="background-image: url('{{ asset('storage/' . $institucional->section_one_image) }}')">
    <div class="luxury-overlay"></div>
    <div class="container position-relative z-index-2">
        <div class="row text-center align-items-center">
            
            {{-- Item 01 --}}
            <div class="col-md-4 mb-5 mb-md-0" data-aos="fade-up">
                <div class="counter-item">
                    <h2 class="counter-number text-gold">
                        <span class="counter" data-target="{{ $institucional->stat_brands_count }}">0</span>
                    </h2>
                    <div class="counter-line mx-auto"></div>
                    <p class="counter-label">Marcas<br>Internacionais</p>
                </div>
            </div>

            {{-- Item 02 --}}
            <div class="col-md-4 mb-5 mb-md-0" data-aos="fade-up" data-aos-delay="200">
                <div class="counter-item">
                    <h2 class="counter-number text-gold">
                        <span class="counter" data-target="{{ $institucional->stat_sqm_count }}">0</span><small class="unit">k</small>
                    </h2>
                    <div class="counter-line mx-auto"></div>
                    <p class="counter-label">Mil M²<br>de Experiência</p>
                </div>
            </div>

            {{-- Item 03 --}}
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                <div class="counter-item">
                    <h2 class="counter-number text-gold">
                        <span class="counter" data-target="{{ $institucional->stat_employees_count }}">0</span><small class="unit">+</small>
                    </h2>
                    <div class="counter-line mx-auto"></div>
                    <p class="counter-label">Colaboradores<br>Especializados</p>
                </div>
            </div>

        </div>
    </div>
</section>
