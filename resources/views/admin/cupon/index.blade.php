@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.cupons_desconto_titulo') }}"
        description="{{ __('messages.gestao_incentivos_desc') }}">
        <x-slot:actions>
            <a href="{{ route('admin.cupons.create') }}" class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold x-small tracking-wider">
                <i class="fas fa-plus me-2"></i> {{ __('messages.novo_cupon_btn') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Alertas --}}
    @if (session('error') || session('success'))
        <div class="alert {{ session('error') ? 'alert-danger' : 'alert-dark' }} border-0 rounded-0 x-small fw-bold text-uppercase py-3 mb-4 shadow-sm">
            <i class="fas {{ session('error') ? 'fa-exclamation-circle' : 'fa-check-circle' }} me-2"></i>
            {{ session('error') ?? session('success') }}
        </div>
    @endif

    {{-- Filtros --}}
    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-md-5">
            <label class="sax-label">{{ __('messages.cupon_buscar_label') }}</label>
            <input type="text" name="busca" value="{{ $busca }}" class="form-control sax-input"
                   placeholder="{{ __('messages.cupon_buscar_placeholder') }}">
        </div>
        <div class="col-md-4">
            <label class="sax-label">{{ __('messages.cupon_situacao_label') }}</label>
            <select name="situacao" class="form-select sax-input">
                <option value="">{{ __('messages.cupon_situacao_todas') }}</option>
                <option value="vigentes" {{ $situacao === 'vigentes' ? 'selected' : '' }}>{{ __('messages.cupon_situacao_vigente') }}</option>
                <option value="agendados" {{ $situacao === 'agendados' ? 'selected' : '' }}>{{ __('messages.cupon_situacao_agendado') }}</option>
                <option value="expirados" {{ $situacao === 'expirados' ? 'selected' : '' }}>{{ __('messages.cupon_situacao_expirado') }}</option>
                <option value="inativos" {{ $situacao === 'inativos' ? 'selected' : '' }}>{{ __('messages.cupon_situacao_inativo') }}</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark rounded-0 px-4 text-uppercase fw-bold x-small w-100">
                {{ __('messages.cupon_filtrar_btn') }}
            </button>
            @if ($busca !== '' || $situacao)
                <a href="{{ route('admin.cupons.index') }}" class="btn btn-outline-secondary rounded-0 px-3 x-small">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </div>
    </form>

    {{-- Grid de Cupons --}}
    <div class="row g-4">
        @forelse($cupons as $cupon)
            @php
                $situacaoCupon = !$cupon->ativo
                    ? ['texto' => __('messages.cupon_situacao_inativo'), 'classe' => 'bg-secondary']
                    : ($cupon->estaVigente()
                        ? ['texto' => __('messages.cupon_situacao_vigente'), 'classe' => 'bg-success']
                        : (!$cupon->temUsoDisponivel()
                            ? ['texto' => __('messages.cupon_situacao_esgotado'), 'classe' => 'bg-danger']
                            : ($cupon->data_inicio > now()
                                ? ['texto' => __('messages.cupon_situacao_agendado'), 'classe' => 'bg-info text-dark']
                                : ['texto' => __('messages.cupon_situacao_expirado'), 'classe' => 'bg-danger'])));
            @endphp

            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card border rounded-0 shadow-none sax-coupon-card h-100">
                    <div class="card-body p-0 d-flex flex-column h-100">
                        {{-- Cabeçalho do Ticket --}}
                        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                            <span class="badge {{ $situacaoCupon['classe'] }} rounded-0 x-small">{{ $situacaoCupon['texto'] }}</span>
                            <span class="badge {{ $cupon->ehPercentual() ? 'bg-primary' : 'bg-dark' }} rounded-0 x-small">
                                {{ $cupon->ehPercentual() ? '%' : '$' }}
                            </span>
                        </div>

                        {{-- Corpo do Ticket --}}
                        <div class="p-4 text-center border-bottom border-dashed">
                            <h2 class="h3 fw-900 tracking-tighter mb-1 text-uppercase">{{ $cupon->codigo }}</h2>
                            <span class="h5 fw-light text-dark font-monospace">{{ $cupon->rotuloDesconto() }}</span>
                        </div>

                        {{-- Detalhes Técnicos --}}
                        <div class="p-3 flex-grow-1">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="sax-label-mini">{{ __('messages.modelo_label_mini') }}</span>
                                <span class="x-small fw-bold text-dark text-end">{{ $cupon->rotuloEscopo() }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="sax-label-mini">{{ __('messages.cupon_vigencia_label') }}</span>
                                <span class="x-small fw-bold text-dark">
                                    {{ $cupon->data_inicio?->format('d/m/y') }} — {{ $cupon->data_final?->format('d/m/y') }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="sax-label-mini">{{ __('messages.cupon_usos_label') }}</span>
                                <span class="x-small fw-bold text-dark font-monospace">
                                    {{ $cupon->usado }} / {{ $cupon->quantidade ?? '∞' }}
                                </span>
                            </div>
                        </div>

                        {{-- Ações --}}
                        <div class="p-3 bg-light d-flex align-items-center gap-3 border-top">
                            <a href="{{ route('admin.cupons.show', $cupon) }}" class="text-dark text-decoration-none x-small fw-bold hover-underline">
                                {{ __('messages.cupon_ver_btn') }}
                            </a>
                            <a href="{{ route('admin.cupons.edit', $cupon) }}" class="text-dark text-decoration-none x-small fw-bold hover-underline">
                                {{ __('messages.editar_btn_mini') }}
                            </a>

                            <form action="{{ route('admin.cupons.toggle', $cupon) }}" method="POST" class="m-0">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-clean text-secondary x-small fw-bold">
                                    {{ $cupon->ativo ? __('messages.cupon_desativar_btn') : __('messages.cupon_ativar_btn') }}
                                </button>
                            </form>

                            <form action="{{ route('admin.cupons.destroy', $cupon) }}" method="POST"
                                onsubmit="return confirm('{{ __('messages.confirmar_eliminar_cupon') }}')" class="ms-auto m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-clean text-danger x-small fw-bold">
                                    {{ __('messages.eliminar_btn_mini') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 py-5 text-center border border-dashed">
                <p class="text-muted x-small text-uppercase tracking-wider mb-0 italic">{{ __('messages.sem_cupons_aviso') }}</p>
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="mt-5 d-flex justify-content-center">
        {{ $cupons->links() }}
    </div>
</x-admin.card>
@endsection
