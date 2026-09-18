(function () {
    'use strict';

    const form = document.getElementById('sectionsForm');
    const preview = document.getElementById('homeLivePreview');
    if (!form || !preview) return;

    const dataElement = document.getElementById('homePreviewData');
    let data = {opticalItems: [], saxCategories: []};
    try {
        data = dataElement ? JSON.parse(dataElement.textContent) : data;
    } catch (error) {
        data = {opticalItems: [], saxCategories: []};
    }
    function uniqueItems(items) {
        const seen = new Set();
        return (Array.isArray(items) ? items : []).filter(function (item) {
            if (!item || !item.key || seen.has(item.key)) return false;
            seen.add(item.key);
            return true;
        });
    }
    const opticalItems = uniqueItems(data.opticalItems);
    const saxCategories = uniqueItems(data.saxCategories);
    const itemsByKey = new Map(opticalItems.map(function (item) { return [item.key, item]; }));
    const list = form.querySelector('[data-home-sections-list]');
    const page = preview.querySelector('[data-preview-page]');
    const sectionsCanvas = preview.querySelector('[data-preview-sections]');
    const state = preview.querySelector('[data-preview-state]');
    const languageSelect = preview.querySelector('[data-preview-language]');
    const activeCount = preview.querySelector('[data-preview-active-count]');
    const categoryCount = preview.querySelector('[data-preview-category-count]');
    const tip = preview.querySelector('[data-preview-tip]');
    if (!list || !page || !sectionsCanvas || !state || !languageSelect || !activeCount || !categoryCount || !tip) return;
    const initialLocale = (preview.dataset.locale || 'es').slice(0, 2);
    let dirty = false;
    let submitting = false;
    let renderFrame = null;

    languageSelect.value = ['pt', 'es', 'en'].includes(initialLocale) ? initialLocale : 'es';

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalize(value) {
        return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    }

    function elementsNamed(name) {
        return Array.from(form.elements).filter(function (element) { return element.name === name; });
    }

    function value(name, fallback) {
        const element = elementsNamed(name)[0];
        return element ? element.value : (fallback || '');
    }

    function isEnabled(key) {
        return elementsNamed('sections[' + key + '][enabled]').some(function (element) {
            return element.type === 'checkbox' && element.checked;
        });
    }

    function sectionContent(key) {
        const language = languageSelect.value;
        const row = list.querySelector('[data-section-key="' + key + '"]');
        const fallback = row?.querySelector('.home-section-copy label')?.textContent.trim() || key;
        return {
            title: value('sections[' + key + '][content][' + language + '][title]', fallback),
            description: value('sections[' + key + '][content][' + language + '][description]', '')
        };
    }

    function selectedItems(key) {
        return elementsNamed('sections[' + key + '][optical_item_keys][]')
            .filter(function (element) { return element.checked; })
            .map(function (element) { return itemsByKey.get(element.value); })
            .filter(Boolean);
    }

    function automaticAudienceItems() {
        const children = opticalItems.filter(function (item) { return item.type === 'childcategory'; });
        const used = new Set();
        function pick(terms, rejectInfantil) {
            let item = children.find(function (candidate) {
                const label = normalize(candidate.label);
                return !used.has(candidate.key)
                    && terms.some(function (term) { return label.includes(term); })
                    && (!rejectInfantil || !label.includes('infantil'));
            });
            item = item || opticalItems.find(function (candidate) { return !used.has(candidate.key); });
            if (item) used.add(item.key);
            return item;
        }
        return [
            pick(['femenino', 'feminino', 'mujer']),
            pick(['masculino', 'homem', 'hombre'], true),
            pick(['infantil', 'nino', 'nina', 'crianca'])
        ].filter(Boolean);
    }

    function topCategoryItems(layout) {
        const source = layout === 'vista' ? opticalItems : saxCategories;
        const selected = layout === 'vista' ? selectedItems('categories') : [];
        if (selected.length) return selected;
        const limit = value('sections[categories][category_limit]', 'all');
        return limit === 'all' ? source : source.slice(0, Number(limit) || source.length);
    }

    function countClass(count) {
        return count >= 5 ? 'many' : String(Math.max(1, count));
    }

    function image(item, contain) {
        const className = contain ? ' home-preview-tile--contain' : '';
        const media = item.image
            ? '<img src="' + escapeHtml(item.image) + '" alt="">'
            : '<i class="fa-solid fa-glasses" aria-hidden="true"></i>';
        return '<span class="home-preview-tile' + className + '">' + media + '<span>' + escapeHtml(item.label) + '</span></span>';
    }

    function heading(content) {
        if (!content.title && !content.description) return '';
        return '<span class="home-preview-copy"><strong>' + escapeHtml(content.title) + '</strong><small>' + escapeHtml(content.description) + '</small></span>';
    }

    function productBlock(content) {
        return heading(content) + '<span class="home-preview-products">' + '<i class="home-preview-product"></i>'.repeat(4) + '</span>';
    }

    function sectionMarkup(key, layout) {
        const content = sectionContent(key);
        const label = '<span class="home-preview-block__label">Clique para editar</span>';
        let body = '';

        if (key === 'main_slider') {
            body = '<span class="home-preview-hero"><i></i><strong>' + escapeHtml(content.title || 'Campanha principal') + '</strong></span>';
        } else if (key === 'categories') {
            const items = topCategoryItems(layout);
            const visible = items.slice(0, 8);
            body = heading(content) + '<span class="home-preview-grid home-preview-grid--' + countClass(items.length) + '">'
                + visible.map(function (item) { return image(item, true); }).join('')
                + (items.length > visible.length ? '<span class="home-preview-grid__more">+' + (items.length - visible.length) + '</span>' : '')
                + '</span>';
        } else if (key === 'exclusive_collection') {
            if (layout === 'vista') {
                const items = selectedItems(key);
                const audiences = items.length ? items : automaticAudienceItems();
                body = '<span class="home-preview-audiences home-preview-audiences--' + countClass(audiences.length) + '">'
                    + audiences.slice(0, 8).map(function (item) {
                        const media = item.image ? '<img src="' + escapeHtml(item.image) + '" alt="">' : '';
                        return '<span class="home-preview-audience">' + media + '<span>' + escapeHtml(item.label) + '</span></span>';
                    }).join('') + '</span>';
            } else {
                const tags = saxCategories.slice(0, 3).map(function (item) { return '<i>' + escapeHtml(item.label) + '</i>'; }).join('');
                body = '<span class="home-preview-exclusive"><span><small>Curadoria SAX</small><strong>'
                    + escapeHtml(content.title) + '</strong><em>' + escapeHtml(content.description) + '</em><span class="home-preview-exclusive__tags">'
                    + tags + '</span></span><b></b></span>';
            }
        } else if (['recent_products', 'most_viewed', 'featured_products'].includes(key)) {
            body = productBlock(content);
        } else if (key === 'editorial_banners') {
            body = heading(content) + '<span class="home-preview-editorials"><span>Editorial 01</span><span>Editorial 02</span></span>';
        } else if (key === 'brands') {
            body = heading(content) + '<span class="home-preview-brands"><span>Brand</span><span>Marca</span><span>Logo</span><span>SAX</span></span>';
        } else if (key === 'help') {
            const language = languageSelect.value;
            const labels = [0, 1, 2].map(function (index) {
                return value('sections[help][items][' + index + '][' + language + ']', 'Benefício');
            });
            body = '<span class="home-preview-benefits">' + labels.map(function (text) { return '<span>' + escapeHtml(text) + '</span>'; }).join('') + '</span>';
        } else if (key === 'newsletter') {
            body = '<span class="home-preview-newsletter"><strong>' + escapeHtml(content.title) + '</strong><span></span></span>';
        } else {
            body = heading(content);
        }

        return '<button type="button" class="home-preview-block" data-preview-section="' + escapeHtml(key) + '">' + label + body + '</button>';
    }

    function updateTip(activeKeys, categories, layout) {
        let mode = '';
        let title = 'Composição equilibrada';
        let message = 'A ordem está clara. Clique nos blocos da prévia para chegar rapidamente à configuração.';

        if (!activeKeys.length) {
            mode = 'is-warning';
            title = 'A Home ficará vazia';
            message = 'Ative pelo menos uma seção antes de salvar.';
        } else if (!activeKeys.includes('main_slider')) {
            mode = 'is-warning';
            title = 'Sem abertura visual';
            message = 'Considere manter o slider ou outro destaque forte como primeira seção.';
        } else if (categories.length > 8) {
            mode = 'is-warning';
            title = 'Muitas categorias no topo';
            message = 'Entre 3 e 6 opções costuma facilitar a escolha e reduzir a rolagem.';
        } else if (layout === 'vista' && (selectedItems('categories').length || selectedItems('exclusive_collection').length)) {
            mode = 'is-success';
            title = 'Curadoria personalizada';
            message = 'As categorias escolhidas manualmente serão mantidas exatamente nesta composição.';
        }

        tip.className = 'home-preview-tip ' + mode;
        tip.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i><p><strong>' + escapeHtml(title) + '</strong><span>' + escapeHtml(message) + '</span></p>';
    }

    function render() {
        const layout = form.querySelector('input[name="storefront_layout"]:checked')?.value || 'sax';
        const rows = Array.from(list.querySelectorAll('[data-home-section]'));
        const activeKeys = rows.map(function (row) { return row.dataset.sectionKey; }).filter(isEnabled);
        const categories = topCategoryItems(layout);

        page.classList.toggle('is-vista', layout === 'vista');
        preview.querySelector('[data-preview-brand]').textContent = layout === 'vista' ? 'VISTA & CO' : 'SAX';
        sectionsCanvas.innerHTML = activeKeys.map(function (key) { return sectionMarkup(key, layout); }).join('');
        activeCount.textContent = activeKeys.length;
        categoryCount.textContent = categories.length;
        updateTip(activeKeys, categories, layout);
        form.querySelectorAll('[data-layout-only]').forEach(function (element) {
            element.hidden = element.dataset.layoutOnly !== layout;
        });
    }

    function scheduleRender() {
        if (renderFrame) cancelAnimationFrame(renderFrame);
        renderFrame = requestAnimationFrame(function () {
            renderFrame = null;
            render();
        });
    }

    function markDirty() {
        if (dirty) return;
        dirty = true;
        state.classList.add('is-dirty');
        state.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Não salvo';
    }

    form.addEventListener('input', function (event) {
        if (!event.target.name) return;
        markDirty();
        scheduleRender();
    });
    form.addEventListener('change', function (event) {
        if (!event.target.name) return;
        markDirty();
        scheduleRender();
    });
    form.addEventListener('click', function (event) {
        if (event.target.closest('[data-move],[data-optical-clear]')) {
            markDirty();
            requestAnimationFrame(scheduleRender);
        }
    });
    form.addEventListener('submit', function () {
        submitting = true;
        state.classList.remove('is-dirty');
        state.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Salvando';
        requestAnimationFrame(function () {
            document.querySelectorAll('button[type="submit"][form="sectionsForm"],#sectionsForm button[type="submit"]').forEach(function (button) {
                button.disabled = true;
            });
        });
    });

    new MutationObserver(function () {
        markDirty();
        scheduleRender();
    }).observe(list, {childList: true});

    preview.querySelectorAll('[data-preview-device]').forEach(function (button) {
        button.addEventListener('click', function () {
            preview.querySelectorAll('[data-preview-device]').forEach(function (item) { item.classList.toggle('is-active', item === button); });
            page.classList.toggle('is-mobile', button.dataset.previewDevice === 'mobile');
        });
    });
    languageSelect.addEventListener('change', render);

    sectionsCanvas.addEventListener('click', function (event) {
        const previewSection = event.target.closest('[data-preview-section]');
        if (!previewSection) return;
        const row = list.querySelector('[data-section-key="' + previewSection.dataset.previewSection + '"]');
        if (!row) return;
        row.querySelector('details')?.setAttribute('open', '');
        row.classList.add('is-preview-target');
        row.scrollIntoView({behavior: 'smooth', block: 'center'});
        window.setTimeout(function () { row.classList.remove('is-preview-target'); }, 1600);
    });

    window.addEventListener('beforeunload', function (event) {
        if (!dirty || submitting) return;
        event.preventDefault();
        event.returnValue = '';
    });

    render();
})();
