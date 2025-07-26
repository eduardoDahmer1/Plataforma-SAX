@extends('layout.layout')

@section('content')
<div class="container py-5">
    <h2>Fale Conosco</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('contact.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Nome</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Telefone</label>
            <input type="text" name="phone" class="form-control">
        </div>

        <div class="mb-3">
            <label>Mensagem</label>
            <textarea name="message" class="form-control" rows="5" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</div>
@endsection
