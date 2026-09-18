(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const app = document.getElementById('activate-app');
        if (!app) return;

        const storageKey = 'sax.activate.preferences.v2';
        const search = document.getElementById('activate-search');
        const searchClear = document.getElementById('activateSearchClear');
        const statusButtons = Array.from(app.querySelectorAll('.activate-status-chip'));
        const letterButtons = Array.from(app.querySelectorAll('[data-letter]'));
        const scopeSelect = document.getElementById('activateScope');
        const sortSelect = document.getElementById('activateSort');
        const perPageSelect = document.getElementById('activatePerPage');
        const resetButton = document.getElementById('activateResetFilters');
        const collapseAllButton = document.getElementById('activateCollapseAll');
        const resultTotal = document.getElementById('activateResultTotal');
        const feedback = document.getElementById('activate-feedback');
        const sections = Array.from(app.querySelectorAll('[data-section]'));
        const pages = new Map(sections.map(function (section) { return [section.dataset.section, 1]; }));
        let feedbackTimer = null;
        let status = 'all';
        let letter = 'all';

        function loadPreferences() {
            try {
                return JSON.parse(localStorage.getItem(storageKey) || '{}');
            } catch (error) {
                return {};
            }
        }

        const preferences = loadPreferences();
        if (['20', '30', '40', '50', '100'].includes(String(preferences.perPage))) {
            perPageSelect.value = String(preferences.perPage);
        }
        if (['asc', 'desc'].includes(preferences.sort)) sortSelect.value = preferences.sort;
        if (['all', 'category', 'brand'].includes(preferences.scope)) scopeSelect.value = preferences.scope;

        function savePreferences() {
            const collapsed = sections.filter(function (section) {
                return section.classList.contains('is-collapsed');
            }).map(function (section) { return section.dataset.section; });
            localStorage.setItem(storageKey, JSON.stringify({
                perPage: perPageSelect.value,
                sort: sortSelect.value,
                scope: scopeSelect.value,
                collapsed: collapsed
            }));
        }

        function normalize(value) {
            return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        }

        function initialLetter(value) {
            const first = normalize(value).charAt(0).toUpperCase();
            return /^[A-Z]$/.test(first) ? first : '#';
        }

        sections.forEach(function (section) {
            Array.from(section.querySelectorAll('.ai')).forEach(function (item, index) {
                item._label = item.querySelector('.ai-n').textContent.trim();
                item._name = normalize(item._label);
                item._letter = initialLetter(item._label);
                item._index = index;
            });
        });

        function notify(message, error) {
            feedback.textContent = message;
            feedback.classList.toggle('is-error', Boolean(error));
            clearTimeout(feedbackTimer);
            feedbackTimer = window.setTimeout(function () { feedback.textContent = ''; }, 2600);
        }

        function matchesBase(item, term) {
            const matchesSearch = !term || item._name.includes(term);
            const matchesStatus = status === 'all' || item.dataset.s === status;
            return matchesSearch && matchesStatus;
        }

        function resetPages() {
            sections.forEach(function (section) { pages.set(section.dataset.section, 1); });
        }

        function updateAvailableLetters(term) {
            const available = new Set();
            sections.forEach(function (section) {
                const inScope = scopeSelect.value === 'all' || scopeSelect.value === section.dataset.section;
                if (!inScope) return;
                section.querySelectorAll('.ai').forEach(function (item) {
                    if (matchesBase(item, term)) available.add(item._letter);
                });
            });
            letterButtons.forEach(function (button) {
                if (button.dataset.letter === 'all') return;
                button.disabled = !available.has(button.dataset.letter);
            });
        }

        function render() {
            const term = normalize(search.value.trim());
            const perPage = Number(perPageSelect.value);
            const descending = sortSelect.value === 'desc';
            let globalTotal = 0;

            searchClear.hidden = search.value.length === 0;
            updateAvailableLetters(term);

            sections.forEach(function (section) {
                const sectionType = section.dataset.section;
                const inScope = scopeSelect.value === 'all' || scopeSelect.value === sectionType;
                section.hidden = !inScope;
                if (!inScope) return;

                const grid = section.querySelector('.sax-activate__grid');
                const items = Array.from(section.querySelectorAll('.ai'));
                const filtered = items.filter(function (item) {
                    return matchesBase(item, term) && (letter === 'all' || item._letter === letter);
                }).sort(function (left, right) {
                    const result = left._label.localeCompare(right._label, undefined, { sensitivity: 'base', numeric: true });
                    return descending ? -result : result;
                });

                items.forEach(function (item) { item.hidden = true; });
                filtered.forEach(function (item) { grid.appendChild(item); });

                const pageCount = Math.max(1, Math.ceil(filtered.length / perPage));
                const currentPage = Math.min(pages.get(sectionType) || 1, pageCount);
                pages.set(sectionType, currentPage);
                const start = (currentPage - 1) * perPage;
                filtered.slice(start, start + perPage).forEach(function (item) { item.hidden = false; });

                globalTotal += filtered.length;
                section.querySelector('[data-visible-count]').textContent = filtered.length;
                section.querySelector('[data-empty]').hidden = filtered.length > 0;

                const pagination = section.querySelector('[data-pagination]');
                pagination.hidden = filtered.length === 0 || pageCount <= 1;
                pagination.querySelector('[data-page-prev]').disabled = currentPage <= 1;
                pagination.querySelector('[data-page-next]').disabled = currentPage >= pageCount;
                pagination.querySelector('[data-page-label]').textContent = currentPage + ' de ' + pageCount;
            });

            resultTotal.textContent = globalTotal;
        }

        function setStatus(nextStatus) {
            status = nextStatus;
            statusButtons.forEach(function (button) {
                button.classList.toggle('is-on', button.dataset.filter === status);
            });
            resetPages();
            render();
        }

        function setLetter(nextLetter) {
            letter = nextLetter;
            letterButtons.forEach(function (button) {
                button.classList.toggle('is-on', button.dataset.letter === letter);
            });
            resetPages();
            render();
        }

        function setCollapsed(section, collapsed, persist) {
            const body = section.querySelector('[data-section-body]');
            const button = section.querySelector('[data-collapse]');
            section.classList.toggle('is-collapsed', collapsed);
            body.hidden = collapsed;
            button.setAttribute('aria-expanded', String(!collapsed));
            if (persist !== false) savePreferences();
            updateCollapseAllButton();
        }

        function updateCollapseAllButton() {
            const visibleSections = sections.filter(function (section) { return !section.hidden; });
            const allCollapsed = visibleSections.length > 0 && visibleSections.every(function (section) {
                return section.classList.contains('is-collapsed');
            });
            collapseAllButton.dataset.collapsed = allCollapsed ? '1' : '0';
            collapseAllButton.innerHTML = allCollapsed
                ? '<i class="fa-solid fa-expand"></i><span>Expandir tudo</span>'
                : '<i class="fa-solid fa-compress"></i><span>Minimizar tudo</span>';
        }

        search.addEventListener('input', function () { resetPages(); render(); });
        searchClear.addEventListener('click', function () { search.value = ''; search.focus(); resetPages(); render(); });
        statusButtons.forEach(function (button) {
            button.addEventListener('click', function () { setStatus(button.dataset.filter); });
        });
        letterButtons.forEach(function (button) {
            button.addEventListener('click', function () { if (!button.disabled) setLetter(button.dataset.letter); });
        });
        [scopeSelect, sortSelect, perPageSelect].forEach(function (select) {
            select.addEventListener('change', function () { resetPages(); savePreferences(); render(); updateCollapseAllButton(); });
        });

        resetButton.addEventListener('click', function () {
            search.value = '';
            scopeSelect.value = 'all';
            sortSelect.value = 'asc';
            perPageSelect.value = '20';
            setStatus('all');
            setLetter('all');
            savePreferences();
            updateCollapseAllButton();
        });

        collapseAllButton.addEventListener('click', function () {
            const collapse = collapseAllButton.dataset.collapsed !== '1';
            sections.filter(function (section) { return !section.hidden; }).forEach(function (section) {
                setCollapsed(section, collapse, false);
            });
            savePreferences();
            updateCollapseAllButton();
        });

        sections.forEach(function (section) {
            section.querySelector('[data-collapse]').addEventListener('click', function () {
                setCollapsed(section, !section.classList.contains('is-collapsed'));
            });
            section.querySelector('[data-page-prev]').addEventListener('click', function () {
                pages.set(section.dataset.section, Math.max(1, (pages.get(section.dataset.section) || 1) - 1));
                render();
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            section.querySelector('[data-page-next]').addEventListener('click', function () {
                pages.set(section.dataset.section, (pages.get(section.dataset.section) || 1) + 1);
                render();
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        app.addEventListener('click', function (event) {
            const toggle = event.target.closest('.sax-toggle');
            if (!toggle) return;

            const item = toggle.closest('.ai');
            const section = toggle.closest('[data-section]');
            const wasActive = toggle.classList.contains('is-on');
            const url = app.dataset.toggleUrl
                .replace('__TYPE__', section.dataset.section)
                .replace('__ID__', toggle.dataset.id);
            toggle.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ active: !wasActive })
            }).then(function (response) {
                return response.ok ? response.json() : Promise.reject();
            }).then(function (data) {
                toggle.classList.toggle('is-on', data.ativo);
                toggle.setAttribute('aria-pressed', String(data.ativo));
                toggle.setAttribute('aria-label', (data.ativo ? 'Desativar ' : 'Ativar ') + item._label);
                item.dataset.s = String(data.status);
                item.querySelector('.ai-status').textContent = data.ativo ? 'Ativa' : 'Inativa';

                const activeCount = section.querySelector('[data-count-active]');
                activeCount.textContent = Number(activeCount.textContent) + (data.ativo ? 1 : -1);
                const heroCount = app.querySelector('[data-hero-' + section.dataset.section + '-active]');
                if (heroCount) heroCount.textContent = Number(heroCount.textContent) + (data.ativo ? 1 : -1);

                notify(data.message, false);
                render();
            }).catch(function () {
                notify(app.dataset.errorMessage, true);
            }).finally(function () { toggle.disabled = false; });
        });

        (Array.isArray(preferences.collapsed) ? preferences.collapsed : []).forEach(function (type) {
            const section = sections.find(function (candidate) { return candidate.dataset.section === type; });
            if (section) setCollapsed(section, true, false);
        });
        render();
        updateCollapseAllButton();
    });
})();
