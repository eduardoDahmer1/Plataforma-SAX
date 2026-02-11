<section class="section-padding bg-palace-soft" id="noite-arabe">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5" data-aos="fade-right">
                <div class="section-title text-start mb-4">
                    {{-- Tag: "Experiência Temática" --}}
                    <span class="text-gold text-uppercase letter-spacing-2">{{ $palace->tematica_tag ?? 'Experiência Temática' }}</span>
                    {{-- Título: "Noite Árabe" --}}
                    <h2 class="display-4 font-serif mt-2">{{ $palace->tematica_titulo }}</h2>
                </div>
                
                {{-- Descrição completa vinda do banco --}}
                <div class="text-secondary mb-5">
                    {!! nl2br(e($palace->tematica_descricao)) !!}
                </div>

                {{-- Botão com link direto para o WhatsApp do banco --}}
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" 
                   target="_blank" 
                   class="btn-palace">
                   Mais Informações
                </a>
            </div>

            <div class="col-lg-7 mt-5 mt-lg-0" data-aos="zoom-in">
                <div class="position-relative">
                    {{-- Imagem Temática vinda da pasta palace/ no storage --}}
                    <img src="{{ asset('storage/' . $palace->tematica_imagem) }}"
                        class="img-fluid rounded shadow-lg" 
                        alt="{{ $palace->tematica_titulo }}"
                        style="min-height: 450px; width: 100%; object-fit: cover;">

                    {{-- Badge de Preço dinâmico (ex: 24 U$) --}}
                    <div class="position-absolute bottom-0 end-0 p-4 d-none d-md-block"
                        style="background: #D4AF37; transform: translate(20px, 20px); border: 2px solid #000; box-shadow: 5px 5px 15px rgba(0,0,0,0.3);">
                        <h4 class="text-black mb-0 fw-bold" style="font-size: 1.8rem;">{{ $palace->tematica_preco }}</h4>
                        <small class="text-black text-uppercase fw-bold"
                            style="font-size: 0.75rem; letter-spacing: 1px;">Por Pessoa</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>