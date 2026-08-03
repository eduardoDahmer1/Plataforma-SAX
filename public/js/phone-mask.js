(function () {
    'use strict';

    const phoneSelector = [
        'input[type="tel"]',
        'input[name="phone"]',
        'input[name="phone_number"]',
        'input[name="telefono"]',
        'input[name="contato_whatsapp"]',
        'input[name*="[phone]"]',
        'input[name*="whatsapp"]',
        'input[id*="phone"]',
        'input[id*="telefone"]'
    ].join(',');

    function digits(value) {
        return String(value || '').replace(/\D+/g, '');
    }

    function countrySelect(input) {
        const form = input.closest('form');
        return form ? form.querySelector('select[name="phone_country"]') : null;
    }

    function selectedCountry(input) {
        return digits(countrySelect(input)?.value || input.dataset.phoneCountry || '');
    }

    function removeCountryCode(value, country) {
        let number = digits(value);
        if (country && number.startsWith(country) && number.length > country.length + 6) {
            number = number.slice(country.length);
        }
        return number;
    }

    function formatBrazil(value) {
        let number = removeCountryCode(value, '55');
        if (number.length > 11 && number.startsWith('0')) number = number.slice(1);
        number = number.slice(0, 11);

        if (!number) return '';
        if (number.length <= 2) return `(${number}`;

        const area = number.slice(0, 2);
        const local = number.slice(2);
        if (local.length <= 4) return `(${area}) ${local}`;

        const prefixLength = local.length > 8 ? 5 : local.length - 4;
        return `(${area}) ${local.slice(0, prefixLength)}-${local.slice(prefixLength)}`;
    }

    function formatParaguay(value, international) {
        let number = removeCountryCode(value, '595').replace(/^0+/, '').slice(0, 11);
        if (!number) return international ? '+595 ' : '';

        let formatted;
        if (number.length <= 3) {
            formatted = number;
        } else if (number.length <= 6) {
            formatted = `${number.slice(0, 3)} ${number.slice(3)}`;
        } else if (number.length <= 9) {
            formatted = `${number.slice(0, 3)} ${number.slice(3, 6)}-${number.slice(6)}`;
        } else {
            formatted = `${number.slice(0, 3)} ${number.slice(3, -4)}-${number.slice(-4)}`;
        }

        return international ? `+595 ${formatted}` : `0${formatted}`;
    }

    function formatStandalone(value) {
        const number = digits(value);
        if (!number) return '';
        if (number.startsWith('55') && number.length >= 12) {
            return `+55 ${formatBrazil(number)}`;
        }
        if (number.startsWith('595') && number.length >= 10) {
            return formatParaguay(number, true);
        }
        if (number.startsWith('0') || (number.startsWith('9') && number.length <= 11)) {
            return formatParaguay(number, false);
        }

        return number.replace(/(\d{3})(?=\d)/g, '$1 ').trim();
    }

    function formatInput(input) {
        if (!(input instanceof HTMLInputElement) || !input.matches(phoneSelector)) return;

        const country = selectedCountry(input);
        if (country === '55') {
            input.value = formatBrazil(input.value);
            input.placeholder = '(45) 99158-8886';
        } else if (country === '595') {
            input.value = formatParaguay(input.value, false);
            input.placeholder = '0981 123-456';
        } else {
            input.value = formatStandalone(input.value);
        }

        input.inputMode = 'tel';
        input.autocomplete = input.autocomplete || 'tel';
    }

    function unmaskInput(input) {
        if (!(input instanceof HTMLInputElement) || !input.matches(phoneSelector)) return;

        const country = selectedCountry(input);
        let number = digits(input.value);
        if (country && number.startsWith(country) && number.length > country.length + 6) {
            number = number.slice(country.length);
        }
        input.value = number;
    }

    function initialize(root) {
        if (root instanceof HTMLInputElement) formatInput(root);
        root.querySelectorAll?.(phoneSelector).forEach(formatInput);
    }

    document.addEventListener('DOMContentLoaded', function () {
        initialize(document);
    });

    document.addEventListener('input', function (event) {
        if (event.target instanceof HTMLInputElement && event.target.matches(phoneSelector)) {
            formatInput(event.target);
        }
    });

    document.addEventListener('focusin', function (event) {
        if (event.target instanceof HTMLInputElement && event.target.matches(phoneSelector)) {
            formatInput(event.target);
        }
    });

    document.addEventListener('change', function (event) {
        if (!(event.target instanceof HTMLSelectElement) || event.target.name !== 'phone_country') return;

        const form = event.target.closest('form');
        form?.querySelectorAll(phoneSelector).forEach(formatInput);
    });

    document.addEventListener('submit', function (event) {
        event.target.querySelectorAll?.(phoneSelector).forEach(unmaskInput);
    }, true);

    new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node instanceof Element) initialize(node);
            });
        });
    }).observe(document.documentElement, { childList: true, subtree: true });
})();
