@extends('layout.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="fa fa-user-plus me-2"></i> Criar Novo Usuário</h2>
        <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li><i class="fa fa-exclamation-circle me-1"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('admin.users.store') }}" method="POST" class="row g-3">
                @csrf

                {{-- Nome --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nome</label>
                    <input type="text" name="name" class="form-control" placeholder="Digite o nome completo" required>
                </div>

                {{-- Email --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="email@exemplo.com" required>
                </div>

                {{-- Senha --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Senha</label>
                    <input type="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres" required>
                </div>

                {{-- Confirmar Senha --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirmar Senha</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Repita a senha" required>
                </div>

                {{-- Tipo de Usuário --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tipo de Usuário</label>
                    <select name="user_type" class="form-select" required>
                        <option value="" disabled selected>Selecione...</option>
                        <option value="1">Usuário Comum</option>
                        <option value="2">Admin Master</option>
                        <option value="3">Usuário do Curso + Loja</option>
                    </select>
                </div>

                {{-- Botão --}}
                <div class="col-12 text-end mt-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-save me-1"></i> Criar Usuário
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
