document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('paymentProofPreviewModal');
    if (!modal) return;

    const modalTitle = modal.querySelector('[data-payment-proof-name]');
    const image = modal.querySelector('[data-payment-proof-image]');
    const pdf = modal.querySelector('[data-payment-proof-pdf]');
    const download = modal.querySelector('[data-payment-proof-download]');

    modal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const previewUrl = trigger.dataset.paymentProofPreview || '';
        const downloadUrl = trigger.dataset.paymentProofDownload || previewUrl;
        const fileName = trigger.dataset.paymentProofName || '';
        const isImage = trigger.dataset.paymentProofType === 'image';

        if (modalTitle) modalTitle.textContent = fileName;
        if (download) {
            download.href = downloadUrl;
            download.setAttribute('download', fileName);
        }

        if (image) {
            image.hidden = !isImage;
            image.alt = fileName;
            image.src = isImage ? previewUrl : '';
        }

        if (pdf) {
            pdf.hidden = isImage;
            pdf.src = isImage ? '' : previewUrl;
        }
    });

    modal.addEventListener('hidden.bs.modal', function () {
        if (image) image.src = '';
        if (pdf) pdf.src = '';
        if (download) {
            download.href = '#';
            download.setAttribute('download', '');
        }
        if (modalTitle) modalTitle.textContent = '';
    });
});
