@extends('layout.layout')

@section('content')
<div class="container py-5">
    <h2 class="mb-4"><i class="fas fa-envelope-open-text me-2"></i> Fale Conosco</h2>

    {{-- Mensagem de sucesso --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Botões de escolha --}}
    <div class="mb-4 d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" onclick="setFormType(1)">
            <i class="fas fa-comment-dots me-1"></i> Fale Conosco
        </button>
        <button type="button" class="btn btn-outline-success" onclick="setFormType(2)">
            <i class="fas fa-file-upload me-1"></i> Envie seu Currículo
        </button>
    </div>

    {{-- Formulário --}}
    <div class="card shadow-sm border-0 mb-5">
        <div class="card-body">
            <form action="{{ route('contact.store') }}" method="POST" id="contactForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="contact_type" id="contact_type" value="1">

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-user me-1"></i> Nome</label>
                    <input type="text" name="name" class="form-control" placeholder="Digite seu nome completo" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-envelope me-1"></i> Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Digite seu email" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-phone me-1"></i> Telefone</label>
                    <input type="text" name="phone" class="form-control" placeholder="(XX) XXXXX-XXXX">
                </div>

                <div class="mb-3 form-field" data-type="1">
                    <label class="form-label"><i class="fas fa-comment-alt me-1"></i> Mensagem ou Comentário</label>
                    <textarea name="message" class="form-control" rows="5" placeholder="Escreva aqui sua mensagem..." required></textarea>
                </div>

                <div class="mb-3 form-field" data-type="2" style="display:none;">
                    <label class="form-label"><i class="fas fa-paperclip me-1"></i> Currículo</label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,image/*">
                    <div class="form-text">Formatos aceitos: PDF ou imagem</div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-1"></i> Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Mapas das lojas --}}
    <div class="mb-5">
        <h4 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i> SAX - Ciudad del Este</h4>
        <div class="ratio ratio-16x9 mb-4">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3600.8882062800003!2d-54.60985242460801!3d-25.508774677511763!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94f69aaaec5ef03d%3A0xff12a8b090a63ebd!2sSAX%20Department%20Store!5e0!3m2!1spt-BR!2spy!4v1755633236261!5m2!1spt-BR!2spy" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <h4 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i> SAX - Asunción</h4>
        <div class="ratio ratio-16x9">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3608.114058350338!2d-57.5959706!3d-25.2667483!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x945da76681b0d661%3A0x2e9754f73b54e3a5!2sSAX%20Department%20Store%20-%20Asunci%C3%B3n!5e0!3m2!1spt-BR!2spy!4v1766423370378!5m2!1spt-BR!2spy" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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
}
</script>
@endsection
