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
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('contact.store') }}" method="POST" id="contactForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="contact_type" id="contact_type" value="1">

                {{-- Nome --}}
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-user me-1"></i> Nome</label>
                    <input type="text" name="name" class="form-control" placeholder="Digite seu nome completo" required>
                </div>

                {{-- Email --}}
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-envelope me-1"></i> Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Digite seu email" required>
                </div>

                {{-- Telefone --}}
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-phone me-1"></i> Telefone</label>
                    <input type="text" name="phone" class="form-control" placeholder="(XX) XXXXX-XXXX">
                </div>

                {{-- Mensagem (tipo 1) --}}
                <div class="mb-3 form-field" data-type="1">
                    <label class="form-label"><i class="fas fa-comment-alt me-1"></i> Mensagem ou Comentário</label>
                    <textarea name="message" class="form-control" rows="5" placeholder="Escreva aqui sua mensagem..." required></textarea>
                </div>

                {{-- Currículo (tipo 2) --}}
                <div class="mb-3 form-field" data-type="2" style="display:none;">
                    <label class="form-label"><i class="fas fa-paperclip me-1"></i> Currículo</label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,image/*">
                    <div class="form-text">Formatos aceitos: PDF ou imagem</div>
                </div>

                {{-- Botão de envio --}}
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-1"></i> Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script de troca do tipo de formulário --}}
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
