@extends('layout.dashboard')

@section('content')
    <div class="row mb-4">
        {{-- Detalhes do Pedido --}}
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-3"><i class="fas fa-receipt me-2"></i>Pedido #{{ $order->id }}</h4>

                    {{-- Status --}}
                    <p>
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Status:</strong>
                        <span
                            class="badge
                                @switch($order->status)
                                    @case('pending') bg-warning text-dark @break
                                    @case('processing') bg-info text-dark @break
                                    @case('completed') bg-success @break
                                    @case('canceled') bg-danger @break
                                    @default bg-secondary
                                @endswitch">
                            {{ ucfirst($order->status) }}
                        </span>
                    </p>

                    {{-- Método de Pagamento --}}
                    <p>
                        <i class="fas fa-credit-card me-1"></i>
                        <strong>Pagamento:</strong>
                        <span
                            class="badge
                                @switch($order->payment_method)
                                    @case('whatsapp') bg-success @break
                                    @case('bancard') bg-primary @break
                                    @case('deposito') bg-warning text-dark @break
                                    @default bg-secondary
                                @endswitch">
                            {{ ucfirst($order->payment_method) }}
                        </span>
                    </p>

                    {{-- Total --}}
                    <p>
                        <i class="fas fa-dollar-sign me-1"></i>
                        <strong>Total:</strong>
                        {{ currency_format($order->items->sum(fn($i) => $i->price * $i->quantity)) }}
                    </p>

                    {{-- Comprovante --}}
                    @if ($order->deposit_receipt)
                        <div class="mb-3">
                            <h6><i class="fas fa-file-image me-1"></i>Comprovante</h6>
                            <a href="{{ asset('storage/' . $order->deposit_receipt) }}" target="_blank">
                                <img src="{{ asset('storage/' . $order->deposit_receipt) }}" alt="Comprovante"
                                    class="img-fluid border rounded" style="max-width: 250px;">
                            </a>
                        </div>
                    @else
                        {{-- Upload do Comprovante --}}
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fa fa-file-upload"></i> Envio do Comprovante</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('orders.deposit.submit', $order->id) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="deposit_receipt" class="form-label">Envie o comprovante</label>
                                        <input type="file" name="deposit_receipt" id="deposit_receipt"
                                            class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100 mt-2">
                                        <i class="fa fa-check-circle"></i> Enviar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Dados Pessoais e Endereço --}}
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-3"><i class="fas fa-user me-2"></i>Dados Pessoais</h4>
                    <p><strong>Nome:</strong> {{ $order->name ?? ($order->user->name ?? '-') }}</p>
                    <p><strong>Documento:</strong> {{ $order->document ?? ($order->user->document ?? '-') }}</p>
                    <p><strong>Email:</strong> {{ $order->email ?? ($order->user->email ?? '-') }}</p>
                    <p><strong>Telefone:</strong> {{ $order->phone ?? ($order->user->phone_number ?? '-') }}</p>

                    {{-- Endereço de Envio --}}
                    <h4 class="mt-4 mb-3"><i class="fas fa-map-marker-alt me-2"></i>Endereço</h4>
                    @if ($order->shipping == 1)
                        <p>
                            {{ $order->street ?? ($order->user->street ?? '-') }},
                            Nº {{ $order->number ?? ($order->user->number ?? '-') }},
                            CEP: {{ $order->cep ?? ($order->user->cep ?? '-') }}<br>
                            {{ $order->city ?? ($order->user->city ?? '-') }},
                            {{ $order->state ?? ($order->user->state ?? '-') }}<br>
                            {{ $order->country ?? ($order->user->country ?? '-') }}
                        </p>
                    @elseif($order->shipping == 2)
                        <p>
                            {{ $order->street ?? '-' }},
                            Nº {{ $order->number ?? '-' }},
                            CEP: {{ $order->cep ?? '-' }}<br>
                            {{ $order->city ?? '-' }},
                            {{ $order->state ?? '-' }}<br>
                            {{ $order->country ?? '-' }}
                        </p>
                    @elseif($order->shipping == 3)
                        <p>Retirar na loja: {{ $order->store == 1 ? 'SAX Ciudad del Este' : 'SAX Assunção' }}</p>
                    @endif

                    @if ($order->observations)
                        <p><strong>Observações:</strong> {{ $order->observations }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Itens do pedido --}}
    <h3 class="mb-3"><i class="fas fa-boxes me-2"></i>Itens</h3>
    <div class="row g-3">
        @foreach ($order->items as $item)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title">{{ $item->product->external_name ?? ($item->product->name ?? 'Produto') }}
                        </h6>
                        <p><strong>Qtd:</strong> {{ $item->quantity }}</p>
                        <p><strong>Preço:</strong> {{ currency_format($item->price) }}</p>
                        <p><strong>Subtotal:</strong> {{ currency_format($item->price * $item->quantity) }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <a href="{{ route('user.dashboard') }}" class="btn btn-secondary mt-4">
        <i class="fas fa-arrow-left me-1"></i>Voltar
    </a>

    {{-- Contato via WhatsApp --}}
    <div class="mt-4">
        <p>
            <strong>Precisa de ajuda?</strong><br>
            <a href="https://wa.me/{{ env('WHATSAPP_NUMBER') }}?text={{ urlencode('Olá, estou tentando finalizar uma compra na web, poderia me ajudar por favor?') }}"
                target="_blank" class="btn btn-success">
                <i class="fab fa-whatsapp me-1"></i> Falar no WhatsApp
            </a>
        </p>
    </div>
@endsection
