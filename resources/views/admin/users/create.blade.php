@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fa fa-user-plus me-2"></i> {{ __('messages.criar_usuario_titulo') }}</h2>
    </div>

    <x-admin.alert />

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('admin.users.store') }}" method="POST" class="row g-3">
                @csrf

                {{-- Nome --}}
                <div class="col-md-6">
                    <label class="sax-form-label">{{ __('messages.nome_label') }}</label>
                    <input type="text" name="name" class="form-control sax-input" 
                           placeholder="{{ __('messages.nome_placeholder') }}" value="{{ old('name') }}" required>
                </div>

                {{-- Email --}}
                <div class="col-md-6">
                    <label class="sax-form-label">{{ __('messages.email_label') }}</label>
                    <input type="email" name="email" class="form-control sax-input" 
                           placeholder="{{ __('messages.email_placeholder') }}" value="{{ old('email') }}" required>
                </div>

                {{-- Senha --}}
                <div class="col-md-6">
                    <label class="sax-form-label">{{ __('messages.senha_label') }}</label>
                    <input type="password" name="password" class="form-control sax-input" 
                           placeholder="{{ __('messages.senha_placeholder') }}" required>
                </div>

                {{-- Confirmar Senha --}}
                <div class="col-md-6">
                    <label class="sax-form-label">{{ __('messages.confirmar_senha_label') }}</label>
                    <input type="password" name="password_confirmation" class="form-control sax-input" 
                           placeholder="{{ __('messages.confirmar_senha_placeholder') }}" required>
                </div>

                {{-- Tipo de Usuário --}}
                <div class="col-md-6">
                    <label class="sax-form-label">{{ __('messages.tipo_usuario_label') }}</label>
                    <select name="user_type" class="form-select sax-input" required>
                        <option value="" disabled selected>{{ __('messages.selecione_placeholder') }}</option>
                        <option value="2" {{ old('user_type') == 2 ? 'selected' : '' }}>{{ __('messages.usuario_comum') }}</option>
                        <option value="1" {{ old('user_type') == 1 ? 'selected' : '' }}>{{ __('messages.admin_master') }}</option>
                        <option value="3" {{ old('user_type') == 3 ? 'selected' : '' }}>{{ __('messages.usuario_curso_loja') }}</option>
                    </select>
                </div>

                {{-- Botão --}}
                <div class="col-12">
                    <x-admin.form-actions 
                        :cancelRoute="route('admin.clients.index')" 
                        submitLabel="{{ __('messages.criar_usuario_btn') }}" />
                </div>
            </form>
        </div>
    </div>
</x-admin.card>
@endsection