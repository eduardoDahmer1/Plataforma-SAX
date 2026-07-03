@extends('layout.dashboard')

@section('content')
    <div class="sax-wishlist-wrapper">
        <div class="dashboard-header mb-5 d-flex justify-content-between align-items-end">
            <div>
                <h2 class="sax-title text-uppercase letter-spacing-2 mb-2">Meus cupons</h2>
                <p class="text-muted mb-0">Aplique um cupom e aproveite suas melhores condições.</p>
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

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form action="{{ route('user.applyCupon') }}" method="POST" class="row g-2 align-items-center">
                    @csrf
                    <div class="col-12 col-lg-9">
                        <label for="codigo" class="sax-label mb-2 d-block">Código do cupom</label>
                        <input id="codigo" type="text" name="codigo" class="form-control sax-input" placeholder="Digite seu cupom" required>
                    </div>
                    <div class="col-12 col-lg-3">
                        <label class="d-none d-lg-block sax-label mb-2">&nbsp;</label>
                        <button type="submit" class="btn btn-dark w-100 py-2">Aplicar cupom</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="sax-section-title m-0">Cupons disponíveis</h6>
            <span class="badge bg-light text-dark border">{{ $cupons->count() }} cupom(ns)</span>
        </div>

        <div class="d-flex flex-column gap-3">
            @forelse($cupons as $userCupon)
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <div class="small text-muted text-uppercase fw-bold mb-1">Código</div>
                            <h5 class="mb-2">{{ $userCupon->cupon->codigo }}</h5>
                            <div class="text-muted small mb-1">
                                @if ($userCupon->cupon->tipo === 'percentual')
                                    {{ $userCupon->cupon->montante }}% de desconto
                                @else
                                    {{ currency($userCupon->cupon->montante) }} de desconto
                                @endif
                            </div>
                            <div class="small text-muted">Válido até {{ date('d/m/Y', strtotime($userCupon->cupon->data_final)) }}</div>
                        </div>

                        <form action="{{ route('user.applyCupon') }}" method="POST" class="w-100">
                            @csrf
                            <input type="hidden" name="codigo" value="{{ $userCupon->cupon->codigo }}">
                            <button type="submit" class="btn btn-outline-dark w-100 px-4">Usar este cupom</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state border rounded-3 bg-white py-5">
                    <i class="fas fa-ticket-alt fa-2x mb-3 opacity-50"></i>
                    <p class="mb-0 text-muted">Nenhum cupom disponível no momento.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
