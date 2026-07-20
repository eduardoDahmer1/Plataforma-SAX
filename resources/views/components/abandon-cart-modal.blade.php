<div class="modal fade" id="abandonCartFeedbackModal" tabindex="-1" aria-labelledby="abandonCartFeedbackTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('cart.abandon') }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header border-0 pb-0">
                    <div>
                        <small class="text-uppercase text-muted fw-bold">{{ __('messages.cart_abandon_eyebrow') }}</small>
                        <h2 class="modal-title h4 fw-bold mt-1" id="abandonCartFeedbackTitle">{{ __('messages.cart_abandon_title') }}</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.cart_abandon_close') }}"></button>
                </div>
                <div class="modal-body pt-3">
                    <p class="text-muted small">{{ __('messages.cart_abandon_intro') }}</p>
                    @php
                        $abandonReasons = [
                            'payment' => [__('messages.cart_abandon_reason_payment_title'), __('messages.cart_abandon_reason_payment_description')],
                            'shipping_price' => [__('messages.cart_abandon_reason_shipping_title'), __('messages.cart_abandon_reason_shipping_description')],
                            'later' => [__('messages.cart_abandon_reason_later_title'), __('messages.cart_abandon_reason_later_description')],
                            'help' => [__('messages.cart_abandon_reason_help_title'), __('messages.cart_abandon_reason_help_description')],
                        ];
                    @endphp
                    <div class="d-grid gap-2">
                        @foreach($abandonReasons as $value => [$title, $description])
                            <label class="border rounded-3 p-3 d-flex gap-3 align-items-start" style="cursor:pointer">
                                <input class="form-check-input mt-1" type="radio" name="abandon_reason" value="{{ $value }}" required>
                                <span><strong class="d-block">{{ $title }}</strong><small class="text-muted">{{ $description }}</small></span>
                            </label>
                        @endforeach
                        <label class="border rounded-3 p-3 d-flex gap-3 align-items-center" style="cursor:pointer">
                            <input class="form-check-input" type="radio" name="abandon_reason" value="no_answer" required>
                            <span class="fw-bold text-muted">{{ __('messages.cart_abandon_reason_no_answer') }}</span>
                        </label>
                    </div>
                    <div class="mt-3">
                        <label for="abandonCartMessage" class="form-label fw-bold">{{ __('messages.cart_abandon_tell_more') }} <span class="text-muted fw-normal">({{ __('messages.cart_abandon_optional') }})</span></label>
                        <textarea id="abandonCartMessage" name="abandon_message" class="form-control" rows="4" maxlength="1500" placeholder="{{ __('messages.cart_abandon_placeholder') }}"></textarea>
                    </div>
                    <div class="alert alert-light border small mt-3 mb-0">{{ __('messages.cart_abandon_history_notice') }}</div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cart_abandon_continue') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('messages.cart_abandon_submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #abandonCartFeedbackModal { z-index: 20050 !important; }
    #abandonCartFeedbackModal .modal-dialog { max-height: calc(100vh - 1.5rem); }
    #abandonCartFeedbackModal .modal-content { max-height: calc(100vh - 1.5rem); }
    #abandonCartFeedbackModal .modal-content > form { display:flex; flex-direction:column; min-height:0; max-height:calc(100vh - 1.5rem); }
    #abandonCartFeedbackModal .modal-body { flex:1 1 auto; min-height:0; overflow-y:auto; }
    body:has(#abandonCartFeedbackModal.show) .modal-backdrop { z-index: 20040 !important; }
    @media (max-width: 575.98px) {
        #abandonCartFeedbackModal .modal-dialog { margin: .5rem; max-height: calc(100vh - 1rem); }
        #abandonCartFeedbackModal .modal-content { max-height: calc(100vh - 1rem); }
        #abandonCartFeedbackModal .modal-header,
        #abandonCartFeedbackModal .modal-body,
        #abandonCartFeedbackModal .modal-footer { padding-left: 1rem; padding-right: 1rem; }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('abandonCartFeedbackModal');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });
</script>
