@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <h3><i class="fa fa-credit-card me-1"></i> Métodos de Pagamento</h3>
        <a href="{{ route('admin.payments.create') }}" class="btn btn-success mt-2 mt-md-0">
            <i class="fa fa-plus me-1"></i> Adicionar Método de Pagamento
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th><i class="fa fa-money-bill-wave me-1"></i> Nome</th>
                    <th><i class="fa fa-layer-group me-1"></i> Tipo</th>
                    <th><i class="fa fa-info-circle me-1"></i> Status</th>
                    <th><i class="fa fa-toggle-on me-1"></i> Ativo</th>
                    <th><i class="fa fa-cogs me-1"></i> Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($methods as $method)
                    <tr>
                        <td>{{ $method->name }}</td>
                        <td>{{ ucfirst($method->type) }}</td>
                        <td>
                            @if($method->active == 1)
                                <span class="badge bg-success"><i class="fa fa-check-circle me-1"></i> Ativo</span>
                            @else
                                <span class="badge bg-secondary"><i class="fa fa-times-circle me-1"></i> Inativo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <input type="checkbox" class="toggle-active" data-id="{{ $method->id }}" {{ $method->active == 1 ? 'checked' : '' }}>
                        </td>
                        <td class="d-flex flex-wrap gap-1">
                            <a href="{{ route('admin.payments.edit', $method->id) }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-edit me-1"></i> Editar
                            </a>
                            <form action="{{ route('admin.payments.destroy', $method->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Quer mesmo excluir esse método?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash me-1"></i> Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-active').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const id = this.dataset.id;
        const active = this.checked ? 1 : 2; // 1 = Ativo, 2 = Inativo

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
            const statusCell = this.closest('tr').querySelector('td:nth-child(3)');
            if(active == 1){
                statusCell.innerHTML = '<span class="badge bg-success"><i class="fa fa-check-circle me-1"></i> Ativo</span>';
            } else {
                statusCell.innerHTML = '<span class="badge bg-secondary"><i class="fa fa-times-circle me-1"></i> Inativo</span>';
            }
        })
        .catch(() => {
            alert('Falha ao alterar status');
            this.checked = !this.checked;
        });
    });
});
</script>
@endsection
