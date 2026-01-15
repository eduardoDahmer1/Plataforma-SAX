@extends('layout.dashboard')

@section('content')
<div class="sax-edit-wrapper">
    <div class="dashboard-header mb-5">
        <h2 class="sax-title text-uppercase letter-spacing-2">Actualizar Registro</h2>
        <div class="sax-divider-dark"></div>
    </div>

    <form action="{{ route('user.profile.update') }}" method="POST" class="sax-premium-form">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Nome --}}
            <div class="col-md-6 mb-3">
                <label class="sax-label">NOMBRE COMPLETO</label>
                <input type="text" name="name" class="form-control sax-input" value="{{ old('name', auth()->user()->name) }}" required>
            </div>

            {{-- Email --}}
            <div class="col-md-6 mb-3">
                <label class="sax-label">EMAIL</label>
                <input type="email" name="email" class="form-control sax-input" value="{{ old('email', auth()->user()->email) }}" required>
            </div>

            {{-- Telefone --}}
            <div class="col-md-12 mb-3">
                <label class="sax-label">TELÉFONO</label>
                <div class="d-flex gap-2">
                    <select name="phone_country" class="form-select sax-input w-auto">
                        <option value="+55" {{ auth()->user()->phone_country == '+55' ? 'selected' : '' }}>BRA (+55)</option>
                        <option value="+595" {{ auth()->user()->phone_country == '+595' ? 'selected' : '' }}>PRY (+595)</option>
                    </select>
                    <input type="text" name="phone_number" class="form-control sax-input flex-grow-1" value="{{ old('phone_number', auth()->user()->phone_number) }}">
                </div>
            </div>

            {{-- Endereço / Ubicación --}}
            <div class="col-12">
                <label class="sax-label">UBICACIÓN / DIRECCIÓN</label>
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="cep" placeholder="Código Postal" class="form-control sax-input" value="{{ old('cep', auth()->user()->cep) }}">
                    </div>
                    <div class="col-md-9">
                        <input type="text" name="address" placeholder="Dirección Completa" class="form-control sax-input" value="{{ old('address', auth()->user()->address) }}">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="city" placeholder="Ciudad" class="form-control sax-input" value="{{ old('city', auth()->user()->city) }}">
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="state" placeholder="Estado/Departamento" class="form-control sax-input" value="{{ old('state', auth()->user()->state) }}">
                    </div>
                </div>
            </div>

            {{-- Documento --}}
            <div class="col-md-6 mb-3">
                <label class="sax-label">NÚMERO DE DOCUMENTO</label>
                <input type="text" name="document" class="form-control sax-input" value="{{ old('document', auth()->user()->document ?? '') }}">
            </div>

            {{-- Cadastro na Sax --}}
            <div class="col-md-6 mb-3">
                <label class="sax-label">¿YA POSEE REGISTRO EN SAX?</label>
                <select name="already_registered" id="already_registered" class="form-select sax-input">
                    <option value="1" {{ auth()->user()->already_registered ? 'selected' : '' }}>Sí</option>
                    <option value="0" {{ !auth()->user()->already_registered ? 'selected' : '' }}>No</option>
                </select>
            </div>

            {{-- Número do cadastro --}}
            <div class="col-md-12 mb-3" id="sax_number_field" style="display: {{ auth()->user()->already_registered ? 'block' : 'none' }};">
                <label class="sax-label">NÚMERO DE REGISTRO INTERNO</label>
                <input type="text" name="additional_info" class="form-control sax-input" value="{{ old('additional_info', auth()->user()->additional_info ?? '') }}">
            </div>
        </div>

        <div class="d-flex justify-content-end mt-5">
            <button type="submit" class="btn btn-dark btn-sax-submit px-5 py-3 text-uppercase fw-bold letter-spacing-1">
                Actualizar Datos
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('already_registered').addEventListener('change', function() {
        document.getElementById('sax_number_field').style.display = this.value === '1' ? 'block' : 'none';
    });
</script>
@endsection
<style>
    /* Container e Header */
.sax-edit-wrapper {
    font-family: 'Inter', sans-serif;
    max-width: 800px;
}

.sax-title {
    font-weight: 700;
    font-size: 1.5rem;
    color: #1a1a1a;
}

.sax-divider-dark {
    width: 50px;
    height: 3px;
    background-color: #000;
    margin-top: 15px;
}

/* Formulário e Inputs */
.sax-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1.2px;
    color: #888;
    margin-bottom: 10px;
}

.sax-input {
    border: none !important;
    border-bottom: 1px solid #e0e0e0 !important;
    border-radius: 0 !important;
    padding: 12px 0 !important;
    background-color: transparent !important;
    font-size: 0.95rem;
    color: #1a1a1a;
    transition: border-color 0.3s ease;
}

.sax-input:focus {
    box-shadow: none !important;
    border-color: #000 !important;
    color: #000;
}

/* Selects */
select.sax-input {
    background-position: right 0 center;
    padding-right: 20px !important;
}

/* Botão de Envio */
.btn-sax-submit {
    border-radius: 0;
    font-size: 0.8rem;
    background-color: #000;
    border: 1px solid #000;
    transition: all 0.4s ease;
}

.btn-sax-submit:hover {
    background-color: #fff;
    color: #000;
}

/* Responsividade */
@media (max-width: 768px) {
    .sax-edit-wrapper {
        padding: 0 15px;
    }
}
</style>