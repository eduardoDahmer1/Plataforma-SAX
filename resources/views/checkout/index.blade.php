@extends('layout.layout')

@section('content')
<style>
    .step { display: none; }
    .step.active { display: block; }
    .checkout-box { border: 1px solid #ccc; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    .payment-details .card {
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .payment-details .card .card-body {
        padding: 15px;
    }

    .payment-details .card .card-title {
        font-size: 1.2em;
        margin-bottom: 10px;
    }

    .payment-details .card .card-text {
        font-size: 1em;
        margin-bottom: 10px;
    }

    .payment-details .card .btn {
        width: 100%;
    }

    .form-control {
        margin-bottom: 15px;
    }

    .upload-btn {
        display: flex;
        flex-direction: column;
    }

    .upload-btn input[type="file"] {
        margin-top: 10px;
    }
</style>

<div class="container mt-5">
    <form id="checkoutForm" method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="step" id="currentStep">

        {{-- STEP 1 --}}
        <div class="step active" id="step1">
            <div class="checkout-box">
                <h4>Itens no Carrinho</h4>
                
                @php
                    $totalCarrinho = 0; // Inicializa a variável para somar o total
                @endphp

                @foreach ($cart as $item)
                    <div class="cart-item">
                        <!-- Exibe o nome do produto, utilizando name, external_name ou 'Produto' -->
                        <p><strong>{{ $item['title'] ?? 'Produto' }}</strong></p> <!-- Aqui usa 'title' em vez de 'slug' -->

                        <p>Preço: R$ {{ number_format($item['price'], 2, ',', '.') }}</p>
                        <p>Quantidade: {{ $item['quantity'] }}</p>
                        <p>Total: R$ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</p>
                    </div>

                    @php
                        // Soma o total de cada item ao total do carrinho
                        $totalCarrinho += $item['price'] * $item['quantity'];
                    @endphp
                @endforeach

                <div class="total-carrinho">
                    <h4>Total do Carrinho: R$ {{ number_format($totalCarrinho, 2, ',', '.') }}</h4>
                </div>
                <button type="button" class="btn btn-primary" onclick="nextStep(1)">Seguir</button>
            </div>
        </div>

        {{-- STEP 2 --}}
        <div class="step" id="step2">
            <div class="checkout-box">
                <h4>Dados Pessoais</h4>
                <label>Nome Completo *</label>
                <input type="text" name="name" class="form-control" required><br>
                <label>Documento *</label>
                <input type="text" name="document" class="form-control" required><br>
                <label>Email *</label>
                <input type="email" name="email" class="form-control" required><br>
                <label>Telefone *</label>
                <input type="text" name="phone" class="form-control" required><br>
            </div>
            <button type="button" class="btn btn-secondary" onclick="prevStep(1)">Voltar</button>
            <button type="button" class="btn btn-primary" onclick="nextStep(2)">Seguir</button>
        </div>

        {{-- STEP 3 --}}
        <div class="step" id="step3">
            <div class="checkout-box">
                <h4>Endereço de Envio</h4>

                <label><input type="radio" name="shipping" value="1" checked> Enviar para o Endereço Cadastrado</label><br>
                <label><input type="radio" name="shipping" value="2"> Endereço Alternativo</label><br>
                <label><input type="radio" name="shipping" value="3"> Retirar na Loja</label><br>

                <!-- Endereço Cadastrado -->
                <div id="nc_address" style="display:none;">
                    <p>Endereço Cadastrado: <strong>Rua Exemplo, 123, Cidade, País</strong></p>
                </div>

                <!-- Endereço Alternativo -->
                <div id="nc_addressShipping" style="display:none;">
                    <label>Escolha o País *</label>
                    <select name="country" id="country" class="form-control" required>
                        <option value="brasil">Brasil</option>
                        <option value="paraguai">Paraguai</option>
                    </select><br>

                    <div id="brasil_address" style="display:none;">
                        <label>CEP *</label>
                        <input type="text" name="cep" class="form-control" placeholder="CEP" required><br>
                        <label>Rua *</label>
                        <input type="text" name="street" class="form-control" placeholder="Rua" required><br>
                        <label>Número *</label>
                        <input type="text" name="number" class="form-control" placeholder="Número da casa" required><br>
                        <label>Observações</label>
                        <input type="text" name="observations" class="form-control" placeholder="Observações"><br>
                    </div>

                    <div id="paraguai_address" style="display:none;">
                        <label>Código da Casa *</label>
                        <input type="text" name="house_code" class="form-control" placeholder="Código da casa" required><br>
                        <label>Observações</label>
                        <input type="text" name="observations" class="form-control" placeholder="Observações"><br>
                    </div>
                </div>

                <!-- Retirar na Loja -->
                <div id="storePickUp" style="display:none;">
                    <p>Escolha a Loja para Retirar:</p>
                    <select name="store" class="form-control">
                        <option value="1">SAX Ciudad del Este</option>
                        <option value="2">SAX Assunção</option>
                    </select>
                </div>

                <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Voltar</button>
                <button type="button" class="btn btn-primary" onclick="nextStep(3)">Seguir</button>
            </div>
        </div>

        {{-- STEP 4 (Modal for Bancard) --}}
        <div class="step" id="step4">
            <div class="checkout-box">
                <h4>Método de Pagamento</h4>
                <!-- Método de pagamento com Depósito Bancário por padrão -->
                <label><input type="radio" name="payment_method" value="deposito" checked onclick="togglePaymentMethod('deposito')"> Depósito bancário</label><br>
                <label><input type="radio" name="payment_method" value="bancard" onclick="togglePaymentMethod('bancard')"> Bancard</label>
            </div>

            {{-- Bancard Modal --}}
            <div class="modal fade" id="bancardModal" tabindex="-1" aria-labelledby="bancardModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bancardModalLabel">Escolha o Método de Pagamento</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <label><input type="radio" name="payment_type" value="card" checked onclick="togglePaymentForm('card')"> Cartão de Crédito</label><br>
                            <label><input type="radio" name="payment_type" value="qr_code" onclick="togglePaymentForm('qr_code')"> QR Code</label><br>

                            <!-- Cartão de Crédito -->
                            <div id="cardPayment" style="display:block;">
                                <label>Número do Cartão</label>
                                <input type="text" name="card_number" class="form-control" placeholder="Número do Cartão"><br>
                                <label>Data de Vencimento</label>
                                <input type="text" name="expiry_date" class="form-control" placeholder="MM/AA"><br>
                                <label>Código de Segurança</label>
                                <input type="text" name="cvv" class="form-control" placeholder="CVV"><br>
                            </div>

                            <!-- QR Code -->
                            <div id="qrCodePayment" style="display:none;">
                                <label>QR Code Link</label>
                                <input type="text" name="qr_code" class="form-control" placeholder="QR Code Link"><br>
                                <p>Para gerar um QR Code, você pode usar um serviço como <a href="https://www.qr-code-generator.com/" target="_blank">QR Code Generator</a> e gerar o link do pagamento.</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Finalizar Compra</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Depósito Bancário --}}
            <div id="bankDetails" class="payment-details" style="display:none;">
                <h5>Escolha o Banco para Depósito</h5>
                <div class="row">
                    @foreach ($paymentMethods as $method)
                        @if ($method->type === 'bank')
                            <div class="col-12 col-sm-6 col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $method->name }}</h6>
                                        <p class="card-text">{{ $method->bank_details }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="upload-btn">
                    <label>Envie o Comprovante de Depósito (se disponível)</label>
                    <input type="file" name="deposit_receipt" class="form-control"><br>
                    <p>Ou entre em contato para enviar o comprovante.</p>
                </div>
            </div>

            @php
                $totalPedido = 0; // Inicializa a variável para somar o total do pedido
            @endphp

            <div class="checkout-box">
                <h4>Resumo do Pedido</h4>

                @foreach ($cart as $item)
                    <p><strong>{{ $item['slug'] ?? 'Produto' }}</strong></p>
                    <p>Preço: R$ {{ number_format($item['price'], 2, ',', '.') }}</p>
                    <p>Quantidade: {{ $item['quantity'] }}</p>
                    <p>Total: R$ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</p>

                    @php
                        $totalPedido += $item['price'] * $item['quantity']; // Soma o total de cada item ao total do pedido
                    @endphp
                @endforeach

                <!-- Exibe o total geral do pedido -->
                <hr>
                <h5>Total do Pedido: R$ {{ number_format($totalPedido, 2, ',', '.') }}</h5>
            </div>


            <button type="button" class="btn btn-secondary" onclick="prevStep(3)">Voltar</button>
            <button type="submit" class="btn btn-success">Finalizar Compra</button>
        </div>
    </form>
</div>

<script>
    let currentStep = 1;
    const steps = document.querySelectorAll('.step');

    function showStep(step) {
        steps.forEach((s, i) => {
            s.classList.toggle('active', i === step - 1);
        });
        document.getElementById('currentStep').value = step;
    }

    function nextStep(step) {
        currentStep = step + 1;
        showStep(currentStep);
    }

    function prevStep(step) {
        currentStep = step;
        showStep(currentStep);
    }

    function togglePaymentMethod(method) {
        if (method === 'bancard') {
            $('#bancardModal').modal('show');
            document.getElementById('bankDetails').style.display = 'none';
        } else if (method === 'deposito') {
            document.getElementById('bancardModal').style.display = 'none';
            document.getElementById('bankDetails').style.display = 'block';
        }
    }

    function togglePaymentForm(type) {
        if (type === 'card') {
            document.getElementById('cardPayment').style.display = 'block';
            document.getElementById('qrCodePayment').style.display = 'none';
        } else if (type === 'qr_code') {
            document.getElementById('cardPayment').style.display = 'none';
            document.getElementById('qrCodePayment').style.display = 'block';
        }
    }

    showStep(currentStep);
</script>

@endsection
