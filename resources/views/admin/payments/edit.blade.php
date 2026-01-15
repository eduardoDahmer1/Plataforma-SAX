@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    
    {{-- Navegação e Título --}}
    <div class="mb-5">
        <a href="{{ route('admin.payments.index') }}" class="text-decoration-none x-small fw-bold text-uppercase text-secondary tracking-wider">
            <i class="fa fa-chevron-left me-1"></i> Volver a métodos
        </a>
        <h1 class="h4 fw-light mt-2 mb-0 text-uppercase tracking-wider">
            {{ isset($method) ? 'Configurar Pasarela' : 'Nueva Pasarela de Pago' }}
        </h1>
        <div class="sax-divider-dark mt-3"></div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ isset($method) ? route('admin.payments.update', $method->id) : route('admin.payments.store') }}" method="POST">
                @csrf
                @if(isset($method)) @method('PUT') @endif

                {{-- Seção: Definição Básica --}}
                <section class="mb-5">
                    <h6 class="x-small fw-bold text-uppercase text-secondary tracking-tighter mb-4">Definición General</h6>
                    <div class="row g-4">
                        <div class="col-md-7">
                            <label for="name" class="sax-form-label">Nombre del Método</label>
                            <input type="text" name="name" id="name" class="form-control sax-input" 
                                   placeholder="Ej: Bancard, Transferencia..." value="{{ $method->name ?? '' }}" required>
                        </div>

                        <div class="col-md-5">
                            <label for="type" class="sax-form-label">Tipo de Conexión</label>
                            <select name="type" id="type" class="form-select sax-input" required>
                                <option value="bank" {{ (isset($method) && $method->type == 'bank') ? 'selected' : '' }}>Depósito / Transferencia</option>
                                <option value="gateway" {{ (isset($method) && $method->type == 'gateway') ? 'selected' : '' }}>Gateway Automático</option>
                            </select>
                        </div>
                    </div>
                </section>

                {{-- Seção Dinâmica: Detalhes Bancários --}}
                <section class="mb-5 bank-only border-start border-3 ps-4" style="{{ isset($method) && $method->type === 'gateway' ? 'display:none' : '' }}">
                    <h6 class="x-small fw-bold text-uppercase text-secondary tracking-tighter mb-4">Datos para Transferencia</h6>
                    <div class="row">
                        <div class="col-12">
                            <label for="bank_details" class="sax-form-label">Instrucciones de Pago</label>
                            <textarea name="bank_details" id="bank_details" class="form-control sax-input" rows="5" 
                                      placeholder="Incluya: Banco, Tipo de cuenta, RUC, etc.">{{ $method->bank_details ?? '' }}</textarea>
                            <small class="text-muted x-small mt-2 d-block italic">Estos datos se mostrarán al cliente al finalizar la compra.</small>
                        </div>
                    </div>
                </section>

                {{-- Seção Dinâmica: Credenciais Gateway --}}
                <section class="mb-5 gateway-only border-start border-3 border-dark ps-4" style="{{ isset($method) && $method->type === 'gateway' ? '' : 'display:none' }}">
                    <h6 class="x-small fw-bold text-uppercase text-dark tracking-tighter mb-4">Credenciales de API</h6>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="public_key" class="sax-form-label">Public Key</label>
                            <input type="text" name="public_key" id="public_key" class="form-control sax-input font-monospace small" 
                                   value="{{ $method->credentials['public_key'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label for="private_key" class="sax-form-label">Private Key / Token</label>
                            <input type="password" name="private_key" id="private_key" class="form-control sax-input font-monospace small" 
                                   value="{{ $method->credentials['private_key'] ?? '' }}">
                        </div>
                    </div>
                </section>

                {{-- Configurações de Ativação --}}
                <div class="border-top pt-4 mt-5 d-flex align-items-center justify-content-between">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="active" value="1" class="form-check-input cursor-pointer" id="active" 
                               {{ (isset($method) && $method->active) ? 'checked' : '' }}>
                        <label class="form-check-label x-small fw-bold text-uppercase ms-2 cursor-pointer" for="active">
                            Habilitar método en el checkout
                        </label>
                    </div>

                    <button type="submit" class="btn btn-dark rounded-0 px-5 py-2 fw-bold text-uppercase tracking-wider small">
                        {{ isset($method) ? 'Guardar Cambios' : 'Crear Pasarela' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Minimalist Technical UI */
    .tracking-wider { letter-spacing: 0.12em; }
    .tracking-tighter { letter-spacing: 0.05em; }
    .x-small { font-size: 0.65rem; }
    .italic { font-style: italic; }
    .sax-divider-dark { width: 40px; height: 2px; background: #000; }
    
    .sax-form-label {
        font-size: 0.65rem;
        font-weight: 800;
        color: #999;
        text-transform: uppercase;
        margin-bottom: 8px;
        display: block;
    }

    .sax-input {
        border-radius: 0;
        border: 1px solid #e5e5e5;
        padding: 12px 15px;
        font-size: 0.9rem;
        transition: 0.2s;
    }

    .sax-input:focus {
        border-color: #000;
        box-shadow: none;
        background-color: #fcfcfc;
    }

    .form-check-input:checked {
        background-color: #000;
        border-color: #000;
    }

    .cursor-pointer { cursor: pointer; }
</style>

<script>
function toggleFields() {
    const type = document.getElementById('type').value;
    const gatewayFields = document.querySelectorAll('.gateway-only');
    const bankFields = document.querySelectorAll('.bank-only');
    
    gatewayFields.forEach(el => el.style.display = (type === 'gateway') ? 'block' : 'none');
    bankFields.forEach(el => el.style.display = (type === 'bank') ? 'block' : 'none');
}

document.getElementById('type').addEventListener('change', toggleFields);
toggleFields();
</script>
@endsection