@extends('layout.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">Criar Novo Usuário</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST">
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
            <label>Senha</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Confirmar Senha</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Tipo de Usuário</label>
            <select name="user_type" class="form-select" required>
                <option value="1">Usuário Comum</option>
                <option value="2">Admin Master</option>
                <option value="3">Usuário do Curso + Loja</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Criar Usuário</button>
    </form>
</div>
@endsection
