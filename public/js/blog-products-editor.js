(function () {
    'use strict';

    window.saxBlogProductsEditor = function (editor) {
        editor.ui.registry.addButton('blogproducts', {
            text: 'Productos',
            tooltip: 'Insertar o editar carrusel de productos',
            onAction: function () {
                const endpoint = document.getElementById('editor-blog').dataset.productsUrl;
                const existing = editor.dom.getParent(editor.selection.getNode(), 'div[data-blog-products]');
                const bookmark = editor.selection.getBookmark(2, true);
                const ids = existing ? existing.dataset.blogProducts.split(',').map(Number) : [];
                let selected = ids.map(id => ({ id: id, name: 'Producto #' + id, available: false }));
                let timer;
                let searchRequest;
                const dialog = document.createElement('dialog');
                dialog.className = 'blog-products-dialog';
                dialog.setAttribute('aria-labelledby', 'blogProductsDialogTitle');
                dialog.innerHTML = '<div class="blog-products-dialog__header"><h2 id="blogProductsDialogTitle">Carrusel de productos</h2>' +
                    '<button type="button" data-close aria-label="Cerrar">×</button></div>' +
                    '<label for="blogProductsHeading">Título del carrusel</label><input id="blogProductsHeading" maxlength="120" placeholder="Productos del artículo">' +
                    '<label for="blogProductsQuery">Buscar por nombre o SKU</label><input id="blogProductsQuery" type="search" maxlength="120" placeholder="Escribí para buscar…">' +
                    '<p class="small text-muted" data-status role="status" aria-live="polite"></p>' +
                    '<div class="blog-products-dialog__results" data-results></div>' +
                    '<h3>Seleccionados <span data-count></span>/20</h3><p class="small text-muted">Se muestran en este orden. Los productos no disponibles se omiten en el artículo.</p>' +
                    '<ol class="blog-products-dialog__selection" data-selection></ol>' +
                    '<div class="blog-products-dialog__actions"><button type="button" class="btn btn-outline-danger" data-delete>Quitar carrusel</button>' +
                    '<button type="button" class="btn btn-outline-dark" data-close>Cancelar</button>' +
                    '<button type="button" class="btn btn-dark" data-insert>Aplicar carrusel</button></div>';
                document.body.appendChild(dialog);
                const heading = dialog.querySelector('#blogProductsHeading');
                const query = dialog.querySelector('#blogProductsQuery');
                const status = dialog.querySelector('[data-status]');
                const results = dialog.querySelector('[data-results]');
                const selection = dialog.querySelector('[data-selection]');
                const insertButton = dialog.querySelector('[data-insert]');
                heading.value = existing ? existing.dataset.title || '' : '';
                dialog.querySelector('[data-delete]').hidden = !existing;

                function escapeHtml(value) {
                    const node = document.createElement('span');
                    node.textContent = value == null ? '' : String(value);
                    return node.innerHTML.replace(/"/g, '&quot;');
                }

                function actionButton(text, label, action, disabled) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'btn btn-sm btn-outline-dark';
                    button.textContent = text;
                    button.setAttribute('aria-label', label);
                    button.disabled = !!disabled;
                    button.addEventListener('click', action);
                    return button;
                }

                function drawSelection() {
                    selection.replaceChildren();
                    dialog.querySelector('[data-count]').textContent = selected.length;
                    insertButton.disabled = !selected.length;
                    selected.forEach(function (product, index) {
                        const row = document.createElement('li');
                        const label = document.createElement('span');
                        label.textContent = product.name + (product.available ? '' : ' · No disponible');
                        row.appendChild(label);
                        const actions = document.createElement('div');
                        actions.appendChild(actionButton('↑', 'Mover hacia arriba: ' + product.name, function () {
                            [selected[index - 1], selected[index]] = [selected[index], selected[index - 1]];
                            drawSelection();
                        }, index === 0));
                        actions.appendChild(actionButton('↓', 'Mover hacia abajo: ' + product.name, function () {
                            [selected[index + 1], selected[index]] = [selected[index], selected[index + 1]];
                            drawSelection();
                        }, index === selected.length - 1));
                        actions.appendChild(actionButton('×', 'Quitar: ' + product.name, function () {
                            selected.splice(index, 1);
                            drawSelection();
                        }));
                        row.appendChild(actions);
                        selection.appendChild(row);
                    });
                }

                async function lookup(params, signal) {
                    const url = new URL(endpoint, window.location.href);
                    Object.entries(params).forEach(function ([key, value]) {
                        if (Array.isArray(value)) value.forEach(id => url.searchParams.append(key + '[]', id));
                        else url.searchParams.set(key, value);
                    });
                    const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: signal });
                    if (!response.ok) throw new Error('No fue posible consultar el catálogo. Intentá nuevamente.');
                    return (await response.json()).products;
                }

                query.addEventListener('input', function () {
                    clearTimeout(timer);
                    if (searchRequest) searchRequest.abort();
                    results.replaceChildren();
                    const term = query.value.trim();
                    status.textContent = '';
                    if (!term) return;
                    searchRequest = new AbortController();
                    const signal = searchRequest.signal;
                    timer = setTimeout(async function () {
                        status.textContent = 'Buscando…';
                        try {
                            const products = await lookup({ q: term }, signal);
                            if (!dialog.open || signal.aborted) return;
                            status.textContent = products.length ? 'Seleccioná los productos para agregarlos.' : 'No se encontraron productos disponibles.';
                            products.forEach(function (product) {
                                const row = document.createElement('div');
                                row.className = 'blog-products-dialog__result';
                                const image = document.createElement('img');
                                image.src = product.image;
                                image.alt = '';
                                const label = document.createElement('span');
                                label.textContent = product.name + ' · ' + (product.sku || '#' + product.id);
                                const add = actionButton('Agregar', 'Agregar: ' + product.name, function () {
                                    if (selected.some(item => item.id === product.id)) {
                                        status.textContent = 'Este producto ya está seleccionado.';
                                    } else if (selected.length >= 20) {
                                        status.textContent = 'Podés seleccionar hasta 20 productos.';
                                    } else {
                                        selected.push(product);
                                        drawSelection();
                                        status.textContent = 'Producto agregado: ' + product.name;
                                    }
                                });
                                row.append(image, label, add);
                                results.appendChild(row);
                            });
                        } catch (error) {
                            if (error.name !== 'AbortError' && dialog.open) status.textContent = error.message;
                        }
                    }, 250);
                });

                dialog.querySelectorAll('[data-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
                dialog.addEventListener('close', function () {
                    clearTimeout(timer);
                    if (searchRequest) searchRequest.abort();
                    dialog.remove();
                    editor.focus();
                });

                dialog.querySelector('[data-delete]').addEventListener('click', function () {
                    editor.undoManager.transact(function () { editor.dom.remove(existing); });
                    editor.nodeChanged();
                    editor.save();
                    dialog.close();
                });

                insertButton.addEventListener('click', function () {
                    if (!selected.length) return;
                    if (!existing && editor.getBody().querySelectorAll('[data-blog-products]').length >= 10) {
                        status.textContent = 'El artículo puede tener hasta 10 carruseles.';
                        return;
                    }
                    const title = heading.value.trim();
                    const html = '<div class="blog-products-placeholder mceNonEditable" contenteditable="false" data-blog-products="' +
                        selected.map(item => item.id).join(',') + '" data-title="' + escapeHtml(title) + '">' +
                        '<strong>' + escapeHtml(title || 'Productos del artículo') + '</strong><br>' +
                        selected.map(item => escapeHtml(item.name)).join(' · ') + '</div>';
                    editor.undoManager.transact(function () {
                        if (existing && existing.isConnected) {
                            const container = editor.getDoc().createElement('div');
                            container.innerHTML = html;
                            existing.replaceWith(container.firstElementChild);
                        } else {
                            editor.selection.moveToBookmark(bookmark);
                            editor.insertContent(html + '<p><br></p>');
                        }
                    });
                    editor.nodeChanged();
                    editor.save();
                    dialog.close();
                });

                drawSelection();
                dialog.showModal();
                if (ids.length) {
                    lookup({ ids: ids }).then(function (products) {
                        if (!dialog.open) return;
                        selected = selected.map(item => products.find(product => product.id === item.id) || item);
                        drawSelection();
                    }).catch(function (error) { if (dialog.open) status.textContent = error.message; });
                }
            },
        });
    };
})();
