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
</x-admin.card>
@endsection
