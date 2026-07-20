@if(session('cart_feedback_notice'))
    @php
        $isLoginRequired = session('cart_feedback_notice') === 'login_required';
    @endphp

    <div class="modal fade sax-access-modal" id="cartFeedbackAccessModal" tabindex="-1" aria-labelledby="cartFeedbackAccessTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button type="button" class="sax-access-close" data-bs-dismiss="modal" aria-label="{{ __('messages.cart_feedback_close') }}">
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <div class="sax-access-mark" aria-hidden="true">
                    <span>S</span>
                </div>

                <span class="sax-access-eyebrow">{{ __('messages.cart_feedback_access_eyebrow') }}</span>
                <h2 id="cartFeedbackAccessTitle">
                    {{ $isLoginRequired ? __('messages.cart_feedback_login_title') : __('messages.cart_feedback_wrong_title') }}
                </h2>
                <p>
                    {{ $isLoginRequired ? __('messages.cart_feedback_login_required') : __('messages.cart_feedback_wrong_account') }}
                </p>

                <div class="sax-access-divider"><span></span></div>

                @if($isLoginRequired)
                    <a href="{{ route('home', ['open' => 'login']) }}" class="sax-access-primary">
                        {{ __('messages.cart_feedback_sign_in') }}
                        <i class="fa-solid fa-arrow-right-long"></i>
                    </a>
                @else
                    <button type="button" class="sax-access-primary" data-bs-dismiss="modal">
                        {{ __('messages.cart_feedback_understood') }}
                        <i class="fa-solid fa-check"></i>
                    </button>
                @endif

                <a href="{{ route('home') }}" class="sax-access-secondary">{{ __('messages.cart_feedback_continue_store') }}</a>
            </div>
        </div>
    </div>

    <style>
        .sax-access-modal { --sax-ink:#111927; --sax-gold:#b88a45; }
        .sax-access-modal .modal-dialog { width:min(92vw,500px); margin-right:auto; margin-left:auto; }
        .sax-access-modal .modal-content { position:relative; overflow:hidden; padding:44px 46px 38px; border:0; border-radius:0; color:var(--sax-ink); text-align:center; background:#fff; box-shadow:0 28px 90px rgba(8,15,27,.24); }
        .sax-access-modal .modal-content:before { content:""; position:absolute; top:0; left:0; width:100%; height:4px; background:linear-gradient(90deg,#8e672e,var(--sax-gold),#d7b678); }
        .sax-access-close { position:absolute; top:16px; right:16px; display:grid; width:36px; height:36px; place-items:center; border:0; border-radius:50%; color:#727985; background:#f4f3f0; transition:.2s ease; }
        .sax-access-close:hover { color:#fff; background:var(--sax-ink); transform:rotate(4deg); }
        .sax-access-mark { display:grid; width:58px; height:58px; place-items:center; margin:0 auto 20px; border:1px solid rgba(184,138,69,.65); border-radius:50%; color:var(--sax-gold); background:#faf8f3; }
        .sax-access-mark span { font:27px/1 Georgia,serif; }
        .sax-access-eyebrow { display:block; margin-bottom:12px; color:var(--sax-gold); font-size:10px; font-weight:700; letter-spacing:2.2px; text-transform:uppercase; }
        .sax-access-modal h2 { max-width:380px; margin:0 auto 14px; font:400 clamp(27px,4vw,35px)/1.12 Georgia,serif; letter-spacing:-.4px; }
        .sax-access-modal p { max-width:400px; margin:0 auto; color:#687181; font-size:14px; line-height:1.7; }
        .sax-access-divider { display:flex; justify-content:center; margin:24px 0; }
        .sax-access-divider:before,.sax-access-divider:after { content:""; width:42px; height:1px; margin-top:3px; background:#ddd8cf; }
        .sax-access-divider span { width:7px; height:7px; margin:0 10px; border:1px solid var(--sax-gold); transform:rotate(45deg); }
        .sax-access-primary { display:flex; width:100%; min-height:52px; justify-content:center; align-items:center; gap:13px; padding:14px 20px; border:1px solid var(--sax-ink); color:#fff; background:var(--sax-ink); font-size:11px; font-weight:700; letter-spacing:1.35px; text-decoration:none; text-transform:uppercase; transition:.25s ease; }
        .sax-access-primary:hover { border-color:var(--sax-gold); color:#fff; background:var(--sax-gold); }
        .sax-access-secondary { display:inline-block; margin-top:18px; color:#747b86; font-size:11px; font-weight:600; letter-spacing:.4px; text-decoration:none; }
        .sax-access-secondary:hover { color:var(--sax-gold); }
        .sax-access-modal + .modal-backdrop,.modal-backdrop.show { opacity:.58; }
        @media(max-width:575px) {
            .sax-access-modal .modal-dialog { width:calc(100% - 28px); margin:14px; }
            .sax-access-modal .modal-content { padding:38px 22px 28px; }
            .sax-access-mark { width:52px; height:52px; margin-bottom:17px; }
            .sax-access-modal h2 { font-size:28px; }
            .sax-access-modal p { font-size:13px; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalElement = document.getElementById('cartFeedbackAccessModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            }
        });
    </script>
@endif
