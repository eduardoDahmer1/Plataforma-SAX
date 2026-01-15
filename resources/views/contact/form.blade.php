@extends('layout.layout')

@section('content')
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

        <div class="row g-5">
            {{-- Lado Esquerdo: Formulário --}}
            <div class="col-lg-7">
                {{-- Seleção de Tipo de Atendimento --}}
                <div class="mb-4 d-flex gap-3 justify-content-start">
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

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="sax-label">NOME COMPLETO</label>
                            <input type="text" name="name" class="form-control sax-input" placeholder="Ex: Maria Silva" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="sax-label">EMAIL</label>
                            <input type="email" name="email" class="form-control sax-input" placeholder="email@exemplo.com" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="sax-label">TELEFONE</label>
                            <input type="text" name="phone" class="form-control sax-input" placeholder="+595 XXX XXXXXX">
                        </div>

                        <div class="col-md-12 mb-4 form-field" data-type="1">
                            <label class="sax-label">MENSAGEM</label>
                            <textarea name="message" class="form-control sax-input" rows="5" placeholder="Como podemos ajudar?" required></textarea>
                        </div>

                        <div class="col-md-12 mb-4 form-field" data-type="2" style="display:none;">
                            <label class="sax-label">ANEXAR CURRÍCULO (PDF/IMG)</label>
                            <input type="file" name="attachment" class="form-control sax-input" accept=".pdf,image/*">
                        </div>

                        <div class="col-md-12">
                            <button type="submit" class="btn btn-dark w-100 py-3 rounded-0 text-uppercase letter-spacing-2 fw-bold btn-sax-submit">
                                Enviar Mensagem
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Lado Direito: Informações e Unidades --}}
            <div class="col-lg-5">
                <div class="ps-lg-4">
                    <h5 class="fw-bold text-uppercase letter-spacing-2 mb-4">Nossas Unidades</h5>
                    
                    <div class="unidade-item mb-4 pb-3 border-bottom">
                        <h6 class="fw-bold mb-1">SAX - CIUDAD DEL ESTE</h6>
                        <p class="text-muted x-small mb-2">Av. San Blas, Cd. del Este, Paraguai</p>
                        <div class="ratio ratio-21x9 rounded-0 overflow-hidden shadow-sm">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3601.4286694602526!2d-54.6110903!3d-25.5074218!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94f6904609800001%3A0x6a0f6d8309e3e78a!2sSAX%20Department%20Store!5e0!3m2!1spt-BR!2spy!4v1700000000000" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>

                    <div class="unidade-item">
                        <h6 class="fw-bold mb-1">SAX - ASUNCIÓN</h6>
                        <p class="text-muted x-small mb-2">Paseo La Galería, Asunción, Paraguai</p>
                        <div class="ratio ratio-21x9 rounded-0 overflow-hidden shadow-sm">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3606.848332029765!2d-57.5684742!3d-25.2756621!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x940974f000000001%3A0x867332f1a6f87f9b!2sSAX%20Asunci%C3%B3n!5e0!3m2!1spt-BR!2spy!4v1700000000001" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setFormType(type) {
    // Atualiza campo oculto
    document.getElementById('contact_type').value = type;
    
    // Alterna visibilidade dos campos
    document.querySelectorAll('.form-field').forEach(el => {
        el.style.display = el.dataset.type == type ? 'block' : 'none';
        el.querySelectorAll('input, textarea').forEach(i => i.required = el.dataset.type == type);
    });

    // Alterna classes dos botões (Tabs)
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

.btn-sax-tab:hover {
    color: #333;
}

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

.sax-input::placeholder {
    color: #ccc;
    font-weight: 300;
}

/* Botão de Envio */
.btn-sax-submit {
    transition: all 0.4s ease;
    border: 1px solid #000;
}

.btn-sax-submit:hover {
    background-color: #fff !important;
    color: #000 !important;
}

/* Ajustes de Mapa */
.ratio iframe {
    filter: grayscale(100%);
    transition: 0.5s ease;
}

.ratio:hover iframe {
    filter: grayscale(0%);
}

.unidade-item h6 {
    letter-spacing: 1px;
}
</style>