@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ $cupon->codigo }}"
        description="{{ $cupon->descricao ?: $cupon->rotuloEscopo() }}">
        <x-slot:actions>
            <a href="{{ route('admin.cupons.edit', $cupon) }}" class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold x-small tracking-wider">
                {{ __('messages.editar_btn_mini') }}
            </a>
            <a href="{{ route('admin.cupons.index') }}" class="btn-back-minimal ms-3">
                <i class="fas fa-chevron-left me-1"></i> {{ __('messages.voltar_listagem_link') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('success'))
        <div class="alert alert-dark border-0 rounded-0 x-small fw-bold text-uppercase py-3 mb-4">{{ session('success') }}</div>
    @endif

    @php
        $situacao = !$cupon->ativo
            ? ['texto' => __('messages.cupon_situacao_inativo'), 'classe' => 'bg-secondary']
            : ($cupon->estaVigente()
                ? ['texto' => __('messages.cupon_situacao_vigente'), 'classe' => 'bg-success']
                : (!$cupon->temUsoDisponivel()
                    ? ['texto' => __('messages.cupon_situacao_esgotado'), 'classe' => 'bg-danger']
                    : ($cupon->data_inicio > now()
                        ? ['texto' => __('messages.cupon_situacao_agendado'), 'classe' => 'bg-info text-dark']
                        : ['texto' => __('messages.cupon_situacao_expirado'), 'classe' => 'bg-danger'])));
    @endphp

    {{-- Painel de números --}}
    <div class="row g-3 mb-5">
        <div class="col-6 col-lg-3">
            <div class="border p-3 h-100">
                <span class="sax-label-mini d-block mb-1">{{ __('messages.cupon_situacao_label') }}</span>
                <span class="badge {{ $situacao['classe'] }} rounded-0 x-small">{{ $situacao['texto'] }}</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="border p-3 h-100">
                <span class="sax-label-mini d-block mb-1">{{ __('messages.cupon_desconto_label') }}</span>
                <span class="h5 mb-0 font-monospace">{{ $cupon->rotuloDesconto() }}</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="border p-3 h-100">
                <span class="sax-label-mini d-block mb-1">{{ __('messages.cupon_usos_label') }}</span>
                <span class="h5 mb-0 font-monospace">
                    {{ $cupon->usado }} / {{ $cupon->quantidade ?? '∞' }}
                </span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="border p-3 h-100">
                <span class="sax-label-mini d-block mb-1">{{ __('messages.cupon_total_descontado_label') }}</span>
                <span class="h5 mb-0 font-monospace">{{ currency_format($totalDescontado) }}</span>
            </div>
        </div>
    </div>

    {{-- Regras --}}
    <div class="mb-5">
        <span class="sax-section-title">{{ __('messages.regras_aplicacao_sec') }}</span>

        <div class="row g-3 mt-1 sax-details-view">
            <div class="col-md-6">
                <p class="mb-2"><strong>{{ __('messages.show_modelo_label') }}</strong> {{ $cupon->rotuloEscopo() }}</p>
                <p class="mb-2"><strong>{{ __('messages.show_tipo_label') }}</strong>
                    {{ $cupon->ehPercentual() ? __('messages.porcentagem_opt') : __('messages.valor_fixo_opt') }}
                </p>
                <p class="mb-2"><strong>{{ __('messages.show_data_label') }}</strong>
                    {{ $cupon->data_inicio?->format('d/m/Y') }} — {{ $cupon->data_final?->format('d/m/Y') }}
                </p>
                <p class="mb-2"><strong>{{ __('messages.cupon_limite_usuario_label') }}:</strong>
                    {{ $cupon->limite_por_usuario ?? __('messages.ilimitado_text') }}
                </p>
            </div>
            <div class="col-md-6">
                <p class="mb-2"><strong>{{ __('messages.compra_minima_label') }}:</strong>
                    {{ $cupon->valor_minimo ? currency_format($cupon->valor_minimo) : '—' }}
                </p>
                <p class="mb-2"><strong>{{ __('messages.cupon_desconto_maximo_label') }}:</strong>
                    {{ $cupon->desconto_maximo ? currency_format($cupon->desconto_maximo) : '—' }}
                </p>
                <p class="mb-2"><strong>{{ __('messages.cupon_preco_maximo_produto_label') }}:</strong>
                    {{ $cupon->preco_maximo_produto ? currency_format($cupon->preco_maximo_produto) : '—' }}
                </p>
                <p class="mb-2"><strong>{{ __('messages.cupon_usos_restantes_label') }}:</strong>
                    {{ $cupon->usosRestantes() ?? __('messages.ilimitado_text') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Histórico de uso --}}
    <div>
        <span class="sax-section-title">{{ __('messages.cupon_historico_uso_titulo') }}</span>

        <div class="table-responsive mt-3">
            <table class="table align-middle x-small">
                <thead>
                    <tr class="text-uppercase text-secondary">
                        <th>{{ __('messages.cupon_col_cliente') }}</th>
                        <th>{{ __('messages.cupon_col_pedido') }}</th>
                        <th class="text-end">{{ __('messages.cupon_col_desconto') }}</th>
                        <th class="text-end">{{ __('messages.cupon_col_data') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usos as $uso)
                        <tr>
                            <td>{{ $uso->user->name ?? '—' }}</td>
                            <td class="font-monospace">{{ $uso->order->order_number ?? '—' }}</td>
                            <td class="text-end font-monospace">{{ currency_format($uso->desconto) }}</td>
                            <td class="text-end">{{ $uso->usado_em?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">{{ __('messages.cupon_sem_uso_ainda') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $usos->links() }}</div>
    </div>
</x-admin.card>
@endsection
