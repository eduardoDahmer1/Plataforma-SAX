@php
    $paymentProofModalCopy = match (app()->getLocale()) {
        'es' => [
            'title' => 'Ver comprobante',
            'close' => 'Cerrar',
            'download' => 'Descargar archivo',
            'pdf_title' => 'Vista previa del comprobante PDF',
        ],
        'en' => [
            'title' => 'View payment proof',
            'close' => 'Close',
            'download' => 'Download file',
            'pdf_title' => 'PDF payment proof preview',
        ],
        default => [
            'title' => 'Visualizar comprovante',
            'close' => 'Fechar',
            'download' => 'Baixar arquivo',
            'pdf_title' => 'Visualização do comprovante em PDF',
        ],
    };
@endphp

<div class="modal fade" id="paymentProofPreviewModal" tabindex="-1" aria-labelledby="paymentProofPreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <div class="min-w-0">
                    <h2 class="modal-title fs-6 fw-bold" id="paymentProofPreviewTitle">{{ $paymentProofModalCopy['title'] }}</h2>
                    <small class="d-block text-white-50 text-truncate" data-payment-proof-name></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ $paymentProofModalCopy['close'] }}"></button>
            </div>
            <div class="modal-body d-flex align-items-center justify-content-center bg-light p-2 p-md-4" style="min-height: 280px;">
                <img class="img-fluid rounded shadow-sm" data-payment-proof-image hidden alt="" style="max-height: 72vh; object-fit: contain;">
                <iframe class="w-100 border-0 rounded bg-white" data-payment-proof-pdf hidden title="{{ $paymentProofModalCopy['pdf_title'] }}" style="height: 72vh;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ $paymentProofModalCopy['close'] }}</button>
                <a href="#" class="btn btn-dark" data-payment-proof-download download>
                    <i class="fa-solid fa-download me-2" aria-hidden="true"></i>{{ $paymentProofModalCopy['download'] }}
                </a>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script src="{{ asset('js/payment-proof-preview.js') }}?v={{ filemtime(public_path('js/payment-proof-preview.js')) }}"></script>
    @endpush
@endonce
