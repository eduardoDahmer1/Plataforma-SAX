document.addEventListener('DOMContentLoaded', function () {
    const formControllers = new Map();

    function createAddressFormController(form) {
        const country = form.querySelector('[data-address-country]');
        const postalCode = form.querySelector('[data-address-postal-code]');
        const postalLabel = form.querySelector('[data-address-postal-label]');
        const stateLabel = form.querySelector('[data-address-state-label]');
        const state = form.querySelector('[data-address-state]');
        const city = form.querySelector('[data-address-city]');
        const street = form.querySelector('[data-address-street]');

        if (!country || !state || !city) return null;

        let paraguayData = null;
        let selectedState = state.dataset.selected || '';
        let selectedCity = city.dataset.selected || '';
        let loadSequence = 0;

        function setField(name, value) {
            const field = form.elements.namedItem(name);
            if (field) field.value = value ?? '';
        }

        function selectSavedCity() {
            if (!selectedCity) return;
            const option = Array.from(city.options).find(function (item) {
                return item.value.toLocaleLowerCase() === selectedCity.toLocaleLowerCase();
            });
            if (option) city.value = option.value;
        }

        function loadBrazilCities(stateId, sequence) {
            city.disabled = true;
            city.innerHTML = '<option value="">Carregando cidades...</option>';

            fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${stateId}/municipios`)
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(cities => {
                    if (sequence !== loadSequence || country.value !== 'brasil') return;
                    city.innerHTML = '<option value="">Selecione a cidade</option>';
                    cities.forEach(item => city.add(new Option(item.nome, item.nome)));
                    city.disabled = false;
                    selectSavedCity();
                })
                .catch(() => {
                    if (sequence !== loadSequence) return;
                    city.innerHTML = '<option value="">Não foi possível carregar as cidades</option>';
                });
        }

        function loadBrazilStates(sequence) {
            state.innerHTML = '<option value="">Carregando estados...</option>';
            city.innerHTML = '<option value="">Selecione o estado primeiro...</option>';
            city.disabled = true;

            fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome')
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(states => {
                    if (sequence !== loadSequence || country.value !== 'brasil') return;
                    state.innerHTML = '<option value="">Selecione o estado</option>';
                    states.forEach(item => {
                        const option = new Option(item.nome, item.sigla);
                        option.dataset.id = item.id;
                        state.add(option);
                    });

                    if (selectedState) {
                        state.value = selectedState;
                        state.dispatchEvent(new Event('change'));
                    }
                })
                .catch(() => {
                    if (sequence !== loadSequence) return;
                    state.innerHTML = '<option value="">Não foi possível carregar os estados</option>';
                });
        }

        function loadParaguay(sequence) {
            state.innerHTML = '<option value="">Carregando departamentos...</option>';
            city.innerHTML = '<option value="">Selecione o departamento primeiro...</option>';
            city.disabled = true;

            fetch('/data/py.json')
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(data => {
                    if (sequence !== loadSequence || country.value !== 'paraguai') return;
                    paraguayData = data;
                    const departments = [...new Set(data.map(item => item.admin_name))].sort();
                    state.innerHTML = '<option value="">Selecione o departamento</option>';
                    departments.forEach(item => state.add(new Option(item, item)));

                    if (selectedState) {
                        state.value = selectedState;
                        state.dispatchEvent(new Event('change'));
                    }
                })
                .catch(() => {
                    if (sequence !== loadSequence) return;
                    state.innerHTML = '<option value="">Não foi possível carregar os departamentos</option>';
                });
        }

        function updateCountry(preserveSelection) {
            if (!preserveSelection) {
                selectedState = '';
                selectedCity = '';
            }

            const sequence = ++loadSequence;
            const isBrazil = country.value === 'brasil';
            postalLabel.textContent = isBrazil ? 'CEP' : 'Código postal';
            stateLabel.textContent = isBrazil ? 'Estado' : 'Departamento';
            if (postalCode) {
                postalCode.placeholder = isBrazil ? '00000-000' : 'Ex.: 1234';
                postalCode.required = isBrazil;
            }

            if (isBrazil) loadBrazilStates(sequence);
            else loadParaguay(sequence);
        }

        state.addEventListener('change', function () {
            if (!state.value) {
                city.disabled = true;
                city.innerHTML = '<option value="">Selecione o estado primeiro...</option>';
                return;
            }

            const sequence = loadSequence;
            if (country.value === 'brasil') {
                const stateId = state.options[state.selectedIndex]?.dataset.id;
                if (stateId) loadBrazilCities(stateId, sequence);
                return;
            }

            city.innerHTML = '<option value="">Selecione a cidade</option>';
            (paraguayData || [])
                .filter(item => item.admin_name === state.value)
                .sort((a, b) => a.city.localeCompare(b.city))
                .forEach(item => city.add(new Option(item.city, item.city)));
            city.disabled = false;
            selectSavedCity();
        });

        country.addEventListener('change', function () {
            updateCountry(false);
        });

        postalCode?.addEventListener('input', function () {
            if (country.value !== 'brasil') return;
            const value = postalCode.value.replace(/\D/g, '').slice(0, 8);
            postalCode.value = value.length > 5 ? `${value.slice(0, 5)}-${value.slice(5)}` : value;
        });

        postalCode?.addEventListener('blur', function () {
            const cep = postalCode.value.replace(/\D/g, '');
            if (country.value !== 'brasil' || cep.length !== 8) return;

            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(data => {
                    if (data.erro) return;
                    if (street && data.logradouro) street.value = data.logradouro;
                    selectedState = data.uf;
                    selectedCity = data.localidade;
                    state.value = data.uf;
                    state.dispatchEvent(new Event('change'));
                })
                .catch(() => {});
        });

        function setAddress(address) {
            setField('editing_address_id', address.id);
            setField('label', address.label);
            setField('postal_code', address.postal_code);
            setField('street', address.street);
            setField('number', address.number);
            setField('district', address.district);
            setField('complement', address.complement);

            selectedState = address.state || '';
            selectedCity = address.city || '';
            country.value = address.country === 'paraguai' ? 'paraguai' : 'brasil';

            const defaultCheckbox = form.querySelector('[data-address-default]');
            const defaultHelp = form.querySelector('[data-default-help]');
            if (defaultCheckbox) {
                defaultCheckbox.checked = Boolean(address.is_default);
                defaultCheckbox.disabled = Boolean(address.is_default);
            }
            defaultHelp?.classList.toggle('d-none', !address.is_default);

            updateCountry(true);
        }

        updateCountry(true);

        return { setAddress };
    }

    document.querySelectorAll('[data-address-form]').forEach(function (form) {
        const controller = createAddressFormController(form);
        if (controller) formControllers.set(form, controller);
    });

    const editModalElement = document.getElementById('editAddressModal');
    const editForm = document.querySelector('[data-address-edit-form]');
    const editController = editForm ? formControllers.get(editForm) : null;
    const editModal = editModalElement && window.bootstrap
        ? bootstrap.Modal.getOrCreateInstance(editModalElement)
        : null;

    function openEditor(button, overrideAddress) {
        if (!editForm || !editController || !editModal) return;

        let address;
        try {
            const storedAddress = JSON.parse(button.dataset.address || '{}');
            address = overrideAddress
                ? {
                    ...storedAddress,
                    ...overrideAddress,
                    is_default: Boolean(storedAddress.is_default || overrideAddress.is_default)
                }
                : storedAddress;
        } catch (_) {
            return;
        }

        editForm.action = button.dataset.action;
        editController.setAddress(address);
        editModal.show();
    }

    document.querySelectorAll('[data-address-edit]').forEach(function (button) {
        button.addEventListener('click', function () {
            openEditor(button);
        });
    });

    const failedEdit = document.getElementById('failed-address-edit');
    if (failedEdit) {
        const button = Array.from(document.querySelectorAll('[data-address-edit]')).find(function (candidate) {
            try {
                return String(JSON.parse(candidate.dataset.address || '{}').id) === String(failedEdit.dataset.addressId);
            } catch (_) {
                return false;
            }
        });
        if (button) {
            try {
                openEditor(button, JSON.parse(failedEdit.dataset.address || '{}'));
            } catch (_) {}
        }
    }
});
