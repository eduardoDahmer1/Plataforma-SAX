(function () {
    'use strict';

    const i18n = window.saxTranslations || {};
    const messages = {
        cpf: i18n.document_invalid_cpf,
        rg_br: i18n.document_invalid_rg_br,
        ci_py: i18n.document_invalid_ci_py,
        ruc_py: i18n.document_invalid_ruc_py
    };

    function digits(value) {
        return String(value || '').replace(/\D/g, '');
    }

    function groupThousands(value) {
        return value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function format(value, type) {
        if (type === 'cpf') {
            const number = digits(value).slice(0, 11);
            return number
                .replace(/^(\d{3})(\d)/, '$1.$2')
                .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
        }

        if (type === 'ci_py') {
            return groupThousands(digits(value).slice(0, 10));
        }

        if (type === 'ruc_py') {
            const number = digits(value).slice(0, 9);
            return number.length <= 3 ? number : number.slice(0, -1) + '-' + number.slice(-1);
        }

        const clean = String(value || '').toUpperCase().replace(/[^0-9X]/g, '').slice(0, 10);
        if (clean.length <= 4) return clean;
        return groupThousands(clean.slice(0, -1)) + '-' + clean.slice(-1);
    }

    function validCpf(value) {
        const cpf = digits(value);
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;

        for (let position = 9; position <= 10; position++) {
            let sum = 0;
            for (let index = 0; index < position; index++) {
                sum += Number(cpf[index]) * ((position + 1) - index);
            }
            let digit = (10 * sum) % 11;
            if (digit === 10) digit = 0;
            if (Number(cpf[position]) !== digit) return false;
        }
        return true;
    }

    function rucCheckDigit(base) {
        let weight = 2;
        let total = 0;
        for (let index = base.length - 1; index >= 0; index--) {
            if (weight > 11) weight = 2;
            total += Number(base[index]) * weight;
            weight++;
        }
        const remainder = total % 11;
        return remainder > 1 ? 11 - remainder : 0;
    }

    function isValid(value, type) {
        if (type === 'cpf') return validCpf(value);
        if (type === 'ci_py') {
            const number = digits(value);
            return /^\d{5,10}$/.test(number) && !/^(\d)\1+$/.test(number);
        }
        if (type === 'ruc_py') {
            const normalized = String(value || '').replace(/[.\s]/g, '');
            const match = normalized.match(/^(\d{3,8})-(\d)$/);
            return !!match && rucCheckDigit(match[1]) === Number(match[2]);
        }

        const rg = String(value || '').toUpperCase().replace(/[^0-9X]/g, '');
        return /^\d{4,9}[0-9X]$/.test(rg) && !/^(\d)\1+$/.test(rg);
    }

    function configureInput(input, type) {
        const settings = {
            cpf: ['000.000.000-00', 14, 'numeric'],
            rg_br: ['00.000.000-0', 13, 'text'],
            ci_py: ['0.000.000', 13, 'numeric'],
            ruc_py: ['80012345-6', 10, 'numeric']
        };
        const current = settings[type] || settings.ci_py;
        input.placeholder = current[0];
        input.maxLength = current[1];
        input.inputMode = current[2];
        input.value = format(input.value, type);
    }

    function bind(group) {
        if (group.dataset.documentReady === '1') return;
        const type = group.querySelector('[data-document-type]');
        const input = group.querySelector('[data-document-input]');
        const feedback = group.querySelector('[data-document-feedback]');
        if (!type || !input) return;
        group.dataset.documentReady = '1';

        const validate = function (showFeedback) {
            const valid = isValid(input.value, type.value);
            const message = valid ? '' : (messages[type.value] || i18n.document_invalid || 'messages.document_invalid_generic');
            input.setCustomValidity(message);
            input.classList.toggle('is-invalid', showFeedback && !valid);
            type.classList.toggle('is-invalid', showFeedback && !valid);
            if (feedback) {
                feedback.textContent = message;
                feedback.style.display = showFeedback && !valid ? 'block' : 'none';
            }
            return valid;
        };

        type.addEventListener('change', function () {
            configureInput(input, type.value);
            validate(input.dataset.documentTouched === '1');
        });
        input.addEventListener('input', function () {
            input.value = format(input.value, type.value);
            validate(input.dataset.documentTouched === '1');
        });
        input.addEventListener('blur', function () {
            input.dataset.documentTouched = '1';
            validate(true);
        });
        input.form?.addEventListener('submit', function () {
            input.dataset.documentTouched = '1';
            validate(true);
        });

        group.saxValidateDocument = function (showFeedback = true) {
            if (showFeedback) input.dataset.documentTouched = '1';
            return validate(showFeedback);
        };

        configureInput(input, type.value);
        validate(false);
    }

    function init(root) {
        (root || document).querySelectorAll('[data-document-group]').forEach(bind);
    }

    window.SaxCustomerDocument = { init, format, isValid };
    document.addEventListener('DOMContentLoaded', function () { init(document); });
}());
