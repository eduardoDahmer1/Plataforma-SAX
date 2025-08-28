<script>
document.addEventListener('DOMContentLoaded', function() {
    // ====== STEPS ======
    let currentStep = 1;
    const steps = document.querySelectorAll('.step');

    function showStep(step) {
        steps.forEach((s, i) => s.classList.toggle('active', i === step - 1));
        document.getElementById('currentStep').value = step;
    }
    window.nextStep = function(step) {
        currentStep = step + 1;
        showStep(currentStep);
    }
    window.prevStep = function(step) {
        currentStep = step;
        showStep(currentStep);
    }
    showStep(currentStep);

    // ====== ENDEREÇO ======
    const shippingRadios = document.querySelectorAll('input[name="shipping"]');
    const ncAddressShipping = document.getElementById('nc_addressShipping');
    const storePickUp = document.getElementById('storePickUp');
    const countrySelect = document.getElementById('country');
    const brasilAddress = document.getElementById('brasil_address');
    const paraguaiAddress = document.getElementById('paraguai_address');

    function updateCountryFields() {
        if (!countrySelect) return;
        brasilAddress.style.display = countrySelect.value === 'brasil' ? 'block' : 'none';
        paraguaiAddress.style.display = countrySelect.value === 'paraguai' ? 'block' : 'none';
    }

    function updateShippingDisplay() {
        const checkedRadio = document.querySelector('input[name="shipping"]:checked');
        if (!checkedRadio) return;
        const value = checkedRadio.value;
        ncAddressShipping.style.display = value === '2' ? 'block' : 'none';
        storePickUp.style.display = value === '3' ? 'block' : 'none';
        if (value === '2') updateCountryFields();
        else {
            brasilAddress.style.display = 'none';
            paraguaiAddress.style.display = 'none';
        }
    }

    shippingRadios.forEach(radio => radio.addEventListener('change', updateShippingDisplay));
    if (countrySelect) countrySelect.addEventListener('change', updateCountryFields);
    updateShippingDisplay();

    // ====== PAGAMENTO ======
    const bancardModal = new bootstrap.Modal(document.getElementById('bancardModal'));
    window.togglePaymentMethod = function(method) {
        const bankDetails = document.getElementById('bankDetails');

        if (method === 'bancard') {
            bankDetails.style.display = 'none';
            bancardModal.show(); // abrir modal
        } else if (method === 'deposito') {
            bankDetails.style.display = 'block';
            bancardModal.hide(); // fechar modal
        }
    }

    // ====== VALIDAÇÃO SIMPLES NO FRONT ======
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        if (!selectedPayment) {
            e.preventDefault();
            alert('Escolha um método de pagamento');
            return false;
        }
    });
});
</script>