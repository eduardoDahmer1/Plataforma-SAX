@extends('layout.layout')

@section('content')
<div class="container py-5">
    <h2>Fale Conosco</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3 d-flex gap-2">
        <button class="btn btn-outline-primary" onclick="setFormType(1)">Fale Conosco</button>
        <button class="btn btn-outline-success" onclick="setFormType(2)">Envie seu Currículo</button>
    </div>

    <form action="{{ route('contact.store') }}" method="POST" id="contactForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="contact_type" id="contact_type" value="1">

        {{-- Nome (sempre obrigatório) --}}
        <div class="mb-3">
            <label>Nome</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        {{-- Email (sempre obrigatório) --}}
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        {{-- Telefone (opcional) --}}
        <div class="mb-3">
            <label>Telefone</label>
            <input type="text" name="phone" class="form-control">
        </div>

        {{-- Mensagem (só para fale conosco) --}}
        <div class="mb-3 form-field" data-type="1">
            <label>Mensagem ou Comentario sobre o site</label>
            <textarea name="message" class="form-control" rows="5" required></textarea>
        </div>

        {{-- Currículo (só para tipo 2) --}}
        <div class="mb-3 form-field" data-type="2" style="display:none;">
            <label>Currículo (PDF ou imagem)</label>
            <input type="file" name="attachment" class="form-control" accept=".pdf,image/*" required>
        </div>

        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>

@endsection
