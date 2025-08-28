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
                        Você será redirecionado para o pagamento via <strong>Bancard</strong> em instantes.
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
                            <span>Bancard</span>
                        </li>
                    </ul>

                    {{-- Mensagem de carinho --}}
                    <div class="alert alert-light border border-primary rounded-3 fs-6 mb-4">
                        <i class="fa fa-heart text-danger me-2"></i>
                        Querido cliente, agradecemos por confiar na Sax. Cada pedido é tratado com atenção e carinho. 
                        Esperamos que sua experiência seja incrível!
                    </div>

                    {{-- Formulário para pagamento Bancard --}}
                    <form id="payForm" action="{{ $bancardUrl }}" method="POST">
                        @csrf
                        <input type="hidden" name="public_key" value="{{ config('services.bancard.public_key') }}">
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <input type="hidden" name="amount" value="{{ $order->total }}">
                        <input type="hidden" name="token" value="{{ $token }}">
                        <button type="submit" class="btn btn-success btn-lg rounded-pill">
                            <i class="fa fa-credit-card me-2"></i> Pagar com Bancard agora
                        </button>
                    </form>

                    <p class="mt-4 fs-7 text-muted">
                        Se não for redirecionado automaticamente, clique no botão acima. 
                        Aceitamos pagamentos via <i class="fa fa-credit-card me-1"></i> Bancard, 
                        <i class="fa fa-university me-1"></i> Depósito Bancário e 
                        <i class="fa fa-comments me-1"></i> Checkout personalizado via WhatsApp.
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

{{-- Redirecionamento automático --}}
<script>
    setTimeout(function() {
        document.getElementById('payForm').submit();
    }, 3000);
</script>
@endsection
