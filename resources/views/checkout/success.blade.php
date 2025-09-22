@extends('layout.checkout')

@section('content')
<div class="container mt-5 mb-5">
    <div class="text-center">
        <h2 class="fw-bold text-success"><i class="fa fa-check-circle"></i> Pagamento concluído!</h2>
        <p class="lead">Seu pagamento foi processado com sucesso.</p>

        @if(session('success'))
            <div class="alert alert-success mt-3">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('user.orders') }}" class="btn btn-primary">
                Ver meus pedidos
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                Continuar comprando
            </a>
        </div>
    </div>
</div>
@endsection
