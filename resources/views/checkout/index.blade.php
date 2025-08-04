@extends('layout.app')

@section('content')
<style>
    .step { display: none; }
    .step.active { display: block; }
    .checkout-box { border: 1px solid #ccc; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
</style>

<div class="container mt-5">
    <form id="checkoutForm">

        {{-- STEP 1 --}}
        <div class="step active" id="step1">
            <div class="checkout-box">
                <h4>Producto</h4>
                <p><strong>MOCASIN SALVATORE FERRAGAMO FINLEY NEGRO</strong></p>
                <p>Código de producto: <strong>5175802</strong></p>
                <p>Tamaño: <strong>1</strong></p>
                <p>Precio: <strong>GS$8.124.600 / U$ 1,083.28</strong></p>
                <p>Total: <strong>GS$8.124.600 / U$ 1,083.28</strong></p>
            </div>
            <button type="button" onclick="nextStep()">Seguir</button>
        </div>

        {{-- STEP 2 --}}
        <div class="step" id="step2">
            <div class="checkout-box">
                <h4>Datos personales</h4>
                <label>Nombre completo *</label>
                <input type="text" name="name" required value="eduardo luiz dahmer"><br>

                <label>Documento *</label>
                <input type="text" name="document" required value="84800007"><br>

                <label>Email *</label>
                <input type="email" name="email" required value="eduluizdahmer@gmail.com"><br>

                <label>Número de teléfono *</label>
                <input type="text" name="phone" required value="45991588886"><br>
            </div>
            <button type="button" onclick="prevStep()">Volver</button>
            <button type="button" onclick="nextStep()">Seguir</button>
        </div>

        {{-- STEP 3 --}}
        <div class="step" id="step3">
            <div class="checkout-box">
                <h4>Envío seleccionado</h4>
                <label><input type="radio" name="shipping" value="direccion" checked> Recibir en mi dirección — U$10.00</label><br>
                <label><input type="radio" name="shipping" value="nueva"> Agregar nueva dirección — U$10.00</label><br>
                <label><input type="radio" name="shipping" value="sax"> Retirar en SAX — FREE</label><br>
                <p>Dirección: rua flor de lis</p>
            </div>
            <button type="button" onclick="prevStep()">Volver</button>
            <button type="button" onclick="nextStep()">Seguir</button>
        </div>

        {{-- STEP 4 --}}
        <div class="step" id="step4">
            <div class="checkout-box">
                <h4>Método de Pago</h4>
                <label><input type="radio" name="payment" value="bancard" checked> Bancard</label><br>
                <label><input type="radio" name="payment" value="deposito"> Depósito bancario</label>

                <div id="deposit-info" style="display:none; margin-top:10px;">
                    <p><strong>BANCO SUDAMERIS</strong><br>
                    CEPAL S.A.<br>
                    RUC: 80093066-5<br>
                    CUENTA CORRIENTE Nº 0002000002934677</p>
                    <p><strong>IMPORTANTE:</strong> Indicar el número de pedido en el comprobante de su transferencia.</p>
                    <p>Después de hacer el pago, envía su comprobante para nuestro equipe de atención al cliente en este WhatsApp: <strong>+595 984 167575</strong></p>
                </div>
            </div>

            <div class="checkout-box">
                <h4>Detalles del pedido</h4>
                <p>Total del pedido: GS$8.124.600 / U$ 1,083.28</p>
                <p>Envío seleccionado: Envío Prime SAX + GS$75.000 / U$10</p>
                <p><strong>Total: US$1,093.28</strong></p>
            </div>

            <button type="button" onclick="prevStep()">Volver</button>
            <button type="submit">Finalizar</button>
        </div>

    </form>
</div>

<script>
    let currentStep = 0;
    const steps = document.querySelectorAll('.step');

    function showStep(index) {
        steps.forEach((step, i) => {
            step.classList.toggle('active', i === index);
        });
    }

    function nextStep() {
        if (validateStep(currentStep)) {
            currentStep++;
            if (currentStep < steps.length) {
                showStep(currentStep);
            }
        }
    }

    function prevStep() {
        currentStep--;
        if (currentStep >= 0) {
            showStep(currentStep);
        }
    }

    function validateStep(index) {
        const step = steps[index];
        const inputs = step.querySelectorAll('input[required]');
        for (let input of inputs) {
            if (!input.value.trim()) {
                alert('Preencha todos os campos obrigatórios!');
                input.focus();
                return false;
            }
        }
        return true;
    }

    // Show/hide depósito info
    document.querySelectorAll('input[name="payment"]').forEach(el => {
        el.addEventListener('change', () => {
            const deposito = document.querySelector('#deposit-info');
            deposito.style.display = el.value === 'deposito' ? 'block' : 'none';
        });
    });

    // Submit fake
    document.querySelector('#checkoutForm').addEventListener('submit', e => {
        e.preventDefault();
        alert('Pedido finalizado!');
    });
</script>
@endsection
