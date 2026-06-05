<section class="py-5" id="eventos">
    <div class="container py-5">
        <div class="row g-3">
            <div class="col-md-6" data-aos="fade-right">
                <div class="h-100 p-5 bg-dark text-white d-flex flex-column justify-content-center">
                    {{-- Título e Descrição traduzidos --}}
                    <h2 class="display-5 font-serif mb-4">{{ $t->palace_eventos_titulo ?? $palace->eventos_titulo }}</h2>
                    <p class="lead opacity-75 mb-4">{{ $t->palace_eventos_descricao ?? $palace->eventos_descricao }}</p>
                    
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" class="text-gold text-decoration-none fw-bold">
                        {{ __('messages.solicitar_orcamento_btn') ?? 'SOLICITAR ORÇAMENTO' }} 
                        <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row g-3">
                    @php 
                        $galeria = is_array($palace->eventos_galeria) ? $palace->eventos_galeria : json_decode($palace->eventos_galeria, true); 
                    @endphp
                    @if(!empty($galeria))
                        @foreach(array_slice($galeria, 0, 4) as $foto)
                        <div class="col-6" data-aos="zoom-in">
                            <img src="{{ asset('storage/' . $foto) }}" class="img-fluid rounded shadow-sm w-100" style="height: 200px; object-fit: cover;" alt="Evento Palace">
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>