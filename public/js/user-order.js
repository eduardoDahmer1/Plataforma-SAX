document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('imgModal');
    const image = document.getElementById('modalImg');

    document.querySelectorAll('[data-receipt-preview]').forEach(function (preview) {
        preview.addEventListener('click', function () {
            if (!modal || !image) return;
            image.src = preview.dataset.receiptPreview || '';
            modal.style.display = 'flex';
        });
    });

    modal?.addEventListener('click', function () {
        modal.style.display = 'none';
        if (image) image.src = '';
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal?.style.display === 'flex') {
            modal.style.display = 'none';
            if (image) image.src = '';
        }
    });
});
