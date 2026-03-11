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
<style>
.counter-section {
    position: relative;
    padding: 120px 0;
    background-attachment: fixed; /* Parallax Nativo */
    background-size: cover;
    background-position: center;
    overflow: hidden;
}

/* Overlay sofisticado com degradê lateral */
.luxury-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: radial-gradient(circle, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.92) 100%);
    z-index: 1;
}

.z-index-2 { position: relative; z-index: 2; }

/* Tipografia dos Números */
.counter-number {
    font-family: 'Playfair Display', serif;
    font-size: clamp(3rem, 6vw, 5rem);
    font-weight: 700;
    line-height: 1;
    margin-bottom: 15px;
    display: flex;
    justify-content: center;
    align-items: baseline;
}

.counter-number .unit {
    font-size: 1.5rem;
    font-family: 'Montserrat', sans-serif;
    margin-left: 5px;
    color: #c5a059;
}

/* Traço dourado fino e elegante */
.counter-line {
    width: 30px;
    height: 1px;
    background: #c5a059;
    margin-bottom: 20px;
    opacity: 0.6;
}

/* Labels */
.counter-label {
    font-family: 'Montserrat', sans-serif;
    color: rgba(255, 255, 255, 0.8);
    text-transform: uppercase;
    letter-spacing: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    line-height: 1.6;
}

/* Ajustes Mobile */
@media (max-width: 768px) {
    .counter-section {
        padding: 80px 0;
        background-attachment: scroll; /* Melhora performance no mobile */
    }
    .counter-number {
        font-size: 3.5rem;
    }
    .counter-label {
        letter-spacing: 2px;
        font-size: 0.65rem;
    }
}
</style>