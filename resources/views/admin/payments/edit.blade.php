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

        {{-- Detalhes de conta bancária --}}
        <div class="mb-3 bank-only">
            <label for="bank_details" class="form-label">Detalhes da Conta Bancária</label>
            <textarea name="bank_details" class="form-control" rows="4" placeholder="Ex: Nome do banco, número da conta..." @if(isset($method) && $method->type === 'gateway') style="display:none" @endif>{{ $method->bank_details ?? '' }}</textarea>
        </div>

        {{-- Campos para Gateway --}}
        <div class="mb-3 gateway-only" style="{{ isset($method) && $method->type === 'gateway' ? '' : 'display:none' }}">
            <label for="public_key" class="form-label">Chave Pública</label>
            <input type="text" name="public_key" class="form-control" value="{{ $method->credentials['public_key'] ?? '' }}">
        </div>

        <div class="mb-3 gateway-only" style="{{ isset($method) && $method->type === 'gateway' ? '' : 'display:none' }}">
            <label for="private_key" class="form-label">Chave Privada</label>
            <input type="text" name="private_key" class="form-control" value="{{ $method->credentials['private_key'] ?? '' }}">
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
    document.querySelectorAll('.gateway-only').forEach(el => el.style.display = (type === 'gateway') ? 'block' : 'none');
    document.querySelectorAll('.bank-only').forEach(el => el.style.display = (type === 'bank') ? 'block' : 'none');
}

document.getElementById('type').addEventListener('change', toggleFields);
toggleFields();
</script>
@endsection
