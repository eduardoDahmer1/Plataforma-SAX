(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const jsonHeaders = { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' };

    const message = (type, text) => {
        if (typeof window.saxToast === 'function') window.saxToast(type, text);
        else window.alert(text);
    };

    const responseJson = async response => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const validation = data.errors ? Object.values(data.errors).flat()[0] : null;
            throw new Error(validation || data.message || 'Não foi possível concluir a operação.');
        }
        return data;
    };

    document.querySelectorAll('[data-home-banner-manager]').forEach(manager => {
        const input = manager.querySelector('[data-banner-input]');
        const dropzone = manager.querySelector('[data-banner-dropzone]');
        const list = manager.querySelector('[data-banner-list]');
        const status = manager.querySelector('[data-upload-status]');
        let dragged = null;
        let reorderTimer = null;

        const refreshState = () => {
            const items = [...list.querySelectorAll('.home-banner-item')];
            list.classList.toggle('is-empty', items.length === 0);
            items.forEach((item, index) => {
                const order = item.querySelector('.home-banner-item__order b');
                if (order) order.textContent = String(index + 1).padStart(2, '0');
            });
        };

        const saveOrder = () => {
            clearTimeout(reorderTimer);
            reorderTimer = setTimeout(async () => {
                try {
                    await responseJson(await fetch(manager.dataset.reorderUrl, {
                        method: 'PUT',
                        headers: jsonHeaders,
                        body: JSON.stringify({ items: [...list.querySelectorAll('.home-banner-item')].map(item => item.dataset.bannerId) })
                    }));
                    message('success', 'Nova ordem salva.');
                } catch (error) {
                    message('error', error.message);
                }
            }, 250);
        };

        const bindItem = item => {
            const link = item.querySelector('[data-banner-link]');
            const active = item.querySelector('[data-banner-active]');
            const saveState = item.querySelector('[data-save-state]');
            const copyFields = [...item.querySelectorAll('[data-banner-copy-field]')];
            let saveTimer = null;

            const save = () => {
                clearTimeout(saveTimer);
                saveTimer = setTimeout(async () => {
                    saveState.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                    try {
                        const copy = Object.fromEntries(copyFields.map(field => [field.dataset.bannerCopyField, field.value.trim()]));
                        await responseJson(await fetch(item.dataset.updateUrl, {
                            method: 'PATCH',
                            headers: jsonHeaders,
                            body: JSON.stringify({ link: link.value.trim(), is_active: active.checked, ...copy })
                        }));
                        saveState.innerHTML = '<i class="fa-solid fa-check"></i>';
                        setTimeout(() => { saveState.textContent = ''; }, 1800);
                    } catch (error) {
                        saveState.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
                        message('error', error.message);
                    }
                }, 450);
            };

            link.addEventListener('input', save);
            link.addEventListener('blur', save);
            active.addEventListener('change', save);
            copyFields.forEach(field => {
                field.addEventListener('input', save);
                field.addEventListener('blur', save);
            });

            item.querySelector('[data-banner-delete]').addEventListener('click', async () => {
                if (!window.confirm('Remover este banner da home?')) return;
                item.classList.add('is-busy');
                try {
                    const data = await responseJson(await fetch(item.dataset.deleteUrl, {
                        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                    }));
                    item.remove();
                    refreshState();
                    message('success', data.message);
                } catch (error) {
                    item.classList.remove('is-busy');
                    message('error', error.message);
                }
            });

            item.addEventListener('dragstart', () => {
                dragged = item;
                item.classList.add('is-dragging');
            });
            item.addEventListener('dragend', () => {
                item.classList.remove('is-dragging');
                dragged = null;
                refreshState();
                saveOrder();
            });
        };

        const makeItem = data => {
            const item = document.createElement('article');
            item.className = 'home-banner-item';
            item.draggable = true;
            item.dataset.bannerId = data.id;
            item.dataset.updateUrl = data.update_url;
            item.dataset.deleteUrl = data.delete_url;

            const media = document.createElement('div');
            media.className = 'home-banner-item__media';
            const image = document.createElement('img');
            image.src = data.image_url;
            image.alt = 'Novo banner';
            media.append(image);
            const order = document.createElement('span');
            order.className = 'home-banner-item__order';
            order.innerHTML = '<i class="fa-solid fa-grip-vertical"></i> <b>00</b>';
            media.append(order);

            const body = document.createElement('div');
            body.className = 'home-banner-item__body';
            body.innerHTML = '<label>Link deste banner</label><div class="home-banner-item__link"><i class="fa-solid fa-link"></i><input type="text" placeholder="https://... ou /categorias/..." data-banner-link><span class="home-banner-save-state" data-save-state></span></div>';
            if (manager.dataset.group === 'main') {
                const languages = [['pt', 'Português'], ['en', 'English'], ['es', 'Español']];
                const fields = languages.map(([code, label]) => `<fieldset><legend>${label}</legend><label>Título</label><input type="text" maxlength="160" data-banner-copy-field="title_${code}"><label>Descrição</label><textarea rows="3" maxlength="600" data-banner-copy-field="description_${code}"></textarea></fieldset>`).join('');
                body.insertAdjacentHTML('beforeend', `<details class="home-banner-copy"><summary><span><i class="fa-solid fa-language"></i> Título e descrição deste slide</span><i class="fa-solid fa-chevron-down"></i></summary><div class="home-banner-copy__languages">${fields}</div></details>`);
            }
            body.insertAdjacentHTML('beforeend', '<div class="home-banner-item__actions"><label class="home-banner-toggle"><input type="checkbox" data-banner-active checked><span></span> Exibir no site</label><button type="button" data-banner-delete><i class="fa-solid fa-trash"></i> Remover</button></div>');
            item.append(media, body);
            bindItem(item);
            return item;
        };

        list.querySelectorAll('.home-banner-item').forEach(bindItem);
        list.addEventListener('dragover', event => {
            event.preventDefault();
            if (!dragged) return;
            const siblings = [...list.querySelectorAll('.home-banner-item:not(.is-dragging)')];
            const next = siblings.find(sibling => event.clientY <= sibling.getBoundingClientRect().top + sibling.offsetHeight / 2);
            list.insertBefore(dragged, next || list.querySelector('[data-banner-empty]'));
        });

        ['dragenter', 'dragover'].forEach(name => dropzone.addEventListener(name, event => {
            event.preventDefault();
            dropzone.classList.add('is-dragging');
        }));
        ['dragleave', 'drop'].forEach(name => dropzone.addEventListener(name, () => dropzone.classList.remove('is-dragging')));

        input.addEventListener('change', async () => {
            const files = [...input.files];
            if (!files.length) return;
            status.textContent = `Enviando ${files.length} imagem(ns)...`;
            dropzone.classList.add('is-uploading');
            const body = new FormData();
            files.forEach(file => body.append('images[]', file));

            try {
                const data = await responseJson(await fetch(manager.dataset.uploadUrl, {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body
                }));
                data.items.forEach(item => list.insertBefore(makeItem(item), list.querySelector('[data-banner-empty]')));
                refreshState();
                status.textContent = data.message;
                message('success', data.message);
            } catch (error) {
                status.textContent = 'Falha no envio. Selecione novamente para tentar.';
                message('error', error.message);
            } finally {
                input.value = '';
                dropzone.classList.remove('is-uploading');
            }
        });

        refreshState();
    });
})();
