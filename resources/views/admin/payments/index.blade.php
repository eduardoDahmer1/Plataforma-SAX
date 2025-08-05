@extends('layout.admin')

@section('content')
<div class="container">
    <h3 class="mb-4">Métodos de Pagamento</h3>

    <a href="{{ route('admin.payments.create') }}" class="btn btn-success mb-3">+ Adicionar Método de Pagamento</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Ativo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($methods as $method)
                <tr>
                    <td>{{ $method->name }}</td>
                    <td>{{ ucfirst($method->type) }}</td>
                    <td>{{ $method->active == 1 ? 'Ativo' : 'Inativo' }}</td>
                    <td>
                        <input type="checkbox" class="toggle-active" data-id="{{ $method->id }}" {{ $method->active == 1 ? 'checked' : '' }}>
                    </td>
                    <td>
                        <a href="{{ route('admin.payments.edit', $method->id) }}" class="btn btn-sm btn-primary">Editar</a>
                        <form action="{{ route('admin.payments.destroy', $method->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Quer mesmo excluir esse método?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
document.querySelectorAll('.toggle-active').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const id = this.dataset.id;
        const active = this.checked ? 1 : 2; // já ajustado para 1 ou 2

        fetch(`/admin/payments/${id}/toggle-active`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ active })
        })
        .then(res => {
            if (!res.ok) throw new Error('Erro ao atualizar status');
            return res.json();
        })
        .then(data => {
            alert(data.message);
            const statusCell = this.closest('tr').querySelector('td:nth-child(3)');
            statusCell.textContent = active == 1 ? 'Ativo' : 'Inativo';
        })
        .catch(() => {
            alert('Falha ao alterar status');
            this.checked = !this.checked;
        });
    });
});

</script>
@endsection
