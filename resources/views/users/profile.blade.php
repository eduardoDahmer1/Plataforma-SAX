@extends('layout.layout')

@section('content')
<div class="row">
    <div class="col-md-3">
        @include('users.components.menu')
    </div>
    <div class="col-md-9">
        <h2>Atualizar Cadastro</h2>

        <form action="{{ route('user.profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Nome --}}
            <div class="mb-3">
                <label>Nome</label>
                <input type="text" name="name" class="form-control" 
                    value="{{ old('name', auth()->user()->name) }}" required>
            </div>

            {{-- Email --}}
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" 
                    value="{{ old('email', auth()->user()->email) }}" required>
            </div>

            {{-- Telefone --}}
            <div class="mb-3">
                <label>Telefone</label>
                <div class="d-flex gap-2">
                    <select name="phone_country" class="form-select" style="width: 120px;">
                        <option value="+55" {{ auth()->user()->phone_country == '+55' ? 'selected' : '' }}>Brasil (+55)</option>
                        <option value="+595" {{ auth()->user()->phone_country == '+595' ? 'selected' : '' }}>Paraguai (+595)</option>
                    </select>
                    <input type="text" name="phone_number" class="form-control" 
                        value="{{ old('phone_number', auth()->user()->phone_number) }}">
                </div>
            </div>

            {{-- Endereço --}}
            <div class="mb-3">
                <label>Endereço / Ubicación</label>
                <input type="text" name="cep" placeholder="CEP" class="form-control mb-2"
                    value="{{ old('cep', auth()->user()->cep) }}">
                <input type="text" name="address" placeholder="Endereço completo" class="form-control mb-2"
                    value="{{ old('address', auth()->user()->address) }}">
                <input type="text" name="city" placeholder="Cidade" class="form-control mb-2"
                    value="{{ old('city', auth()->user()->city) }}">
                <input type="text" name="state" placeholder="Estado" class="form-control"
                    value="{{ old('state', auth()->user()->state) }}">
            </div>

            {{-- Cadastro na Sax --}}
            <div class="mb-3">
                <label>Já possui cadastro na Sax?</label>
                <select name="already_registered" id="already_registered" class="form-select">
                    <option value="1" {{ auth()->user()->already_registered ? 'selected' : '' }}>Sim</option>
                    <option value="0" {{ !auth()->user()->already_registered ? 'selected' : '' }}>Não</option>
                </select>
            </div>

            {{-- Número do cadastro (aparece só se já estiver cadastrado) --}}
            <div class="mb-3" id="sax_number_field"
                style="display: {{ auth()->user()->already_registered ? 'block' : 'none' }};">
                <label>Número do cadastro</label>
                <input type="text" name="additional_info" class="form-control"
                    value="{{ old('additional_info', auth()->user()->additional_info ?? '') }}">
            </div>

            {{-- Script para mostrar/ocultar --}}
            <script>
                const alreadyRegisteredSelect = document.getElementById('already_registered');
                const saxNumberField = document.getElementById('sax_number_field');

                alreadyRegisteredSelect.addEventListener('change', function() {
                    saxNumberField.style.display = this.value === '1' ? 'block' : 'none';
                });
            </script>

            <button type="submit" class="btn btn-success">Atualizar</button>
        </form>
    </div>
</div>
@endsection
