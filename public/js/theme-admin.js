(function () {
    'use strict';

    const root = document.getElementById('themeAdmin');
    if (!root) return;

    const themes = JSON.parse(document.getElementById('themeAdminData').textContent);
    const fonts = JSON.parse(document.getElementById('themeFontData').textContent);
    const form = document.getElementById('themeForm');
    const preview = document.getElementById('themeLivePreview');
    const state = document.getElementById('themeSaveState');
    const title = document.getElementById('themeEditorTitle');
    const description = document.getElementById('themeEditorDescription');
    const previewLink = document.getElementById('themePreviewLink');
    const resetButton = document.getElementById('themeReset');
    const scopeButtons = Array.from(root.querySelectorAll('[data-theme-scope]'));
    const fields = Array.from(form.querySelectorAll('[data-theme-field]'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let activeScope = scopeButtons[0]?.dataset.themeScope;
    let debounceTimer = null;
    const requestControllers = {};
    let pendingScope = null;
    let hydrating = false;

    function endpoint(template, scope) {
        return template.replace('__scope__', encodeURIComponent(scope));
    }

    function setState(mode, message) {
        state.className = 'theme-save-state is-' + mode;
        const icon = mode === 'saving' ? 'fa-spinner fa-spin' : (mode === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check');
        state.innerHTML = '<i class="fa-solid ' + icon + '"></i><span>' + message + '</span>';
    }

    function getValues() {
        return fields.reduce(function (values, field) {
            values[field.dataset.themeField] = field.value;
            return values;
        }, {});
    }

    function updatePreview(settings) {
        const style = preview.style;
        style.setProperty('--preview-primary', settings.primary_color);
        style.setProperty('--preview-accent', settings.accent_color);
        style.setProperty('--preview-background', settings.background_color);
        style.setProperty('--preview-surface', settings.surface_color);
        style.setProperty('--preview-heading', settings.heading_color);
        style.setProperty('--preview-subtitle', settings.subtitle_color);
        style.setProperty('--preview-body', settings.body_color);
        style.setProperty('--preview-inverse', settings.inverse_text_color);
        style.setProperty('--preview-link', settings.link_color);
        style.setProperty('--preview-button', settings.button_background);
        style.setProperty('--preview-button-text', settings.button_text);
        style.setProperty('--preview-header-bg', settings.header_background_color);
        style.setProperty('--preview-header-text', settings.header_text_color);
        style.setProperty('--preview-header-accent', settings.header_accent_color);
        style.setProperty('--preview-footer-bg', settings.footer_background_color);
        style.setProperty('--preview-footer-heading', settings.footer_heading_color);
        style.setProperty('--preview-footer-text', settings.footer_text_color);
        style.setProperty('--preview-border', settings.border_color);
        style.setProperty('--preview-hover', settings.hover_color);
        style.setProperty('--preview-heading-font', fonts[settings.heading_font]?.stack || 'Georgia, serif');
        style.setProperty('--preview-body-font', fonts[settings.body_font]?.stack || 'Arial, sans-serif');
        style.setProperty('--preview-header-font', fonts[settings.header_font]?.stack || 'Arial, sans-serif');
        style.setProperty('--preview-footer-font', fonts[settings.footer_font]?.stack || 'Arial, sans-serif');
        style.setProperty('--preview-heading-weight', settings.heading_weight === 'original' ? '600' : settings.heading_weight);
        style.setProperty('--preview-base-size', settings.base_font_size === 'original' ? '15px' : settings.base_font_size + 'px');
        style.setProperty('--preview-button-radius', settings.button_radius === 'original' ? '0px' : (settings.button_radius === '999' ? '999px' : settings.button_radius + 'px'));
        style.setProperty('--preview-card-radius', settings.card_radius === 'original' ? '0px' : settings.card_radius + 'px');
        updateContrast(settings);
    }

    function luminance(hex) {
        const channels = hex.slice(1).match(/.{2}/g).map(function (part) {
            const value = parseInt(part, 16) / 255;
            return value <= 0.03928 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
        });
        return (0.2126 * channels[0]) + (0.7152 * channels[1]) + (0.0722 * channels[2]);
    }

    function contrast(first, second) {
        const a = luminance(first);
        const b = luminance(second);
        return (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05);
    }

    function updateContrast(settings) {
        const checks = [
            ['Títulos', settings.heading_color, settings.surface_color, 3],
            ['Descrições', settings.body_color, settings.surface_color, 4.5],
            ['Botão', settings.button_text, settings.button_background, 4.5],
            ['Texto em fundo escuro', settings.inverse_text_color, settings.primary_color, 4.5],
            ['Header', settings.header_text_color, settings.header_background_color, 4.5],
            ['Footer', settings.footer_text_color, settings.footer_background_color, 4.5]
        ];
        const failures = checks.filter(function (item) { return contrast(item[1], item[2]) < item[3]; });
        const box = document.getElementById('themeContrast');
        if (!failures.length) {
            box.className = 'theme-contrast is-ok';
            box.innerHTML = '<i class="fa-solid fa-shield-halved"></i><span><strong>Contraste adequado</strong> As principais combinações estão legíveis.</span>';
            return;
        }
        box.className = 'theme-contrast is-warning';
        box.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i><span><strong>Atenção ao contraste:</strong> revise ' + failures.map(function (item) { return item[0].toLowerCase(); }).join(', ') + '.</span>';
    }

    function hydrate(scope) {
        hydrating = true;
        const theme = themes[scope];
        title.textContent = theme.meta.label;
        description.textContent = theme.meta.description;

        fields.forEach(function (field) {
            const key = field.dataset.themeField;
            field.value = theme.settings[key];
            const hex = form.querySelector('[data-color-hex="' + key + '"]');
            if (hex) hex.value = theme.settings[key].toUpperCase();
        });

        updatePreview(theme.settings);
        hydrating = false;
    }

    function markCustomized(scope, customized) {
        const button = scopeButtons.find(function (item) { return item.dataset.themeScope === scope; });
        const badge = button?.querySelector('[data-scope-status]');
        if (!badge) return;
        badge.classList.toggle('is-custom', customized);
        badge.textContent = customized ? 'Personalizado' : 'Original';
    }

    async function save(scopeOverride, settingsOverride) {
        if (!activeScope || hydrating) return;
        const scopeAtRequest = scopeOverride || activeScope;
        const settings = settingsOverride || getValues();
        window.clearTimeout(debounceTimer);
        if (pendingScope === scopeAtRequest) pendingScope = null;
        requestControllers[scopeAtRequest]?.abort();
        requestControllers[scopeAtRequest] = new AbortController();
        if (activeScope === scopeAtRequest) setState('saving', 'Salvando…');

        try {
            const response = await fetch(endpoint(root.dataset.updateUrl, scopeAtRequest), {
                method: 'PUT',
                headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf},
                body: JSON.stringify({settings: settings}),
                signal: requestControllers[scopeAtRequest].signal
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Não foi possível salvar.');
            themes[scopeAtRequest].settings = payload.settings;
            themes[scopeAtRequest].customized = true;
            markCustomized(scopeAtRequest, true);
            if (activeScope === scopeAtRequest) setState('saved', 'Salvo agora');
        } catch (error) {
            if (error.name !== 'AbortError' && activeScope === scopeAtRequest) setState('error', error.message || 'Erro ao salvar');
        }
    }

    function scheduleSave() {
        if (hydrating) return;
        window.clearTimeout(debounceTimer);
        pendingScope = activeScope;
        setState('saving', 'Alterações pendentes…');
        debounceTimer = window.setTimeout(save, 850);
    }

    scopeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (button.dataset.themeScope === activeScope) return;
            if (pendingScope === activeScope) save(activeScope, getValues());
            activeScope = button.dataset.themeScope;
            scopeButtons.forEach(function (item) { item.classList.toggle('is-active', item === button); });
            previewLink.href = button.dataset.previewUrl;
            hydrate(activeScope);
            setState('saved', themes[activeScope].customized ? 'Personalizado' : 'Tema original');
        });
    });

    fields.forEach(function (field) {
        const eventName = field.matches('select') ? 'change' : 'input';
        field.addEventListener(eventName, function () {
            const key = field.dataset.themeField;
            const hex = form.querySelector('[data-color-hex="' + key + '"]');
            if (hex) hex.value = field.value.toUpperCase();
            updatePreview(getValues());
            scheduleSave();
        });
    });

    form.querySelectorAll('[data-color-hex]').forEach(function (hex) {
        hex.addEventListener('input', function () {
            let value = hex.value.trim();
            if (value && value.charAt(0) !== '#') value = '#' + value;
            if (!/^#[0-9a-fA-F]{6}$/.test(value)) {
                hex.classList.add('is-invalid');
                return;
            }
            hex.classList.remove('is-invalid');
            const picker = form.querySelector('[data-theme-field="' + hex.dataset.colorHex + '"]');
            picker.value = value;
            hex.value = value.toUpperCase();
            updatePreview(getValues());
            scheduleSave();
        });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        save();
    });

    resetButton.addEventListener('click', async function () {
        if (!window.confirm('Restaurar a identidade original desta área?')) return;
        window.clearTimeout(debounceTimer);
        pendingScope = null;
        requestControllers[activeScope]?.abort();
        setState('saving', 'Restaurando…');
        try {
            const response = await fetch(endpoint(root.dataset.resetUrl, activeScope), {
                method: 'DELETE',
                headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf}
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Não foi possível restaurar.');
            themes[activeScope].settings = payload.settings;
            themes[activeScope].customized = false;
            hydrate(activeScope);
            markCustomized(activeScope, false);
            setState('saved', 'Tema original restaurado');
        } catch (error) {
            setState('error', error.message || 'Erro ao restaurar');
        }
    });

    hydrate(activeScope);
})();
