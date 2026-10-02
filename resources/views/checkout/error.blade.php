@extends('layout.checkout')

@section('content')
<section class="payment-result-shell">
    <div class="container">
        <div class="payment-result-card mx-auto">
            <div class="text-center mb-4">
                <div class="result-icon error mx-auto mb-3"><i class="fa-solid fa-xmark"></i></div>
                <span class="result-label">Pagamento não concluído</span>
                <h1 class="result-title mb-2">Não foi possível processar</h1>
                <p class="result-subtitle mb-0">Revise os dados ou escolha outra forma de pagamento para tentar novamente.</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger mb-4" role="alert">{{ session('error') }}</div>
            @endif

            <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
                <a href="{{ route('checkout.index') }}" class="btn btn-dark px-4"><i class="fa-solid fa-rotate-right me-2"></i>Tentar novamente</a>
                <a href="{{ route('cart.view') }}" class="btn btn-outline-secondary px-4"><i class="fa-solid fa-bag-shopping me-2"></i>Revisar carrinho</a>
            </div>
        </div>
    </div>
</section>
@endsection
