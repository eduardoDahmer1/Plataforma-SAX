(() => {
    'use strict';

    async function copyText(value) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(value);
            return;
        }

        const input = document.createElement('textarea');
        input.value = value;
        input.setAttribute('readonly', '');
        input.style.position = 'fixed';
        input.style.opacity = '0';
        input.style.pointerEvents = 'none';
        document.body.append(input);
        input.select();
        input.setSelectionRange(0, input.value.length);
        const copied = document.execCommand('copy');
        input.remove();
        if (!copied) throw new Error('Clipboard copy failed');
    }

    document.addEventListener('click', async event => {
        const button = event.target.closest('[data-copy-link]');
        if (!button || button.disabled) return;

        const label = button.dataset.copyLabel || 'Copiar link';
        const copiedLabel = button.dataset.copiedLabel || 'Link copiado!';
        const icon = button.querySelector('[data-copy-link-icon]');
        const mobileLabel = button.querySelector('[data-copy-link-label]');
        const tooltip = button.querySelector('[data-copy-link-tooltip]');
        const status = button.querySelector('[data-copy-link-status]');

        try {
            button.disabled = true;
            await copyText(button.dataset.copyUrl || window.location.href);
            button.classList.add('is-copied');
            if (icon) icon.className = 'fa-solid fa-check';
            if (mobileLabel) mobileLabel.textContent = copiedLabel;
            if (tooltip) tooltip.textContent = copiedLabel;
            if (status) status.textContent = copiedLabel;

            window.setTimeout(() => {
                button.classList.remove('is-copied');
                button.disabled = false;
                if (icon) icon.className = 'fa-solid fa-link';
                if (mobileLabel) mobileLabel.textContent = label;
                if (tooltip) tooltip.textContent = label;
                if (status) status.textContent = '';
            }, 1800);
        } catch (error) {
            button.disabled = false;
            button.classList.add('has-error');
            window.setTimeout(() => button.classList.remove('has-error'), 1800);
        }
    });
})();
