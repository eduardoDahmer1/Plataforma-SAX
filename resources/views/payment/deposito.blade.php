@extends('layout.layout')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            {{-- Card principal --}}
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body text-center p-5">

                    {{-- Ícone de sucesso --}}
                    <i class="fas fa-check-circle fa-5x text-success mb-4"></i>

                    {{-- Título --}}
                    <h2 class="card-title mb-3 fw-bold">Obrigado pela sua compra!</h2>

                    {{-- Mensagem personalizada --}}
                    <p class="lead mb-4 fs-5">
                        Olá <strong>{{ $order->user->name ?? 'Cliente' }}</strong>, seu pedido 
                        <strong>#{{ $order->id }}</strong> foi registrado com sucesso. 
                        Ficamos muito felizes em tê-lo conosco!
                    </p>

                    <p class="mb-4 fs-6 text-muted">
                        Nossa equipe está cuidando de tudo com muito carinho. Assim que o pagamento for confirmado, 
                        você receberá atualizações detalhadas sobre seu pedido.
                    </p>

                    {{-- Detalhes do pedido --}}
                    <ul class="list-group list-group-flush text-start mb-4 rounded">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-user me-2 text-primary"></i>Nome</span>
                            <span>{{ $order->user->name ?? 'Cliente' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-envelope me-2 text-primary"></i>Email</span>
                            <span>{{ $order->user->email ?? '' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-phone me-2 text-primary"></i>Telefone</span>
                            <span>{{ $order->user->phone_number ?? '' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-shopping-cart me-2 text-primary"></i>Total do pedido</span>
                            <span>{{ currency_format($order->total) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fa fa-credit-card me-2 text-primary"></i>Método de pagamento</span>
                            <span>{{ ucfirst($order->payment_method) }}</span>
                        </li>
                    </ul>

                    {{-- Mensagem de carinho --}}
                    <div class="alert alert-light border border-primary rounded-3 fs-6 mb-4">
                        <i class="fa fa-heart text-danger me-2"></i>
                        Querido cliente, queremos agradecer por confiar na Sax. Cada pedido é tratado com atenção e carinho, 
                        porque você é parte da nossa família. Esperamos que sua experiência seja incrível!
                    </div>

                    {{-- Botão para voltar --}}
                    <a href="{{ route('home') }}" class="btn btn-primary btn-lg rounded-pill">
                        <i class="fa fa-store me-2"></i> Voltar à loja
                    </a>

                    {{-- Info de métodos de pagamento adicionais --}}
                    <p class="mt-4 text-muted fs-7">
                        Aceitamos pagamentos via <i class="fa fa-credit-card me-1"></i> Bancard, 
                        <i class="fa fa-university me-1"></i> Depósito Bancário e 
                        <i class="fa fa-comments me-1"></i> Checkout personalizado com acompanhamento via WhatsApp.
                    </p>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- CSS opcional para deixar ainda mais elegante --}}
<style>
.card-body ul.list-group-item {
    font-size: 0.95rem;
}
.alert-light {
    background-color: #f8f9fa !important;
}
</style>
@endsection
