@extends('layout.dashboard')

@section('content')
    <div class="sax-wishlist-wrapper">
        <div class="dashboard-header mb-5 d-flex justify-content-between align-items-end">
            <div>
                <h2 class="sax-title text-uppercase letter-spacing-2 mb-2">{{ __('messages.cupon_meus_cupons_titulo') }}</h2>
                <p class="text-muted mb-0">{{ __('messages.cupon_meus_cupons_desc') }}</p>
                <div class="sax-divider-dark mt-3"></div>
            </div>
            <a href="{{ route('user.dashboard') }}" class="btn-back-minimal">
                <i class="fas fa-chevron-left me-1"></i> {{ __('messages.voltar') }}
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm mb-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success shadow-sm mb-4">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger shadow-sm mb-4">{{ session('error') }}</div>
        @endif

        {{-- Cupom aplicado no carrinho agora --}}
        @if ($resumo['cupon'])
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <div class="small text-uppercase fw-bold text-success mb-1">{{ __('messages.cupon_aplicado_label') }}</div>
                        <h5 class="mb-1">{{ $resumo['cupon']->codigo }}</h5>
                        <div class="text-muted small">
                            {{ __('messages.desconto') }}: <strong class="text-success">- {{ currency_format($resumo['desconto']) }}</strong>
                            · {{ __('messages.total') }}: <strong>{{ currency_format($resumo['total']) }}</strong>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('cart.view') }}" class="btn btn-dark px-4">{{ __('messages.cupon_ir_carrinho_btn') }}</a>
                        <form action="{{ route('user.cupons.remove') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary px-4">{{ __('messages.cupon_remover_btn') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Aplicar um código --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form action="{{ route('user.cupons.apply') }}" method="POST" class="row g-2 align-items-center">
                    @csrf
                    <div class="col-12 col-lg-9">
                        <label for="codigo" class="sax-label mb-2 d-block">{{ __('messages.cupon_codigo_label') }}</label>
                        <input id="codigo" type="text" name="codigo" maxlength="60" required
                               class="form-control sax-input text-uppercase"
                               placeholder="{{ __('messages.cupon_placeholder') }}">
                    </div>
                    <div class="col-12 col-lg-3">
                        <label class="d-none d-lg-block sax-label mb-2">&nbsp;</label>
                        <button type="submit" class="btn btn-dark w-100 py-2">{{ __('messages.cupon_aplicar_btn') }}</button>
                    </div>
                </form>
                <small class="text-muted d-block mt-2">{{ __('messages.cupon_aplicar_ajuda') }}</small>
            </div>
        </div>

        {{-- Cupons disponíveis --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="sax-section-title m-0">{{ __('messages.cupon_disponiveis_titulo') }}</h6>
            <span class="badge bg-light text-dark border">{{ $disponiveis->count() }}</span>
        </div>

        <div class="d-flex flex-column gap-3 mb-5">
            @forelse($disponiveis as $cupon)
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <div class="small text-muted text-uppercase fw-bold mb-1">{{ __('messages.codigo_label_mini') }}</div>
                            <h5 class="mb-2">{{ $cupon->codigo }}</h5>

                            <div class="text-muted small mb-1">
                                <strong>{{ $cupon->rotuloDesconto() }}</strong> · {{ $cupon->rotuloEscopo() }}
                            </div>

                            @if ($cupon->descricao)
                                <div class="text-muted small mb-1">{{ $cupon->descricao }}</div>
                            @endif

                            <div class="small text-muted">
                                {{ __('messages.cupon_valido_ate', ['data' => $cupon->data_final->format('d/m/Y')]) }}
                                @if ($cupon->valor_minimo)
                                    · {{ __('messages.cupon_min_compra', ['valor' => currency_format($cupon->valor_minimo)]) }}
                                @endif
                                @if ($cupon->desconto_maximo)
                                    · {{ __('messages.cupon_max_desconto', ['valor' => currency_format($cupon->desconto_maximo)]) }}
                                @endif
                                @if ($cupon->preco_maximo_produto)
                                    · {{ __('messages.cupon_max_preco_produto', ['valor' => currency_format($cupon->preco_maximo_produto)]) }}
                                @endif
                            </div>
                        </div>

                        <form action="{{ route('user.cupons.apply') }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <input type="hidden" name="codigo" value="{{ $cupon->codigo }}">
                            <button type="submit" class="btn btn-outline-dark px-4"
                                {{ ($resumo['cupon']->id ?? null) === $cupon->id ? 'disabled' : '' }}>
                                {{ ($resumo['cupon']->id ?? null) === $cupon->id
                                    ? __('messages.cupon_em_uso_btn')
                                    : __('messages.cupon_usar_btn') }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state border rounded-3 bg-white py-5 text-center">
                    <i class="fas fa-ticket-alt fa-2x mb-3 opacity-50"></i>
                    <p class="mb-0 text-muted">{{ __('messages.cupon_nenhum_disponivel') }}</p>
                </div>
            @endforelse
        </div>

        {{-- Histórico --}}
        @if ($historico->isNotEmpty())
            <h6 class="sax-section-title mb-3">{{ __('messages.cupon_historico_uso_titulo') }}</h6>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr class="small text-uppercase text-muted">
                                <th class="ps-4">{{ __('messages.codigo_label_mini') }}</th>
                                <th>{{ __('messages.cupon_col_pedido') }}</th>
                                <th class="text-end">{{ __('messages.cupon_col_desconto') }}</th>
                                <th class="text-end pe-4">{{ __('messages.cupon_col_data') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($historico as $uso)
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $uso->cupon->codigo ?? '—' }}</td>
                                    <td class="font-monospace small">{{ $uso->order->order_number ?? '—' }}</td>
                                    <td class="text-end text-success">- {{ currency_format($uso->desconto) }}</td>
                                    <td class="text-end pe-4 small text-muted">{{ $uso->usado_em?->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
