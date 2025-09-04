<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
    
        // =========================
        // ====== STEPS ===========
        // =========================
        let currentStep = 1;
        const steps = document.querySelectorAll('.step');
    
        function showStep(step) {
            steps.forEach((s, i) => s.classList.toggle('active', i === step - 1));
            const currentStepInput = document.getElementById('currentStep');
            if (currentStepInput) currentStepInput.value = step;
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
    
        // =========================
        // ====== ENDEREÇO =========
        // =========================
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
    
        // =========================
        // ====== PAGAMENTO =======
        // =========================
        const bancardContainer = document.getElementById('bancardContainer');
        const bankDetails = document.getElementById('bankDetails');
    
        window.togglePaymentMethod = function(method) {
            if (method === 'bancard') {
                bankDetails.style.display = 'none';
                bancardContainer.style.display = 'block';
    
                setTimeout(function() {
                    if (typeof Bancard !== 'undefined' && processId) {
                        const styles = {
                            'input-background-color': '#ffffff',
                            'input-text-color': '#333333',
                            'input-border-color': '#cccccc',
                            'input-placeholder-color': '#999999',
                            'button-background-color': '#5CB85C',
                            'button-text-color': '#ffffff',
                            'button-border-color': '#4CAE4C',
                            'form-background-color': '#f9f9f9',
                            'form-border-color': '#dddddd',
                            'header-background-color': '#f5f5f5',
                            'header-text-color': '#333333',
                            'hr-border-color': '#eeeeee'
                        };
                        const options = { styles: styles };
                        Bancard.Checkout.createForm('iframe-container', processId, options);
                    } else {
                        console.error('Bancard ainda não está definido ou processId vazio.');
                    }
                }, 100);
    
            } else if (method === 'deposito') {
                bankDetails.style.display = 'block';
                bancardContainer.style.display = 'none';
            }
        };
    
        // =========================
        // ====== VALIDAÇÃO =======
        // =========================
        const checkoutForm = document.getElementById('checkoutForm');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
                if (!selectedPayment) {
                    e.preventDefault();
                    alert('Escolha um método de pagamento');
                    return false;
                }
    
                // Bloqueia envio do formulário para Bancard, só exibe iframe
                if (selectedPayment.value === 'bancard') {
                    e.preventDefault();
                    togglePaymentMethod('bancard');
                }
            });
        }
    
    });
    </script>
    