@extends('layout.layout')

@section('content')
<style>
    /* Estilo Base SAX */
    .letter-spacing-2 { letter-spacing: 2px; }
    .letter-spacing-3 { letter-spacing: 4px; }
    .x-small { font-size: 0.75rem; }

    .sax-divider {
        width: 60px;
        height: 3px;
        background-color: #000;
    }

    /* Tabs Estilizadas */
    .btn-sax-tab {
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        padding: 10px 0;
        font-weight: bold;
        font-size: 0.8rem;
        letter-spacing: 2px;
        color: #999;
        transition: 0.3s ease;
    }

    .btn-sax-tab.active {
        color: #000;
        border-bottom: 2px solid #000;
    }

    .btn-sax-tab:hover { color: #333; }

    /* Formulário de Luxo */
    .sax-label {
        display: block;
        font-size: 0.7rem;
        font-weight: bold;
        letter-spacing: 1.5px;
        margin-bottom: 8px;
        color: #000;
    }

    .sax-input {
        border: none;
        border-bottom: 1px solid #e0e0e0;
        border-radius: 0;
        padding: 12px 0;
        font-size: 0.9rem;
        transition: border-color 0.3s;
        background-color: transparent !important;
    }

    .sax-input:focus {
        box-shadow: none;
        border-color: #000;
    }

    .sax-input::placeholder { color: #ccc; font-weight: 300; }

    /* Botão de Envio */
    .btn-sax-submit {
        transition: all 0.4s ease;
        border: 1px solid #000;
    }

    .btn-sax-submit:hover {
        background-color: #fff !important;
        color: #000 !important;
    }

    /* Estilização dos Mapas */
    .map-section-title {
        font-size: 1.2rem;
        font-weight: bold;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 30px;
    }

    .map-card {
        background: #fff;
        padding: 15px;
        border: 1px solid #eee;
        height: 100%;
        transition: 0.3s;
    }

    .map-card:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }

    .map-card h6 {
        font-weight: bold;
        font-size: 0.85rem;
        letter-spacing: 1px;
        margin-bottom: 5px;
    }

    .map-card p {
        color: #777;
        font-size: 0.75rem;
        margin-bottom: 15px;
        min-height: 32px;
    }

    .map-wrapper {
        position: relative;
        overflow: hidden;
        background: #f0f0f0;
    }

    .map-wrapper iframe {
        width: 100%;
        height: 250px;
        filter: grayscale(100%);
        transition: 0.5s ease;
        border: 0;
    }

    .map-wrapper:hover iframe {
        filter: grayscale(0%);
    }

    /* Ajuste de Espaçamento Mobile */
    @media (max-width: 768px) {
        .map-card { margin-bottom: 20px; }
    }
</style>

<div class="contact-page-wrapper py-5 bg-white">
    <div class="container">
        {{-- Cabeçalho Minimalista --}}
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold text-uppercase letter-spacing-3 mb-2">Contato</h1>
            <div class="sax-divider mx-auto mb-3"></div>
            <p class="text-muted text-uppercase x-small letter-spacing-2">Estamos à sua disposição para o que precisar</p>
        </div>

        @if(session('success'))
            <div class="alert alert-dark rounded-0 border-0 d-flex align-items-center mb-4">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Seção de Formulário Centralizada --}}
        <div class="row justify-content-center mb-5 pb-5">
            <div class="col-lg-10">
                <div class="row g-5">
                    <div class="col-lg-12">
                        {{-- Seleção de Tipo de Atendimento --}}
                        <div class="mb-4 d-flex gap-3 justify-content-start border-bottom">
                            <button type="button" class="btn btn-sax-tab active" id="btn-atendimento" onclick="setFormType(1)">
                                ATENDIMENTO
                            </button>
                            <button type="button" class="btn btn-sax-tab" id="btn-curriculo" onclick="setFormType(2)">
                                TRABALHE CONOSCO
                            </button>
                        </div>

                        <form action="{{ route('contact.store') }}" method="POST" id="contactForm" enctype="multipart/form-data" class="sax-form">
                            @csrf
                            <input type="hidden" name="contact_type" id="contact_type" value="1">

                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="sax-label">NOME COMPLETO</label>
                                    <input type="text" name="name" class="form-control sax-input" placeholder="Ex: Maria Silva" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="sax-label">EMAIL</label>
                                    <input type="email" name="email" class="form-control sax-input" placeholder="email@exemplo.com" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="sax-label">TELEFONE</label>
                                    <input type="text" name="phone" class="form-control sax-input" placeholder="+595 XXX XXXXXX">
                                </div>

                                <div class="col-md-12 form-field" data-type="1">
                                    <label class="sax-label">MENSAGEM</label>
                                    <textarea name="message" class="form-control sax-input" rows="4" placeholder="Como podemos ajudar?" required></textarea>
                                </div>

                                <div class="col-md-12 form-field" data-type="2" style="display:none;">
                                    <label class="sax-label">ANEXAR CURRÍCULO (PDF/IMG)</label>
                                    <input type="file" name="attachment" class="form-control sax-input" accept=".pdf,image/*">
                                </div>

                                <div class="col-md-4 ms-auto">
                                    <button type="submit" class="btn btn-dark w-100 py-3 rounded-0 text-uppercase letter-spacing-2 fw-bold btn-sax-submit">
                                        Enviar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-5 opacity-0">

        {{-- Seção de Mapas Lado a Lado --}}
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h2 class="map-section-title">Nossas Unidades</h2>
            </div>
            
            {{-- Unidade CDE --}}
            <div class="col-lg-4">
                <div class="map-card">
                    <h6 class="text-uppercase">SAX CDE</h6>
                    <p>Av. San Blas, Ciudad del Este, Paraguai</p>
                    <div class="map-wrapper shadow-sm">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1865759.3980800086!2d-57.434554686265194!3d-24.028902543292244!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94f69aaaec5ef03d%3A0xff12a8b090a63ebd!2sSAX%20Department%20Store!5e0!3m2!1spt-BR!2spy!4v1770210106773!5m2!1spt-BR!2spy" loading="lazy"></iframe>
                    </div>
                </div>
            </div>

            {{-- Unidade Asunción --}}
            <div class="col-lg-4">
                <div class="map-card">
                    <h6 class="text-uppercase">SAX Asunción</h6>
                    <p>Paseo La Galería, Asunción, Paraguai</p>
                    <div class="map-wrapper shadow-sm">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d28864.905476014617!2d-57.634103725683595!3d-25.266777699999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x945da76681b0d661%3A0x2e9754f73b54e3a5!2sSAX%20Department%20Store%20-%20Asunci%C3%B3n!5e0!3m2!1spt-BR!2spy!4v1770210083150!5m2!1spt-BR!2spy" loading="lazy"></iframe>
                    </div>
                </div>
            </div>

            {{-- Unidade Pedro Juan --}}
            <div class="col-lg-4">
                <div class="map-card">
                    <h6 class="text-uppercase">SAX Pedro Juan Caballero</h6>
                    <p>Shopping Dubai, Pedro Juan Caballero, Paraguai</p>
                    <div class="map-wrapper shadow-sm">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d58954.282839244144!2d-55.76757534513292!3d-22.555054301589653!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94626f0079a38969%3A0xc5b346bd463b3b48!2sSAX%20Department%20Store%20-%20Pedro%20Juan%20Caballero!5e0!3m2!1spt-BR!2spy!4v1770210046629!5m2!1spt-BR!2spy" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setFormType(type) {
    document.getElementById('contact_type').value = type;
    
    document.querySelectorAll('.form-field').forEach(el => {
        el.style.display = el.dataset.type == type ? 'block' : 'none';
        el.querySelectorAll('input, textarea').forEach(i => i.required = el.dataset.type == type);
    });

    if(type == 1) {
        document.getElementById('btn-atendimento').classList.add('active');
        document.getElementById('btn-curriculo').classList.remove('active');
    } else {
        document.getElementById('btn-curriculo').classList.add('active');
        document.getElementById('btn-atendimento').classList.remove('active');
    }
}
</script>
@endsection