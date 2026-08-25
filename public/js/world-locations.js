(function () {
    'use strict';

    const countryPromises = new Map();
    const postalOverrides = {
        AR: { format: '9999', example: '1000' },
        AU: { format: '9999', example: '2000' },
        BR: { format: '99999-999', example: '10000-000' },
        CA: { format: 'A9A 9A9', example: 'M5V 3L9' },
        CO: { format: '999999', example: '110111' },
        GB: { format: 'A9A 9AA', example: 'SW1A 1AA' },
        US: { format: '99999', example: '10001' }
    };

    function countries(sourceUrl) {
        const url = sourceUrl || '/api/locations/countries';
        if (!countryPromises.has(url)) {
            countryPromises.set(url, fetch(url, {
                headers: { Accept: 'application/json' }
            })
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(payload => Array.isArray(payload.data) ? payload.data : [])
                .catch(() => []));
        }

        return countryPromises.get(url);
    }

    function readablePostalFormat(format) {
        return String(format || '')
            .replaceAll('@', 'A')
            .replaceAll('#', '9');
    }

    function postalGuide(iso2, sourceUrl) {
        const code = String(iso2 || '').trim().toUpperCase();
        if (postalOverrides[code]) return Promise.resolve(postalOverrides[code]);

        return countries(sourceUrl).then(items => {
            const country = items.find(item => String(item.iso2 || '').toUpperCase() === code);
            return {
                format: readablePostalFormat(country?.postal_code_format || ''),
                example: ''
            };
        });
    }

    function postalCodes(iso2, city, adminCode, sourceUrl) {
        const country = String(iso2 || '').trim().toUpperCase();
        const place = String(city || '').trim();
        if (!country || !place) return Promise.resolve([]);

        const query = new URLSearchParams({ country, city: place });
        if (adminCode) query.set('admin_code', adminCode);

        return fetch(`${sourceUrl || '/api/locations/postal-codes'}?${query.toString()}`, {
            headers: { Accept: 'application/json' }
        })
            .then(response => response.ok ? response.json() : Promise.reject())
            .then(payload => Array.isArray(payload.data) ? payload.data : [])
            .catch(() => []);
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

    window.SaxWorldLocations = { countries, postalGuide, postalCodes, init };
    document.addEventListener('DOMContentLoaded', () => init(document));
}());
