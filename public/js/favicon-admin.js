(function () {
    'use strict';

    const root = document.querySelector('.theme-favicons');
    if (!root) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const accepted = new Set(['png', 'jpg', 'jpeg', 'webp', 'gif', 'ico', 'avif']);

    function refreshTabIcon(card, url) {
        if (card.dataset.layout !== root.dataset.activeLayout) return;
        const icon = document.querySelector('head link[rel="icon"]');
        if (icon) {
            icon.href = url;
            icon.type = url.split('?')[0].endsWith('.svg') ? 'image/svg+xml' : 'image/png';
        }
    }

    function setStatus(card, message, error) {
        const status = card.querySelector('[data-favicon-status]');
        status.textContent = message;
        status.classList.toggle('is-error', Boolean(error));
    }

    async function responseJson(response) {
        const payload = await response.json().catch(function () { return {}; });
        if (!response.ok) {
            const firstError = payload.errors && Object.values(payload.errors).flat()[0];
            throw new Error(firstError || payload.message || 'Não foi possível enviar o ícone.');
        }
        return payload;
    }

    root.querySelectorAll('[data-favicon-card]').forEach(function (card) {
        const uploadForm = card.querySelector('[data-favicon-upload]');
        const resetForm = card.querySelector('[data-favicon-reset]');
        const input = card.querySelector('[data-favicon-input]');
        const dropzone = card.querySelector('[data-favicon-dropzone]');
        const preview = card.querySelector('[data-favicon-preview]');
        let uploading = false;
        uploadForm.classList.add('has-js');

        async function upload(file) {
            if (!file || uploading) return;
            const extension = file.name.split('.').pop().toLowerCase();
            if (!accepted.has(extension)) {
                setStatus(card, 'Formato inválido. Use PNG, JPG/JPEG, WebP, GIF, ICO ou AVIF.', true);
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                setStatus(card, 'O arquivo deve ter no máximo 5 MB.', true);
                return;
            }

            uploading = true;
            card.classList.add('is-uploading');
            setStatus(card, 'Convertendo e enviando…', false);
            const body = new FormData(uploadForm);
            body.set('favicon', file);

            try {
                const payload = await responseJson(await fetch(uploadForm.action, {
                    method: 'POST',
                    headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
                    body: body
                }));
                preview.src = payload.url;
                refreshTabIcon(card, payload.url);
                setStatus(card, payload.message || 'Ícone atualizado.', false);
                input.value = '';
            } catch (error) {
                setStatus(card, error.message, true);
            } finally {
                uploading = false;
                card.classList.remove('is-uploading');
            }
        }

        input.addEventListener('change', function () { upload(input.files?.[0]); });
        uploadForm.addEventListener('submit', function (event) {
            event.preventDefault();
            upload(input.files?.[0]);
        });
        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (event) {
                event.preventDefault();
                dropzone.classList.remove('is-dragover');
            });
        });
        dropzone.addEventListener('drop', function (event) {
            upload(event.dataTransfer?.files?.[0]);
        });

        resetForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (uploading) return;
            card.classList.add('is-uploading');
            setStatus(card, 'Restaurando ícone padrão…', false);
            try {
                const payload = await responseJson(await fetch(resetForm.action, {
                    method: 'DELETE',
                    headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': csrf}
                }));
                preview.src = payload.url;
                refreshTabIcon(card, payload.url);
                setStatus(card, payload.message || 'Ícone padrão restaurado.', false);
            } catch (error) {
                setStatus(card, error.message, true);
            } finally {
                card.classList.remove('is-uploading');
            }
        });
    });
})();
