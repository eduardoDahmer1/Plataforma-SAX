(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const app = document.getElementById('msg-app');
        const bulkForm = document.getElementById('contactsBulkForm');
        if (!app || !bulkForm) return;

        const token = document.querySelector('meta[name="csrf-token"]').content;
        const selectAll = document.getElementById('contactsSelectAll');
        const checks = Array.from(app.querySelectorAll('[data-contact-check]'));
        const bulkButtons = Array.from(bulkForm.querySelectorAll('[data-bulk-action]'));
        const selectedCount = document.getElementById('contactsSelectedCount');
        const expandButton = document.getElementById('contactsExpandAll');
        const copyButton = document.getElementById('contactsCopyEmails');

        checks.forEach(function (checkbox) {
            checkbox.addEventListener('click', function (event) { event.stopPropagation(); });
            checkbox.closest('[data-no-toggle]')?.addEventListener('click', function (event) { event.stopPropagation(); });
            checkbox.addEventListener('change', syncSelection);
        });

        function selectedChecks() {
            return checks.filter(function (checkbox) { return checkbox.checked; });
        }

        function syncSelection() {
            const total = selectedChecks().length;
            selectedCount.textContent = total + (total === 1 ? ' selecionado' : ' selecionados');
            bulkButtons.forEach(function (button) { button.disabled = total === 0; });
            checks.forEach(function (checkbox) {
                checkbox.closest('[data-row]')?.classList.toggle('is-selected', checkbox.checked);
            });
            if (selectAll) {
                selectAll.checked = checks.length > 0 && total === checks.length;
                selectAll.indeterminate = total > 0 && total < checks.length;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checks.forEach(function (checkbox) { checkbox.checked = selectAll.checked; });
                syncSelection();
            });
        }

        bulkForm.addEventListener('submit', function (event) {
            if (event.submitter?.value !== 'delete') return;
            const total = selectedChecks().length;
            if (!window.confirm('Excluir definitivamente ' + total + ' contato(s) selecionado(s)?')) event.preventDefault();
        });

        if (copyButton) {
            copyButton.addEventListener('click', function () {
                const emails = selectedChecks().map(function (checkbox) {
                    return checkbox.closest('[data-row]')?.dataset.email;
                }).filter(Boolean).filter(function (email, index, all) { return all.indexOf(email) === index; });
                if (!emails.length) return;
                const copy = navigator.clipboard?.writeText
                    ? navigator.clipboard.writeText(emails.join('; '))
                    : Promise.reject();
                copy.then(function () {
                    copyButton.innerHTML = '<i class="fa-solid fa-check"></i> Copiados';
                    window.setTimeout(function () { copyButton.innerHTML = '<i class="fa-solid fa-copy"></i> Copiar e-mails'; }, 1800);
                }).catch(function () { window.prompt('Copie os e-mails:', emails.join('; ')); });
            });
        }

        function markAsRead(row) {
            if (!row.classList.contains('is-unread') || row.dataset.readPending === '1') return;
            row.dataset.readPending = '1';
            fetch(app.dataset.readUrl.replace('__ID__', row.dataset.id), {
                method: 'PATCH',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token }
            }).then(function (response) {
                if (!response.ok) throw new Error();
                row.classList.remove('is-unread');
                row.querySelector('.contacts-unread-dot')?.remove();
            }).catch(function () {
                // Mantém o destaque de não lida para que a ação possa ser tentada novamente.
            }).finally(function () { delete row.dataset.readPending; });
        }

        function setAllExpanded(expand) {
            app.querySelectorAll('[data-row]').forEach(function (row) {
                const detail = row.querySelector('.sax-msg__detail');
                const toggle = row.querySelector('[data-toggle]');
                if (!detail || !toggle) return;
                detail.hidden = !expand;
                row.classList.toggle('is-open', expand);
                toggle.setAttribute('aria-expanded', String(expand));
                if (expand) markAsRead(row);
            });
            expandButton.dataset.expanded = expand ? '1' : '0';
            expandButton.innerHTML = expand
                ? '<i class="fa-solid fa-angles-up"></i> Recolher todas'
                : '<i class="fa-solid fa-angles-down"></i> Abrir todas';
        }

        if (expandButton) {
            expandButton.addEventListener('click', function () { setAllExpanded(expandButton.dataset.expanded !== '1'); });
        }

        app.addEventListener('click', function (event) {
            if (event.target.closest('[data-no-toggle], [data-del], a, button')) return;
            const row = event.target.closest('[data-row]');
            if (row && row.classList.contains('is-open')) markAsRead(row);
        });
        app.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            const row = event.target.closest('[data-row]');
            window.setTimeout(function () {
                if (row?.classList.contains('is-open')) markAsRead(row);
            }, 0);
        });

        syncSelection();
    });
})();
