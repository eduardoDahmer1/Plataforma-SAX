document.addEventListener('DOMContentLoaded', function () {
    const bulkForm = document.getElementById('dhl-product-bulk-form');

    if (bulkForm) {
        const selectPage = document.querySelector('[data-select-page]');
        const productChecks = Array.from(document.querySelectorAll('[data-product-select]'));
        const counter = bulkForm.querySelector('[data-selection-count]');
        const action = bulkForm.querySelector('[data-bulk-action]');
        const profileWrap = bulkForm.querySelector('[data-bulk-profile]');
        const dimensionWrap = bulkForm.querySelector('[data-bulk-dimensions]');
        const submit = bulkForm.querySelector('[data-bulk-submit]');
        const profile = profileWrap?.querySelector('select');
        const dimensions = Array.from(dimensionWrap?.querySelectorAll('input') || []);

        const updateSelection = function () {
            const selected = productChecks.filter((checkbox) => checkbox.checked).length;
            counter.textContent = selected + (selected === 1 ? ' selecionado' : ' selecionados');
            submit.disabled = selected === 0 || action.value === '';

            if (selectPage) {
                selectPage.checked = selected > 0 && selected === productChecks.length;
                selectPage.indeterminate = selected > 0 && selected < productChecks.length;
            }
        };

        const updateAction = function () {
            const needsProfile = action.value === 'apply_profile';
            const needsDimensions = action.value === 'set_exact';

            profileWrap.hidden = !needsProfile;
            if (profile) profile.required = needsProfile;

            dimensionWrap.hidden = !needsDimensions;
            dimensions.forEach((input) => { input.required = needsDimensions; });
            updateSelection();
        };

        selectPage?.addEventListener('change', function () {
            productChecks.forEach((checkbox) => { checkbox.checked = selectPage.checked; });
            updateSelection();
        });
        productChecks.forEach((checkbox) => checkbox.addEventListener('change', updateSelection));
        action.addEventListener('change', updateAction);

        bulkForm.addEventListener('submit', function (event) {
            const selected = productChecks.filter((checkbox) => checkbox.checked).length;
            if (selected === 0) {
                event.preventDefault();
                return;
            }

            const confirmations = {
                clear_exact: 'Limpar as medidas reais dos produtos selecionados?',
                clear_profile: 'Remover o perfil fixado e voltar ao cálculo automático?',
                mark_review: 'Marcar os produtos selecionados como restritos e sujeitos à revisão?',
            };
            if (confirmations[action.value] && !window.confirm(confirmations[action.value])) {
                event.preventDefault();
                return;
            }

            submit.disabled = true;
        });

        updateAction();
    }

    const category = document.getElementById('dhl-category-filter');
    const subcategory = document.getElementById('dhl-subcategory-filter');
    const child = document.getElementById('dhl-childcategory-filter');

    const filterOptions = function (select, predicate) {
        if (!select) return;
        Array.from(select.options).forEach(function (option, index) {
            if (index === 0) return;
            const visible = predicate(option);
            option.hidden = !visible;
            option.disabled = !visible;
        });
    };

    const updateTaxonomy = function () {
        const categoryId = category?.value || '';
        const subcategoryId = subcategory?.value || '';

        filterOptions(subcategory, (option) => !categoryId || option.dataset.category === categoryId);
        filterOptions(child, (option) => {
            if (subcategoryId) return option.dataset.subcategory === subcategoryId;
            return !categoryId || option.dataset.category === categoryId;
        });
    };

    category?.addEventListener('change', function () {
        if (subcategory) subcategory.value = '';
        if (child) child.value = '';
        updateTaxonomy();
    });
    subcategory?.addEventListener('change', function () {
        if (child) child.value = '';
        updateTaxonomy();
    });
    updateTaxonomy();
});
