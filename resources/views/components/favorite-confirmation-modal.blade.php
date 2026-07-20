<div class="modal fade sax-favorite-modal" id="favoriteConfirmationModal" tabindex="-1"
     aria-labelledby="favoriteConfirmationTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-0 shadow-lg">
            <div class="sax-favorite-modal__accent"></div>
            <div class="modal-body text-center px-4 px-sm-5 py-4">
                <button type="button" class="btn-close sax-favorite-modal__close" data-bs-dismiss="modal"
                        aria-label="{{ __('messages.favorite_confirm_no') }}"></button>
                <div class="sax-favorite-modal__icon mx-auto mb-3">
                    <i class="far fa-heart"></i>
                </div>
                <span class="sax-favorite-modal__eyebrow">{{ __('messages.favorite_confirm_eyebrow') }}</span>
                <h2 class="h4 mt-2 mb-2" id="favoriteConfirmationTitle"></h2>
                <p class="text-muted mb-4" id="favoriteConfirmationMessage"></p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-dark rounded-0 py-3 text-uppercase fw-bold"
                            id="favoriteConfirmationSubmit"></button>
                    <button type="button" class="btn btn-link text-dark text-decoration-none"
                            data-bs-dismiss="modal">{{ __('messages.favorite_confirm_no') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .sax-favorite-modal { z-index: 20100; }
    .sax-favorite-modal__accent { height: 3px; background: #c79a4b; }
    .sax-favorite-modal__close { position: absolute; top: 1rem; right: 1rem; }
    .sax-favorite-modal__icon {
        width: 64px; height: 64px; border: 1px solid #c79a4b; border-radius: 50%;
        display: grid; place-items: center; color: #b98736; font-size: 1.4rem;
    }
    .sax-favorite-modal__eyebrow {
        color: #b17f31; font-size: .7rem; font-weight: 700; letter-spacing: .18em; text-transform: uppercase;
    }
    .sax-favorite-modal h2 { font-family: Georgia, 'Times New Roman', serif; color: #111827; }
    @media (max-width: 575.98px) {
        .sax-favorite-modal .modal-dialog { margin: 1rem; }
        .sax-favorite-modal .modal-body { padding-top: 2rem !important; }
    }
</style>

<script>
    window.saxFavoriteConfirmation = {
        addTitle: @json(__('messages.favorite_confirm_add_title')),
        removeTitle: @json(__('messages.favorite_confirm_remove_title')),
        addMessage: @json(__('messages.favorite_confirm_add_message')),
        removeMessage: @json(__('messages.favorite_confirm_remove_message')),
        addButton: @json(__('messages.favorite_confirm_add_button')),
        removeButton: @json(__('messages.favorite_confirm_remove_button'))
    };
</script>
