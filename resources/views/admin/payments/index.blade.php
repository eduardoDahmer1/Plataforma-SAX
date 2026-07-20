@extends('layout.admin')

@push('styles')
<style>
    .payments-list{border:1px solid #eaecf0;border-radius:16px;overflow:hidden;background:#fff}.payments-table{margin:0!important}.payments-table thead th{padding:14px 16px!important}.payments-table tbody td{padding:16px!important}.payments-table tbody tr:last-child td{border-bottom:0}.payment-method-cell{min-width:230px}.payment-method-icon{width:42px;height:42px;flex:0 0 42px;display:grid;place-items:center;color:#2970ff;background:#eef4ff;border-radius:12px}.payment-method-name{display:block;color:#101828;font-size:.82rem;font-weight:750;line-height:1.25}.payment-method-description{display:block;margin-top:3px;color:#98a2b3;font-size:.69rem}.payment-type-badge{display:inline-flex;align-items:center;gap:5px;padding:6px 9px;color:#475467;background:#f2f4f7;border-radius:999px;font-size:.66rem;font-weight:700}.payment-status{display:inline-flex;align-items:center;gap:7px;padding:6px 10px;border-radius:999px;font-size:.67rem;font-weight:700}.payment-status.is-active{color:#027a48;background:#ecfdf3}.payment-status.is-inactive{color:#667085;background:#f2f4f7}.payment-status .status-dot{width:7px;height:7px;background:#98a2b3;border-radius:50%}.payment-status .status-dot.active{background:#12b76a}.payment-switch-wrap{display:inline-flex;align-items:center;justify-content:center;padding:7px 10px;background:#f8fafc;border:1px solid #eaecf0;border-radius:999px}.payment-switch-wrap .form-check{min-height:0;margin:0;padding-left:2.25em}.payment-switch-wrap .form-check-input{width:2.2em;height:1.15em;margin-top:0;cursor:pointer}.payment-actions{display:flex;justify-content:flex-end;gap:7px}.payment-action{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;color:#667085!important;background:#f8fafc;border:1px solid #eaecf0;border-radius:10px!important;text-decoration:none!important}.payment-action:hover{color:#2970ff!important;background:#eef4ff;border-color:#c9d9ff}.payment-action.is-danger{color:#d92d20!important}.payment-action.is-danger:hover{background:#fef3f2;border-color:#fecdca}.payment-empty{padding:48px 20px!important}.payment-empty-icon{width:48px;height:48px;display:grid;place-items:center;margin:0 auto 12px;color:#98a2b3;background:#f2f4f7;border-radius:14px}@media(max-width:767.98px){.payments-list{border:0;background:transparent;overflow:visible}.payments-table,.payments-table tbody,.payments-table tr,.payments-table td{display:block;width:100%}.payments-table thead{display:none}.payments-table tbody{display:grid;gap:12px}.payments-table tbody tr{padding:16px;background:#fff;border:1px solid #eaecf0!important;border-radius:15px;box-shadow:0 7px 22px rgba(16,24,40,.045)}.payments-table tbody td{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 0!important;border-bottom:1px solid #f2f4f7!important;text-align:right!important}.payments-table tbody td:first-child{padding-top:0!important}.payments-table tbody td:last-child{padding-bottom:0!important;border-bottom:0!important}.payments-table tbody td:before{content:attr(data-label);color:#98a2b3;font-size:.65rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase}.payments-table .payment-method-cell{min-width:0;justify-content:flex-start!important}.payments-table .payment-method-cell:before{display:none}.payment-actions{justify-content:flex-end}.payment-action{width:auto;padding:0 11px;gap:6px}.payment-action span{display:inline!important}.payments-table .payment-empty{display:block!important;text-align:center!important}.payments-table .payment-empty:before{display:none}}
</style>
@endpush

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
    <div class="table-responsive payments-list">
        <table class="table align-middle payments-table">
            <thead class="bg-white">
                <tr class="text-uppercase x-small tracking-wider text-secondary">
                    <th class="border-0 fw-bold">{{ __('messages.col_metodo') }}</th>
                    <th class="border-0 fw-bold">{{ __('messages.col_tipo') }}</th>
                    <th class="border-0 fw-bold">{{ __('messages.col_estado') }}</th>
                    <th class="border-0 fw-bold text-center">{{ __('messages.col_visibilidade') }}</th>
                    <th class="py-3 border-0 fw-bold text-end">{{ __('messages.col_gestao') }}</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @forelse($methods as $method)
                <tr>
                    <td class="payment-method-cell" data-label="{{ __('messages.col_metodo') }}">
                        <div class="d-flex align-items-center">
                            <div class="payment-method-icon me-3">
                                <i class="fa-solid {{ $method->type === 'gateway' ? 'fa-credit-card' : 'fa-building-columns' }}"></i>
                            </div>
                            <div>
                                <span class="payment-method-name">{{ $method->name }}</span>
                                <span class="payment-method-description">{{ $method->type === 'gateway' ? 'Pagamento digital' : 'Transferência bancária' }}</span>
                            </div>
                        </div>
                    </td>
                    <td data-label="{{ __('messages.col_tipo') }}">
                        <span class="payment-type-badge"><i class="fa-solid {{ $method->type === 'gateway' ? 'fa-bolt' : 'fa-building-columns' }}"></i>{{ $method->type === 'gateway' ? 'Gateway' : 'Conta bancária' }}</span>
                    </td>
                    <td data-label="{{ __('messages.col_estado') }}">
                        <div class="status-indicator payment-status {{ $method->active == 1 ? 'is-active' : 'is-inactive' }}">
                            <span class="status-dot {{ $method->active == 1 ? 'active' : '' }}"></span>
                            <span class="{{ $method->active == 1 ? 'text-dark' : 'text-muted' }}">
                                {{ $method->active == 1 ? __('messages.status_ativo') : __('messages.status_inativo') }}
                            </span>
                        </div>
                    </td>
                    <td class="text-center" data-label="{{ __('messages.col_visibilidade') }}">
                        <div class="payment-switch-wrap">
                          <div class="form-check form-switch">
                            <input class="form-check-input toggle-active cursor-pointer" type="checkbox"
                                   aria-label="Alterar visibilidade de {{ $method->name }}"
                                   data-id="{{ $method->id }}" {{ $method->active == 1 ? 'checked' : '' }}>
                          </div>
                        </div>
                    </td>
                    <td class="text-end" data-label="{{ __('messages.col_gestao') }}">
                        <div class="payment-actions">
                            <a href="{{ route('admin.payments.edit', $method->id) }}" class="payment-action" title="{{ __('messages.configurar_link') }}">
                                <i class="fa-solid fa-sliders"></i><span class="d-none">{{ __('messages.configurar_link') }}</span>
                            </a>
                            <form action="{{ route('admin.payments.destroy', $method->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirmar_exclusao_metodo') }}')" class="m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="payment-action is-danger" title="{{ __('messages.eliminar_link') }}">
                                    <i class="fa-regular fa-trash-can"></i><span class="d-none">{{ __('messages.eliminar_link') }}</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center payment-empty"><div class="payment-empty-icon"><i class="fa-solid fa-credit-card"></i></div><span class="text-muted small">{{ __('messages.sem_gateways_aviso') }}</span></td>
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
                const status = row.querySelector('.status-indicator');

                if(active === 1){
                    dot.classList.add('active');
                    status.classList.add('is-active');
                    status.classList.remove('is-inactive');
                    text.textContent = 'ATIVO';
                    text.classList.replace('text-muted', 'text-dark');
                } else {
                    dot.classList.remove('active');
                    status.classList.add('is-inactive');
                    status.classList.remove('is-active');
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
