@extends('layout.checkout')

@section('content')
<section class="payment-result-shell">
    <div class="container">
        <div class="payment-result-card mx-auto">
            <div class="text-center mb-4">
                <div class="result-icon success mx-auto mb-3"><i class="fa-solid fa-check"></i></div>
                <span class="result-label">Pedido confirmado</span>
                <h1 class="result-title mb-2">Pagamento concluído</h1>
                <p class="result-subtitle mb-0">Seu pagamento foi processado. Você pode acompanhar cada atualização na área de pedidos.</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success mb-4" role="alert">{{ session('success') }}</div>
            @endif

            <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
                <a href="{{ route('user.orders') }}" class="btn btn-dark px-4"><i class="fa-solid fa-list me-2"></i>Ver meus pedidos</a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary px-4"><i class="fa-solid fa-store me-2"></i>Continuar comprando</a>
            </div>
        </div>
    </div>
</section>
@endsection
