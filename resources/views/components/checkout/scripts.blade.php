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
        const shippingRegistered = document.getElementById('shipping_registered');
        const shippingAlternative = document.getElementById('shipping_alternative');
        const shippingStore = document.getElementById('shipping_store');
        const countrySelect = document.getElementById('country');
        const brasilAddress = document.getElementById('brasil_address');
        const paraguaiAddress = document.getElementById('paraguai_address');
        const storeSelect = document.getElementById('storeSelect');
        const storeMaps = document.querySelectorAll('.store-map');
        const observationsInput = document.getElementById('observations');
    
        // Guarda valores iniciais
        let observationsValues = {
            1: observationsInput?.value || '',
            2: observationsInput?.value || '',
            3: observationsInput?.value || ''
        };
    
        window.toggleShippingFields = function(value) {
            shippingRegistered.style.display = value == 1 ? 'block' : 'none';
            shippingAlternative.style.display = value == 2 ? 'block' : 'none';
            shippingStore.style.display = value == 3 ? 'block' : 'none';
    
            if (value == 2) updateCountryFields();
            else {
                brasilAddress.style.display = 'none';
                paraguaiAddress.style.display = 'none';
            }
    
            // Atualiza campo de observations
            if (observationsInput) {
                observationsInput.value = observationsValues[value] || '';
            }
        }
    
        function updateCountryFields() {
            if (!countrySelect) return;
            brasilAddress.style.display = countrySelect.value === 'brasil' ? 'block' : 'none';
            paraguaiAddress.style.display = countrySelect.value === 'paraguai' ? 'block' : 'none';
        }
    
        // Atualiza valor do observations ao digitar
        if (observationsInput) {
            observationsInput.addEventListener('input', function() {
                const checked = document.querySelector('input[name="shipping"]:checked');
                if (checked) observationsValues[checked.value] = this.value;
            });
        }
    
        shippingRadios.forEach(radio => radio.addEventListener('change', function() {
            toggleShippingFields(this.value);
        }));
    
        if (countrySelect) countrySelect.addEventListener('change', updateCountryFields);
    
        // Mapas da loja
        if (storeSelect) {
            storeSelect.addEventListener('change', function() {
                storeMaps.forEach(map => map.style.display = map.dataset.store === this.value ? 'block' : 'none');
            });
        }
    
        // Inicializa display correto
        const checkedRadio = document.querySelector('input[name="shipping"]:checked');
        if (checkedRadio) toggleShippingFields(checkedRadio.value);
    
        // =========================
        // ====== PAGAMENTO =======
        // =========================
        window.selectPayment = function(method) {
            const paymentInput = document.getElementById('payment_method');
            if(paymentInput) paymentInput.value = method;
    
            document.getElementById('btn-deposito')?.classList.remove('active');
            document.getElementById('btn-bancard')?.classList.remove('active');
            document.getElementById('btn-' + method)?.classList.add('active');
        };
    
        // =========================
        // ====== VALIDAÇÃO =======
        // =========================
        const checkoutForm = document.getElementById('checkoutForm');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                const selectedMethod = document.getElementById('payment_method')?.value;
                if (!selectedMethod) {
                    e.preventDefault();
                    alert('Escolha um método de pagamento');
                    return false;
                }
            });
        }
    
    });
    </script>
    