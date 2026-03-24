@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    
    {{-- Navegação e Título --}}
    <div class="mb-5">
        <a href="{{ route('admin.payments.index') }}" class="text-decoration-none x-small fw-bold text-uppercase text-secondary tracking-wider">
            <i class="fa fa-chevron-left me-1"></i> Voltar aos métodos
        </a>
        <h1 class="h4 fw-light mt-2 mb-0 text-uppercase tracking-wider">
            Novo gateway de pagamento
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
                    <h6 class="x-small fw-bold text-uppercase text-secondary tracking-tighter mb-4">Definição geral</h6>
                    <div class="row g-4">
                        <div class="col-md-7">
                            <label for="name" class="sax-form-label">Nome do método</label>
                            <input type="text" name="name" id="name" class="form-control sax-input" 
                                   placeholder="Ex.: Bancard V2, Depósito bancário..." value="{{ $method->name ?? '' }}" required>
                        </div>

                        <div class="col-md-5">
                            <label for="type" class="sax-form-label">Tipo de conexão</label>
                            <select name="type" id="type" class="form-select sax-input" required>
                                <option value="bank" {{ (isset($method) && $method->type == 'bank') ? 'selected' : '' }}>Depósito / Transferência</option>
                                <option value="gateway" {{ (isset($method) && $method->type == 'gateway') ? 'selected' : '' }}>Gateway Automático</option>
                            </select>
                        </div>
                    </div>
                </section>

                {{-- Seção Dinâmica: Detalhes Bancários --}}
                <section class="mb-5 bank-only border-start border-3 ps-4" style="{{ isset($method) && $method->type === 'gateway' ? 'display:none' : '' }}">
                    <h6 class="x-small fw-bold text-uppercase text-secondary tracking-tighter mb-4">Dados para transferência</h6>
                    <div class="row">
                        <div class="col-12">
                            <label for="bank_details" class="sax-form-label">Instruções de pagamento</label>
                            <textarea name="bank_details" id="bank_details" class="form-control sax-input" rows="5" 
                                      placeholder="Inclua: banco, tipo de conta, RUC, agência, observações...">{{ $method->bank_details ?? '' }}</textarea>
                            <small class="text-muted x-small mt-2 d-block italic">Estes dados serão exibidos ao cliente ao finalizar a compra.</small>
                        </div>
                    </div>
                </section>

                {{-- Seção Dinâmica: Credenciais Gateway --}}
                <section class="mb-5 gateway-only border-start border-3 border-dark ps-4" style="{{ isset($method) && $method->type === 'gateway' ? '' : 'display:none' }}">
                    <h6 class="x-small fw-bold text-uppercase text-dark tracking-tighter mb-4">Credenciais de API</h6>
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
                        <div class="col-12" id="sandboxControl" style="display:none">
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="sandbox" value="1" class="form-check-input cursor-pointer" id="sandbox" checked>
                                <label class="form-check-label x-small fw-bold text-uppercase ms-2 cursor-pointer" for="sandbox">
                                    Usar sandbox
                                </label>
                            </div>
                            <small class="text-muted x-small mt-2 d-block italic">Quando ativo, o gateway usa a URL de homologação. Quando desativado, usa produção.</small>
                        </div>
                    </div>
                </section>

                {{-- Configurações de Ativação --}}
                <div class="border-top pt-4 mt-5 d-flex align-items-center justify-content-between">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="active" value="1" class="form-check-input cursor-pointer" id="active" 
                               {{ (isset($method) && $method->active) ? 'checked' : '' }}>
                        <label class="form-check-label x-small fw-bold text-uppercase ms-2 cursor-pointer" for="active">
                            Habilitar método na finalização da compra
                        </label>
                    </div>

                    <button type="submit" class="btn btn-dark rounded-0 px-5 py-2 fw-bold text-uppercase tracking-wider small">
                        Criar gateway
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleFields() {
    const type = document.getElementById('type').value;
    const name = (document.getElementById('name')?.value || '').trim().toLowerCase();
    const gatewayFields = document.querySelectorAll('.gateway-only');
    const bankFields = document.querySelectorAll('.bank-only');
    const sandboxControl = document.getElementById('sandboxControl');
    
    gatewayFields.forEach(el => el.style.display = (type === 'gateway') ? 'block' : 'none');
    bankFields.forEach(el => el.style.display = (type === 'bank') ? 'block' : 'none');

    if (sandboxControl) {
        sandboxControl.style.display = (type === 'gateway' && name === 'bancard v2') ? 'block' : 'none';
    }
}

document.getElementById('type').addEventListener('change', toggleFields);
document.getElementById('name').addEventListener('input', toggleFields);
toggleFields();
</script>
@endsection