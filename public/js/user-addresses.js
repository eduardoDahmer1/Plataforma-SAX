document.addEventListener('DOMContentLoaded', function () {
    const country = document.getElementById('address-country');
    const postalCode = document.getElementById('address-postal-code');
    const postalLabel = document.getElementById('address-postal-label');
    const stateLabel = document.getElementById('address-state-label');
    const state = document.getElementById('address-state');
    const city = document.getElementById('address-city');
    const street = document.getElementById('address-street');

    if (!country || !state || !city) return;

    let paraguayData = null;
    const selectedState = state.dataset.selected || '';
    const selectedCity = city.dataset.selected || '';

    function selectSavedCity() {
        if (!selectedCity) return;
        const option = Array.from(city.options).find(function (item) {
            return item.value.toLocaleLowerCase() === selectedCity.toLocaleLowerCase();
        });
        if (option) city.value = option.value;
    }

    function loadBrazilCities(stateId) {
        city.disabled = true;
        city.innerHTML = '<option value="">Carregando cidades...</option>';

        fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${stateId}/municipios`)
            .then(response => response.ok ? response.json() : Promise.reject())
            .then(cities => {
                city.innerHTML = '<option value="">Selecione a cidade</option>';
                cities.forEach(item => city.add(new Option(item.nome, item.nome)));
                city.disabled = false;
                selectSavedCity();
            })
            .catch(() => {
                city.innerHTML = '<option value="">Não foi possível carregar as cidades</option>';
            });
    }

    function loadBrazilStates() {
        state.innerHTML = '<option value="">Carregando estados...</option>';
        city.innerHTML = '<option value="">Selecione o estado primeiro...</option>';
        city.disabled = true;

        fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome')
            .then(response => response.ok ? response.json() : Promise.reject())
            .then(states => {
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
                state.innerHTML = '<option value="">Não foi possível carregar os estados</option>';
            });
    }

    function loadParaguay() {
        state.innerHTML = '<option value="">Carregando departamentos...</option>';
        city.innerHTML = '<option value="">Selecione o departamento primeiro...</option>';
        city.disabled = true;

        fetch('/data/py.json')
            .then(response => response.ok ? response.json() : Promise.reject())
            .then(data => {
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
                state.innerHTML = '<option value="">Não foi possível carregar os departamentos</option>';
            });
    }

    function updateCountry() {
        const isBrazil = country.value === 'brasil';
        postalLabel.textContent = isBrazil ? 'CEP' : 'Código postal';
        stateLabel.textContent = isBrazil ? 'Estado' : 'Departamento';
        postalCode.placeholder = isBrazil ? '00000-000' : 'Ex.: 1234';

        if (isBrazil) loadBrazilStates();
        else loadParaguay();
    }

    state.addEventListener('change', function () {
        if (!state.value) {
            city.disabled = true;
            city.innerHTML = '<option value="">Selecione o estado primeiro...</option>';
            return;
        }

        if (country.value === 'brasil') {
            const stateId = state.options[state.selectedIndex]?.dataset.id;
            if (stateId) loadBrazilCities(stateId);
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

    country.addEventListener('change', updateCountry);

    postalCode?.addEventListener('input', function () {
        if (country.value !== 'brasil') return;
        const digits = postalCode.value.replace(/\D/g, '').slice(0, 8);
        postalCode.value = digits.length > 5 ? `${digits.slice(0, 5)}-${digits.slice(5)}` : digits;
    });

    postalCode?.addEventListener('blur', function () {
        const cep = postalCode.value.replace(/\D/g, '');
        if (country.value !== 'brasil' || cep.length !== 8) return;

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.ok ? response.json() : Promise.reject())
            .then(data => {
                if (data.erro) return;
                if (street && data.logradouro) street.value = data.logradouro;
                state.value = data.uf;
                state.dispatchEvent(new Event('change'));
                setTimeout(function () {
                    city.value = data.localidade;
                }, 700);
            })
            .catch(() => {});
    });

    updateCountry();
});
