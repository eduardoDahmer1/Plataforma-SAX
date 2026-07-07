{{-- Modal global de confirmación (única instancia, en layout/admin).
     Se dispara desde cualquier <form data-confirm="mensaje"> — la delegación vive en admin.js.
     Si el modal no está presente, admin.js cae al confirm() nativo. --}}
<div class="modal fade" id="saxConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-3">
            <div class="modal-body text-center p-4">
                <div class="mb-3 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <p class="fw-bold mb-2 text-uppercase letter-spacing-1 small">Tem certeza?</p>
                <p class="small text-muted mb-0" id="saxConfirmMessage"></p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2 pt-0 pb-4">
                <button type="button" class="btn btn-action-sax x-small fw-bold text-uppercase px-4" data-bs-dismiss="modal">
                    {{ __('messages.cancel_btn') }}
                </button>
                <button type="button" class="btn btn-danger x-small fw-bold text-uppercase px-4" id="saxConfirmAccept">
                    <i class="fas fa-check me-1"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
