(function () {
    'use strict';

    let countriesPromise;

    function countries() {
        if (!countriesPromise) {
            countriesPromise = fetch('/api/locations/countries', {
                headers: { Accept: 'application/json' }
            })
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(payload => Array.isArray(payload.data) ? payload.data : [])
                .catch(() => []);
        }

        return countriesPromise;
    }

    function populatePhoneCountry(select) {
        if (!select || select.dataset.worldPhoneReady === '1') return;
        select.dataset.worldPhoneReady = '1';
        const selected = String(select.dataset.selected || select.value || '');

        countries().then(items => {
            const byCallingCode = new Map();
            items.forEach(country => {
                const code = String(country.calling_code || '').replace(/\D/g, '');
                if (!code) return;
                if (!byCallingCode.has(code)) byCallingCode.set(code, []);
                byCallingCode.get(code).push(country.iso2);
            });

            const options = Array.from(byCallingCode.entries())
                .map(([code, isoCodes]) => ({
                    code,
                    label: `${isoCodes.slice(0, 3).join('/')} (+${code})`
                }))
                .sort((a, b) => a.label.localeCompare(b.label));

            if (options.length <= 2) return;

            select.innerHTML = '';
            options.forEach(item => select.add(new Option(item.label, item.code)));
            if (selected && Array.from(select.options).some(option => option.value === selected)) {
                select.value = selected;
            }
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    function init(root) {
        (root || document).querySelectorAll('[data-world-phone-country]').forEach(populatePhoneCountry);
    }

    window.SaxWorldLocations = { countries, init };
    document.addEventListener('DOMContentLoaded', () => init(document));
}());
