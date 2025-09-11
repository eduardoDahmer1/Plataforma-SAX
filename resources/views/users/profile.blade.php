@extends('layout.dashboard')

@section('content')
    <h2 class="mb-4">Atualizar Cadastro</h2>

    <form action="{{ route('user.profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Nome --}}
        <div class="mb-3">
            <label>Nome</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}"
                    required>
            </div>
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label>Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}"
                    required>
            </div>
        </div>

        {{-- Telefone --}}
        <div class="mb-3">
            <label>Telefone</label>
            <div class="d-flex gap-2">
                <select name="phone_country" class="form-select" style="max-width: 120px;">
                    <option value="+55" {{ auth()->user()->phone_country == '+55' ? 'selected' : '' }}>Brasil
                        (+55)</option>
                    <option value="+595" {{ auth()->user()->phone_country == '+595' ? 'selected' : '' }}>Paraguai
                        (+595)</option>
                </select>
                <div class="input-group flex-grow-1">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    <input type="text" name="phone_number" class="form-control"
                        value="{{ old('phone_number', auth()->user()->phone_number) }}">
                </div>
            </div>
        </div>

        {{-- Endereço --}}
        <div class="mb-3">
            <label>Endereço / Ubicación</label>
            <div class="row g-2">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-map-pin"></i></span>
                        <input type="text" name="cep" placeholder="CEP" class="form-control"
                            value="{{ old('cep', auth()->user()->cep) }}">
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-home"></i></span>
                        <input type="text" name="address" placeholder="Endereço completo" class="form-control"
                            value="{{ old('address', auth()->user()->address) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <input type="text" name="city" placeholder="Cidade" class="form-control"
                        value="{{ old('city', auth()->user()->city) }}">
                </div>
                <div class="col-md-6">
                    <input type="text" name="state" placeholder="Estado" class="form-control"
                        value="{{ old('state', auth()->user()->state) }}">
                </div>
            </div>
        </div>

        {{-- Cadastro na Sax --}}
        <div class="mb-3">
            <label>Já possui cadastro na Sax?</label>
            <select name="already_registered" id="already_registered" class="form-select">
                <option value="1" {{ auth()->user()->already_registered ? 'selected' : '' }}>Sim</option>
                <option value="0" {{ !auth()->user()->already_registered ? 'selected' : '' }}>Não</option>
            </select>
        </div>

        {{-- Número do cadastro --}}
        <div class="mb-3" id="sax_number_field"
            style="display: {{ auth()->user()->already_registered ? 'block' : 'none' }};">
            <label>Número do cadastro</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                <input type="text" name="additional_info" class="form-control"
                    value="{{ old('additional_info', auth()->user()->additional_info ?? '') }}">
            </div>
        </div>

        {{-- Documento --}}
        <div class="mb-3">
            <label>Documento</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                <input type="text" name="document" class="form-control"
                    value="{{ old('document', auth()->user()->document ?? '') }}">
            </div>
        </div>

        <button type="submit" class="btn btn-success w-100 mt-3"><i class="fas fa-save me-2"></i>Atualizar</button>
    </form>

    {{-- Script --}}
    <script>
        const alreadyRegisteredSelect = document.getElementById('already_registered');
        const saxNumberField = document.getElementById('sax_number_field');

        alreadyRegisteredSelect.addEventListener('change', function() {
            saxNumberField.style.display = this.value === '1' ? 'block' : 'none';
        });
    </script>
@endsection
