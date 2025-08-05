@extends('layout.admin')

@section('content')
<div class="container">
    <h3 class="mb-4">{{ isset($method) ? 'Editar' : 'Adicionar' }} Método de Pagamento</h3>

    <form action="{{ isset($method) ? route('admin.payments.update', $method->id) : route('admin.payments.store') }}" method="POST">
        @csrf
        @if(isset($method))
            @method('PUT')
        @endif

        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" name="name" class="form-control" value="{{ $method->name ?? '' }}" required>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Tipo</label>
            <select name="type" id="type" class="form-select" required>
                <option value="bank" {{ (isset($method) && $method->type == 'bank') ? 'selected' : '' }}>Conta Bancária</option>
                <option value="gateway" {{ (isset($method) && $method->type == 'gateway') ? 'selected' : '' }}>Gateway</option>
            </select>
        </div>

        <div class="mb-3" id="details-container">
            <label for="details" class="form-label">Detalhes</label>
            <textarea name="details" class="form-control" rows="4" placeholder="Ex: Nome do banco, número da conta, instruções..." required>{{ $method->details ?? '' }}</textarea>
        </div>

        <div class="mb-3 gateway-only" style="display: none;">
            <label for="public_key" class="form-label">Chave Pública</label>
            <input type="text" name="public_key" class="form-control" value="{{ $method->public_key ?? '' }}">
        </div>

        <div class="mb-3 gateway-only" style="display: none;">
            <label for="private_key" class="form-label">Chave Privada</label>
            <input type="text" name="private_key" class="form-control" value="{{ $method->private_key ?? '' }}">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="active" value="1" class="form-check-input" id="active" {{ (isset($method) && $method->active) ? 'checked' : '' }}>
            <label class="form-check-label" for="active">Ativo</label>
        </div>

        <button type="submit" class="btn btn-primary">{{ isset($method) ? 'Atualizar' : 'Salvar' }}</button>
    </form>
</div>

<script>
function toggleFields() {
    const type = document.getElementById('type').value;
    const gatewayFields = document.querySelectorAll('.gateway-only');
    if(type === 'gateway') {
        gatewayFields.forEach(el => el.style.display = 'block');
    } else {
        gatewayFields.forEach(el => el.style.display = 'none');
    }
}

document.getElementById('type').addEventListener('change', toggleFields);

// Inicializa no carregamento da página
toggleFields();
</script>
@endsection