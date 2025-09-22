@extends('layout.checkout')

@section('content')
<div class="container mt-5 mb-5">
    <div class="text-center">
        <h2 class="fw-bold text-danger"><i class="fa fa-times-circle"></i> Ocorreu um erro no pagamento</h2>
        <p class="lead">Infelizmente, seu pagamento não pôde ser processado.</p>

        @if(session('error'))
            <div class="alert alert-danger mt-3">
                {{ session('error') }}
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('checkout.index') }}" class="btn btn-primary">
                Tentar novamente
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                Voltar à loja
            </a>
        </div>
    </div>
</div>
@endsection
