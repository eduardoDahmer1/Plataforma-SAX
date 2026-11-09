document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-batch-rename]').forEach(function (root) {
        const trigger = root.querySelector('[data-rename-start]');
        const form = root.querySelector('[data-rename-form]');
        const input = form.querySelector('[name="name"]');
        const cancel = root.querySelector('[data-rename-cancel]');
        const status = root.querySelector('[data-rename-status]');
        let saving = false;

        function close() {
            if (saving) return;
            form.hidden = true;
            trigger.hidden = false;
            trigger.focus();
        }
        trigger.addEventListener('click', function () {
            input.value = root.dataset.name || '';
            status.textContent = '';
            input.removeAttribute('aria-invalid');
            trigger.hidden = true;
            form.hidden = false;
            input.focus();
            input.select();
        });
        cancel.addEventListener('click', close);
        form.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                close();
            }
        });
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (saving) return;
            saving = true;
            form.querySelectorAll('input, button').forEach(control => control.disabled = true);
            status.className = 'small text-muted';
            status.textContent = 'Guardando…';
            let saved = false;
            try {
                const response = await fetch(root.dataset.url, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                    },
                    body: JSON.stringify({ name: input.value.trim() }),
                });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.errors?.name?.[0] || 'No se pudo guardar el nombre. Intentá de nuevo.');
                }
                root.dataset.name = data.name || '';
                root.querySelector('[data-rename-label]').textContent = data.display_name;
                input.removeAttribute('aria-invalid');
                status.className = 'small text-success';
                status.textContent = 'Nombre guardado.';
                saved = true;
            } catch (error) {
                input.setAttribute('aria-invalid', 'true');
                status.className = 'small text-danger';
                status.textContent = error.message || 'No se pudo guardar el nombre. Intentá de nuevo.';
            } finally {
                saving = false;
                form.querySelectorAll('input, button').forEach(control => control.disabled = false);
            }
            if (saved) close();
            else input.focus();
        });
    });
});
