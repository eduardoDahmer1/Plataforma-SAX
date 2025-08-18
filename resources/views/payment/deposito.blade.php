@extends('layout.layout')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                    <h2 class="card-title mb-3">Obrigado pela sua compra!</h2>
                    <p class="lead mb-4">
                        Seu pedido <strong>#{{ $order->id }}</strong> foi registrado com sucesso.
                    </p>
                    <p class="mb-4">
                        Estamos aguardando a confirmação do pagamento e você receberá atualizações em breve.
                    </p>

                    <ul class="list-group list-group-flush text-start mb-4">
                        <li class="list-group-item">
                            <strong>Nome:</strong> {{ $order->user->name ?? 'Cliente' }}
                        </li>
                        <li class="list-group-item">
                            <strong>Email:</strong> {{ $order->user->email ?? '' }}
                        </li>
                        <li class="list-group-item">
                            <strong>Telefone:</strong> {{ $order->user->phone_number ?? '' }}
                        </li>
                        <li class="list-group-item">
                            <strong>Total do pedido:</strong> ${{ number_format($order->total, 2, ',', '.') }}
                        </li>
                        <li class="list-group-item">
                            <strong>Método de pagamento:</strong> {{ ucfirst($order->payment_method) }}
                        </li>
                    </ul>

                    <p class="mb-0">
                        Entraremos em contato em breve com mais informações sobre o seu pedido.
                    </p>

                    <a href="{{ route('home') }}" class="btn btn-primary mt-4">
                        Voltar à loja
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
