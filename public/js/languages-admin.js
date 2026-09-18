document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('tr-app');
    if (!app) return;

    const feedback = document.getElementById('tr-feedback');
    const saveAllButton = document.getElementById('languageSaveAll');
    const dirtyCount = document.getElementById('languageDirtyCount');
    const filterPanel = document.getElementById('languageFilters');
    const filterToggle = document.getElementById('languageFiltersToggle');
    const filterForm = document.getElementById('languageFilterForm');
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let feedbackTimer = null;
    let isSavingAll = false;

    function notify(message, isError) {
        if (!feedback) return;
        feedback.textContent = message;
        feedback.classList.toggle('is-error', Boolean(isError));
        clearTimeout(feedbackTimer);
        feedbackTimer = setTimeout(function () { feedback.textContent = ''; }, 3200);
    }

    function endpoint(name, id) {
        return app.dataset[name].replace('__ID__', id);
    }

    function dirtyRows() {
        return Array.from(app.querySelectorAll('[data-row].is-dirty-row'));
    }

    function refreshDirtyState() {
        const rows = dirtyRows();
        if (dirtyCount) dirtyCount.textContent = rows.length;
        if (saveAllButton) saveAllButton.hidden = rows.length === 0;
    }

    function updateRowDirtyState(row) {
        const inputs = Array.from(row.querySelectorAll('input[name]'));
        const changed = inputs.some(function (input) { return input.value !== input.defaultValue; });
        row.classList.toggle('is-dirty-row', changed);
        const saveButton = row.querySelector('[data-save]');
        if (saveButton) saveButton.hidden = !changed;
        refreshDirtyState();
    }

    function updateCompletion(row, missing) {
        row.classList.toggle('is-missing', missing);
        row.dataset.wasMissing = missing ? '1' : '0';
        const badge = row.querySelector('.language-row-status');
        if (!badge) return;
        badge.classList.toggle('is-missing', missing);
        badge.classList.toggle('is-complete', !missing);
        badge.innerHTML = '<i class="fa-solid ' + (missing ? 'fa-circle-exclamation' : 'fa-circle-check') + '"></i> ' + (missing ? 'Incompleta' : 'Completa');
    }

    async function saveRow(row, quiet) {
        if (!row || !row.classList.contains('is-dirty-row')) return true;
        const button = row.querySelector('[data-save]');
        const inputs = Array.from(row.querySelectorAll('input[name]'));
        const payload = {};
        inputs.forEach(function (input) { payload[input.name] = input.value; });
        if (button) button.disabled = true;

        try {
            const response = await fetch(endpoint('updateUrl', row.dataset.id), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: JSON.stringify(Object.assign(payload, { _method: 'PUT' }))
            });
            const data = await response.json();
            if (!response.ok) {
                const error = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Não foi possível salvar.');
                notify(error, true);
                return false;
            }

            inputs.forEach(function (input) {
                input.defaultValue = input.value;
                input.classList.remove('is-dirty');
            });
            row.classList.remove('is-dirty-row');
            if (button) button.hidden = true;
            updateCompletion(row, Boolean(data.falta));
            refreshDirtyState();
            if (!quiet) notify(data.message || 'Tradução salva.', false);
            return true;
        } catch (error) {
            notify(app.dataset.errorMessage, true);
            return false;
        } finally {
            if (button) button.disabled = false;
        }
    }

    app.addEventListener('input', function (event) {
        const input = event.target.closest('.language-table input[name]');
        if (!input) return;
        input.classList.toggle('is-dirty', input.value !== input.defaultValue);
        updateRowDirtyState(input.closest('[data-row]'));
    });

    app.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' || (!event.ctrlKey && !event.metaKey)) return;
        const row = event.target.closest('[data-row]');
        if (!row) return;
        event.preventDefault();
        saveRow(row, false);
    });

    app.addEventListener('click', async function (event) {
        const saveButton = event.target.closest('[data-save]');
        const deleteButton = event.target.closest('[data-del]');
        const copyButton = event.target.closest('[data-copy-key]');

        if (saveButton) {
            await saveRow(saveButton.closest('[data-row]'), false);
            return;
        }

        if (copyButton) {
            const key = copyButton.closest('[data-row]').querySelector('input[name="key"]').value;
            try {
                await navigator.clipboard.writeText(key);
                notify('Chave copiada: ' + key, false);
            } catch (error) {
                notify('Não foi possível copiar a chave.', true);
            }
            return;
        }

        if (!deleteButton) return;
        const row = deleteButton.closest('[data-row]');
        if (!window.confirm(app.dataset.confirmDelete)) return;
        deleteButton.disabled = true;
        try {
            const response = await fetch(endpoint('deleteUrl', row.dataset.id), {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ _method: 'DELETE' })
            });
            if (!response.ok) throw new Error('delete-failed');
            const data = await response.json();
            row.remove();
            refreshDirtyState();
            notify(data.message || 'Tradução excluída.', false);
        } catch (error) {
            deleteButton.disabled = false;
            notify(app.dataset.errorMessage, true);
        }
    });

    if (saveAllButton) {
        saveAllButton.addEventListener('click', async function () {
            if (isSavingAll) return;
            isSavingAll = true;
            saveAllButton.disabled = true;
            const rows = dirtyRows();
            let saved = 0;
            for (const row of rows) {
                if (await saveRow(row, true)) saved += 1;
            }
            saveAllButton.disabled = false;
            isSavingAll = false;
            notify(saved === rows.length ? saved + ' alterações salvas.' : saved + ' de ' + rows.length + ' alterações salvas.', saved !== rows.length);
        });
    }

    function setFiltersCollapsed(collapsed) {
        if (!filterPanel || !filterToggle) return;
        filterPanel.hidden = collapsed;
        filterToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        const label = filterToggle.querySelector('span');
        if (label) label.textContent = collapsed ? 'Mostrar filtros' : 'Minimizar filtros';
        localStorage.setItem('sax-language-filters-collapsed', collapsed ? '1' : '0');
    }

    if (filterToggle) {
        setFiltersCollapsed(localStorage.getItem('sax-language-filters-collapsed') === '1');
        filterToggle.addEventListener('click', function () { setFiltersCollapsed(!filterPanel.hidden); });
    }

    if (filterForm) {
        filterForm.querySelectorAll('[data-auto-submit]').forEach(function (select) {
            select.addEventListener('change', function () { filterForm.requestSubmit(); });
        });
    }

    window.addEventListener('beforeunload', function (event) {
        if (!dirtyRows().length || isSavingAll) return;
        event.preventDefault();
        event.returnValue = '';
    });
});
