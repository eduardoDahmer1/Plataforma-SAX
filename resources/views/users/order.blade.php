@extends('layout.layout')

@section('content')
    <div class="row">
        <div class="col-md-3 mb-4">
            @include('users.components.menu')
        </div>

        <div class="col-md-9">
            <div class="row mb-4">
                {{-- Detalhes do Pedido --}}
                <div class="col-md-6 mb-3">
                    <h2 class="mb-3"><i class="fas fa-receipt me-2"></i>Detalhes do Pedido #{{ $order->id }}</h2>

                    {{-- Status --}}
                    <p>
                        <i class="fas fa-info-circle me-1"></i>Status:
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
                        <i class="fas fa-credit-card me-1"></i>Método de Pagamento:
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
                    <p><i class="fas fa-dollar-sign me-1"></i>Total:
                        {{ currency_format($order->items->sum(fn($i) => $i->price * $i->quantity)) }}</p>

                    {{-- Comprovante --}}
                    @if ($order->deposit_receipt)
                        <div class="mb-3">
                            <h5><i class="fas fa-file-image me-1"></i>Comprovante de Depósito</h5>
                            <a href="{{ asset('storage/' . $order->deposit_receipt) }}" target="_blank">
                                <img src="{{ asset('storage/' . $order->deposit_receipt) }}" alt="Comprovante"
                                    class="img-fluid border rounded" style="max-width: 250px;">
                            </a>
                        </div>
                    @else
                        {{-- Upload do Comprovante --}}
                        <div class="card mb-3 shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fa fa-file-upload"></i> Envio do Comprovante</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('orders.deposit.submit', $order->id) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="deposit_receipt" class="form-label">Envie o comprovante de
                                            depósito</label>
                                        <input type="file" name="deposit_receipt" id="deposit_receipt"
                                            class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100 mt-3">
                                        <i class="fa fa-check-circle"></i> Enviar Comprovante
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Dados Pessoais e Endereço --}}
                <div class="col-md-6 mb-3">
                    <h3 class="mb-3"><i class="fas fa-user me-2"></i>Dados Pessoais</h3>
                    <p><strong>Nome:</strong> {{ $order->name ?? ($order->user->name ?? '-') }}</p>
                    <p><strong>Documento:</strong> {{ $order->document ?? ($order->user->document ?? '-') }}</p>
                    <p><strong>Email:</strong> {{ $order->email ?? ($order->user->email ?? '-') }}</p>
                    <p><strong>Telefone:</strong> {{ $order->phone ?? ($order->user->phone_number ?? '-') }}</p>

                    {{-- Endereço de Envio --}}
                    <h3 class="mt-4 mb-3"><i class="fas fa-map-marker-alt me-2"></i>Endereço de Envio</h3>
                    @if ($order->shipping == 1)
                        {{-- Endereço cadastrado --}}
                        <p>
                            {{ $order->street ?? ($order->user->street ?? '-') }},
                            Nº {{ $order->number ?? ($order->user->number ?? '-') }},
                            CEP: {{ $order->cep ?? ($order->user->cep ?? '-') }}<br>
                            {{ $order->city ?? ($order->user->city ?? '-') }},
                            {{ $order->state ?? ($order->user->state ?? '-') }}<br>
                            {{ $order->country ?? ($order->user->country ?? '-') }}
                        </p>
                        @if ($order->observations)
                            <p><strong>Observações:</strong> {{ $order->observations }}</p>
                        @endif
                    @elseif($order->shipping == 2)
                        {{-- Endereço alternativo --}}
                        <p>
                            {{ $order->street ?? '-' }},
                            Nº {{ $order->number ?? '-' }},
                            CEP: {{ $order->cep ?? '-' }}<br>
                            {{ $order->city ?? '-' }},
                            {{ $order->state ?? '-' }}<br>
                            {{ $order->country ?? '-' }}
                        </p>
                        @if ($order->observations)
                            <p><strong>Observações:</strong> {{ $order->observations }}</p>
                        @endif
                    @elseif($order->shipping == 3)
                        {{-- Retirada na loja --}}
                        <p>
                            Retirar na loja: {{ $order->store == 1 ? 'SAX Ciudad del Este' : 'SAX Assunção' }}
                        </p>
                        @if ($order->observations)
                            <p><strong>Observações:</strong> {{ $order->observations }}</p>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Itens do pedido --}}
            <h3 class="mb-3"><i class="fas fa-boxes me-2"></i>Itens</h3>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->product->external_name ?? ($item->product->name ?? 'Produto') }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ currency_format($item->price) }}</td>
                                <td>{{ currency_format($item->price * $item->quantity) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <a href="{{ route('user.dashboard') }}" class="btn btn-secondary mt-3">
                <i class="fas fa-arrow-left me-1"></i>Voltar
            </a>

            {{-- Contato via WhatsApp --}}
            <div class="mt-4">
                <p>
                    <strong>Precisa de ajuda?</strong><br>
                    <a href="https://wa.me/{{ env('WHATSAPP_NUMBER') }}?text={{ urlencode('Olá, você visitou nosso site. Precisa de ajuda com algo?') }}"
                        target="_blank" class="text-success fw-bold">
                        Clique aqui para falar no WhatsApp
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection
