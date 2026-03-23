@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    
    {{-- Header Minimalista --}}
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="h4 fw-light text-uppercase tracking-wider mb-1">Gateways de pagamento</h1>
            <p class="small text-secondary mb-0">Configure os métodos de transação disponíveis na finalização da compra</p>
        </div>
        <a href="{{ route('admin.payments.create') }}" class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold tracking-wider">
            <i class="fa fa-plus me-2"></i> Novo Método
        </a>
    </div>

    {{-- Lista de Métodos --}}
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="bg-white">
                <tr class="text-uppercase x-small tracking-wider text-secondary">
                    <th class="py-3 border-0 fw-bold" style="width: 250px;">Método</th>
                    <th class="py-3 border-0 fw-bold">Tipo</th>
                    <th class="py-3 border-0 fw-bold">Estado</th>
                    <th class="py-3 border-0 fw-bold text-center" style="width: 100px;">Visibilidad</th>
                    <th class="py-3 border-0 fw-bold text-end">Gestão</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @forelse($methods as $method)
                <tr class="border-bottom clickable-row">
                    <td class="py-4">
                        <div class="d-flex align-items-center">
                            <div class="payment-icon me-3">
                                <i class="fa fa-credit-card"></i>
                            </div>
                            <span class="fw-bold text-dark text-uppercase small">{{ $method->name }}</span>
                        </div>
                    </td>
                    <td class="py-4">
                        <span class="x-small text-secondary fw-bold text-uppercase bg-light px-2 py-1">{{ $method->type }}</span>
                    </td>
                    <td class="py-4">
                        <div class="status-indicator">
                            <span class="status-dot {{ $method->active == 1 ? 'active' : '' }}"></span>
                            <span class="x-small text-uppercase fw-bold {{ $method->active == 1 ? 'text-dark' : 'text-muted' }}">
                                {{ $method->active == 1 ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </td>
                    <td class="py-4 text-center">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input toggle-active cursor-pointer" type="checkbox" 
                                   data-id="{{ $method->id }}" {{ $method->active == 1 ? 'checked' : '' }}>
                        </div>
                    </td>
                    <td class="py-4 text-end">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ route('admin.payments.edit', $method->id) }}" class="text-dark text-decoration-none x-small fw-bold tracking-tighter hover-underline">
                                CONFIGURAR
                            </a>
                            <form action="{{ route('admin.payments.destroy', $method->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este método de pago?')" class="m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-clean text-danger x-small fw-bold tracking-tighter">
                                    ELIMINAR
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted small italic">Não há gateways de pagamento configurados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-active').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const id = this.dataset.id;
        const active = this.checked ? 1 : 2;

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
            if (!res.ok) throw new Error('Error');
            return res.json();
        })
        .then(data => {
            const row = this.closest('tr');
            const dot = row.querySelector('.status-dot');
            const text = row.querySelector('.status-indicator span:last-child');
            
            if(active == 1){
                dot.classList.add('active');
                text.textContent = 'ACTIVO';
                text.classList.replace('text-muted', 'text-dark');
            } else {
                dot.classList.remove('active');
                text.textContent = 'INACTIVO';
                text.classList.replace('text-dark', 'text-muted');
            }
        })
        .catch(() => {
            alert('Error al actualizar');
            this.checked = !this.checked;
        });
    });
});
</script>
@endsection