@extends('layout.admin')

@section('content')
<x-admin.card>
<x-admin.page-header
        title="{{ __('messages.gateways_pagamento_titulo') }}"
        description="{{ __('messages.gateways_pagamento_desc') }}">
        <x-slot:actions>
            <a href="{{ route('admin.payments.create') }}" class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold tracking-wider">
                <i class="fa fa-plus me-2"></i> {{ __('messages.novo_metodo_btn') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Lista de Métodos --}}
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="bg-white">
                <tr class="text-uppercase x-small tracking-wider text-secondary">
                    <th class="py-3 border-0 fw-bold" style="width: 250px;">{{ __('messages.col_metodo') }}</th>
                    <th class="py-3 border-0 fw-bold">{{ __('messages.col_tipo') }}</th>
                    <th class="py-3 border-0 fw-bold">{{ __('messages.col_estado') }}</th>
                    <th class="py-3 border-0 fw-bold text-center" style="width: 100px;">{{ __('messages.col_visibilidade') }}</th>
                    <th class="py-3 border-0 fw-bold text-end">{{ __('messages.col_gestao') }}</th>
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
                                {{ $method->active == 1 ? __('messages.status_ativo') : __('messages.status_inativo') }}
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
                                {{ __('messages.configurar_link') }}
                            </a>
                            <form action="{{ route('admin.payments.destroy', $method->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirmar_exclusao_metodo') }}')" class="m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-clean text-danger x-small fw-bold tracking-tighter">
                                    {{ __('messages.eliminar_link') }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted small italic">{{ __('messages.sem_gateways_aviso') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
    document.querySelectorAll('.toggle-active').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const id = this.dataset.id;
            const active = this.checked ? 1 : 0;

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

                if(active === 1){
                    dot.classList.add('active');
                    text.textContent = 'ATIVO';
                    text.classList.replace('text-muted', 'text-dark');
                } else {
                    dot.classList.remove('active');
                    text.textContent = 'INATIVO';
                    text.classList.replace('text-dark', 'text-muted');
                }
            })
            .catch(() => {
                alert('Erro ao atualizar');
                this.checked = !this.checked;
            });
        });
    });
    </script>
</x-admin.card>
@endsection
