@extends('layout.layout')

@section('content')
@php
    $firstName = trim(explode(' ', $cart->user?->name ?? '')[0] ?? '');
    $displayTotal = (float) $cart->total * (float) ($cart->currency_value ?: 1);
    $selectedReason = old('reason', $reason ?? $cart->feedback_reason);
    $reasonIcons = [
        'later' => 'fa-regular fa-clock',
        'payment' => 'fa-regular fa-credit-card',
        'help' => 'fa-regular fa-circle-question',
        'gave_up' => 'fa-regular fa-heart',
        'other' => 'fa-regular fa-message',
    ];
@endphp

<section class="sax-care-page">
    <div class="sax-care-glow sax-care-glow-left"></div>
    <div class="sax-care-glow sax-care-glow-right"></div>

    <div class="sax-care-shell">
        <header class="sax-care-intro">
            <span class="sax-care-eyebrow">{{ __('messages.cart_feedback_eyebrow') }}</span>
            <h1>{{ $firstName ? $firstName . ', ' : '' }}{{ __('messages.cart_feedback_title') }}</h1>
            <p>{{ __('messages.cart_feedback_intro') }}</p>
        </header>

        <div class="sax-care-grid">
            <div class="sax-care-form-card">
                <div class="sax-card-heading">
                    <span>01</span>
                    <div>
                        <small>{{ __('messages.cart_feedback_experience') }}</small>
                        <h2>{{ __('messages.cart_feedback_how_help') }}</h2>
                    </div>
                </div>

                @if(session('success'))
                    <div class="sax-success-message" role="status">
                        <i class="fa-solid fa-check"></i>
                        <div>
                            <strong>{{ __('messages.cart_feedback_received') }}</strong>
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="sax-error-message" role="alert">
                        <strong>{{ __('messages.cart_feedback_review') }}</strong>
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('abandoned-cart.feedback.store', $cart->recovery_token) }}">
                    @csrf
                    <fieldset class="sax-reason-fieldset">
                        <legend>{{ __('messages.cart_feedback_choose') }}</legend>
                        <div class="sax-reason-list">
                            @foreach($reasons as $key => $label)
                                <label class="sax-reason-option">
                                    <input type="radio" name="reason" value="{{ $key }}" @checked($selectedReason === $key) required>
                                    <span class="sax-reason-icon"><i class="{{ $reasonIcons[$key] ?? 'fa-regular fa-message' }}"></i></span>
                                    <span class="sax-reason-text">{{ $label }}</span>
                                    <span class="sax-reason-check"><i class="fa-solid fa-check"></i></span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="sax-message-field">
                        <label for="feedback-message">{{ __('messages.cart_feedback_tell_more') }} <span>{{ __('messages.cart_feedback_optional') }}</span></label>
                        <textarea id="feedback-message" maxlength="1500" name="message" placeholder="{{ __('messages.cart_feedback_placeholder') }}">{{ old('message', session('success') ? $cart->feedback_message : '') }}</textarea>
                        <small>{{ __('messages.cart_feedback_privacy') }}</small>
                    </div>

                    <button class="sax-submit-button" type="submit">
                        <span>{{ __('messages.cart_feedback_send') }}</span>
                        <i class="fa-solid fa-arrow-right-long"></i>
                    </button>
                </form>
            </div>

            <aside class="sax-cart-summary">
                <div class="sax-summary-top">
                    <span class="sax-care-eyebrow">{{ __('messages.cart_feedback_selection') }}</span>
                    <span class="sax-cart-number">{{ __('messages.cart_feedback_cart_number', ['id' => $cart->id]) }}</span>
                </div>
                <h2>{{ __('messages.cart_feedback_chosen_items') }}</h2>

                <div class="sax-summary-items">
                    @forelse($cart->items->take(3) as $item)
                        @php
                            $image = $item->product?->photo_url
                                ?? ($item->image ? asset('storage/uploads/' . ltrim($item->image, '/')) : asset('storage/uploads/noimage.webp'));
                        @endphp
                        <div class="sax-summary-item">
                            <div class="sax-item-image"><img src="{{ $image }}" alt="{{ $item->product_name }}"></div>
                            <div class="sax-item-copy">
                                <strong>{{ $item->product_name }}</strong>
                                <span>{{ $item->sku ? 'SKU ' . $item->sku . ' · ' : '' }}{{ __('messages.cart_feedback_quantity', ['quantity' => $item->quantity]) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="sax-empty-summary">{{ __('messages.cart_feedback_empty') }}</p>
                    @endforelse
                </div>

                @if($cart->items->count() > 3)
                    <p class="sax-more-items">{{ trans_choice('messages.cart_feedback_more_items', $cart->items->count() - 3, ['count' => $cart->items->count() - 3]) }}</p>
                @endif

                <div class="sax-summary-total">
                    <span>{{ __('messages.cart_feedback_total') }}</span>
                    <strong>{{ $cart->currency_sign }} {{ number_format($displayTotal, 2, '.', ',') }}</strong>
                </div>

                <div class="sax-personal-care">
                    <span class="sax-care-monogram">S</span>
                    <div>
                        <strong>{{ __('messages.cart_feedback_count_on_us') }}</strong>
                        <p>{{ __('messages.cart_feedback_support_text') }}</p>
                        <a href="{{ route('contact.form') }}">{{ __('messages.cart_feedback_contact') }} <i class="fa-solid fa-arrow-right-long"></i></a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
    .sax-care-page { --ink:#101722; --muted:#667085; --gold:#b58a47; --line:#e4e0d8; position:relative; overflow:hidden; min-height:70vh; padding:46px 24px 72px; color:var(--ink); background:#f7f6f3; font-family:Arial, sans-serif; }
    .sax-care-page:before { content:""; position:absolute; inset:0; opacity:.42; pointer-events:none; background-image:linear-gradient(rgba(17,23,34,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(17,23,34,.025) 1px,transparent 1px); background-size:42px 42px; }
    .sax-care-glow { position:absolute; width:440px; height:440px; border-radius:50%; filter:blur(2px); pointer-events:none; }
    .sax-care-glow-left { left:-250px; top:80px; background:radial-gradient(circle,rgba(181,138,71,.15),transparent 68%); }
    .sax-care-glow-right { right:-260px; bottom:-120px; background:radial-gradient(circle,rgba(19,33,55,.11),transparent 68%); }
    .sax-care-shell { position:relative; z-index:1; width:min(1180px,100%); margin:auto; }
    .sax-care-intro { max-width:720px; margin-bottom:30px; }
    .sax-care-eyebrow { display:block; color:var(--gold); font-size:11px; line-height:1; font-weight:700; letter-spacing:2.4px; text-transform:uppercase; }
    .sax-care-intro h1 { margin:12px 0 10px; font-family:Georgia,serif; font-size:clamp(34px,4vw,52px); line-height:1.05; font-weight:400; letter-spacing:-1px; }
    .sax-care-intro p { max-width:650px; margin:0; color:var(--muted); font-size:15px; line-height:1.65; }
    .sax-care-grid { display:grid; grid-template-columns:minmax(0,1.48fr) minmax(330px,.82fr); background:#fff; border:1px solid rgba(17,23,34,.09); box-shadow:0 28px 80px rgba(17,23,34,.09); }
    .sax-care-form-card { padding:38px 42px 42px; }
    .sax-card-heading { display:flex; gap:18px; align-items:center; padding-bottom:28px; border-bottom:1px solid var(--line); }
    .sax-card-heading>span { display:grid; place-items:center; width:42px; height:42px; flex:0 0 42px; border:1px solid var(--gold); border-radius:50%; color:var(--gold); font-family:Georgia,serif; font-size:13px; }
    .sax-card-heading small { color:var(--muted); text-transform:uppercase; letter-spacing:1.5px; font-size:10px; }
    .sax-card-heading h2 { margin:3px 0 0; font-family:Georgia,serif; font-size:27px; font-weight:400; }
    .sax-success-message,.sax-error-message { display:flex; gap:14px; align-items:flex-start; margin:26px 0 0; padding:17px 19px; border-left:3px solid #2f7d57; background:#f1f8f4; }
    .sax-success-message>i { display:grid; place-items:center; width:26px; height:26px; border-radius:50%; color:white; background:#2f7d57; font-size:11px; }
    .sax-success-message strong,.sax-error-message strong { display:block; font-size:14px; }
    .sax-success-message p,.sax-error-message p { margin:3px 0 0; color:#52645a; font-size:13px; }
    .sax-error-message { display:block; border-color:#9c3030; background:#fff3f3; }
    .sax-reason-fieldset { margin:30px 0 0; padding:0; border:0; }
    .sax-reason-fieldset legend { margin-bottom:16px; color:#3d4655; font-size:13px; font-weight:700; letter-spacing:.15px; }
    .sax-reason-list { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .sax-reason-option { position:relative; display:flex; min-height:70px; gap:13px; align-items:center; margin:0; padding:14px 15px; cursor:pointer; border:1px solid #e0e2e5; background:#fff; transition:.22s ease; }
    .sax-reason-option:hover { border-color:#b7a17d; transform:translateY(-1px); }
    .sax-reason-option input { position:absolute; opacity:0; pointer-events:none; }
    .sax-reason-option:has(input:checked) { border-color:var(--gold); background:#fbf8f2; box-shadow:inset 0 0 0 1px var(--gold); }
    .sax-reason-icon { display:grid; place-items:center; width:34px; height:34px; flex:0 0 34px; color:var(--gold); background:#f5f1e9; border-radius:50%; }
    .sax-reason-text { padding-right:18px; font-size:13px; font-weight:600; line-height:1.35; }
    .sax-reason-check { position:absolute; right:12px; color:var(--gold); opacity:0; font-size:10px; }
    .sax-reason-option:has(input:checked) .sax-reason-check { opacity:1; }
    .sax-message-field { margin-top:25px; }
    .sax-message-field label { display:block; margin-bottom:10px; color:#3d4655; font-size:13px; font-weight:700; }
    .sax-message-field label span { margin-left:5px; color:#9298a2; font-weight:400; }
    .sax-message-field textarea { display:block; width:100%; min-height:130px; resize:vertical; padding:16px 17px; border:1px solid #dcdfe4; border-radius:0; outline:0; color:var(--ink); background:#fff; font:14px/1.55 Arial,sans-serif; transition:.2s; }
    .sax-message-field textarea:focus { border-color:var(--gold); box-shadow:0 0 0 3px rgba(181,138,71,.1); }
    .sax-message-field small { display:block; margin-top:8px; color:#8a9099; font-size:11px; }
    .sax-submit-button { display:flex; width:100%; justify-content:space-between; align-items:center; margin-top:26px; padding:17px 20px; border:1px solid var(--ink); color:#fff; background:var(--ink); font-size:12px; font-weight:700; letter-spacing:1.4px; text-transform:uppercase; transition:.25s; }
    .sax-submit-button:hover { border-color:var(--gold); background:var(--gold); }
    .sax-submit-button i { transition:transform .25s; }
    .sax-submit-button:hover i { transform:translateX(5px); }
    .sax-cart-summary { display:flex; flex-direction:column; padding:38px 34px; color:#fff; background:linear-gradient(155deg,#141b27,#202c3d); }
    .sax-summary-top { display:flex; justify-content:space-between; gap:15px; align-items:center; }
    .sax-cart-number { color:#9ca7b7; font-size:11px; letter-spacing:.7px; }
    .sax-cart-summary h2 { margin:18px 0 27px; color:#fff; font-family:Georgia,serif; font-size:25px; font-weight:400; }
    .sax-summary-items { border-top:1px solid rgba(255,255,255,.13); }
    .sax-summary-item { display:flex; gap:14px; align-items:center; padding:17px 0; border-bottom:1px solid rgba(255,255,255,.13); }
    .sax-item-image { width:66px; height:76px; flex:0 0 66px; overflow:hidden; background:#f3f3f1; }
    .sax-item-image img { width:100%; height:100%; object-fit:contain; mix-blend-mode:multiply; }
    .sax-item-copy { min-width:0; }
    .sax-item-copy strong { display:-webkit-box; overflow:hidden; color:#fff; font-size:12px; line-height:1.45; -webkit-line-clamp:2; -webkit-box-orient:vertical; }
    .sax-item-copy span { display:block; margin-top:7px; color:#9ca7b7; font-size:10px; text-transform:uppercase; letter-spacing:.5px; }
    .sax-more-items,.sax-empty-summary { margin:14px 0 0; color:#aeb7c4; font-size:11px; }
    .sax-summary-total { display:flex; justify-content:space-between; align-items:end; margin-top:25px; padding-bottom:26px; border-bottom:1px solid rgba(255,255,255,.13); }
    .sax-summary-total span { color:#aeb7c4; font-size:11px; text-transform:uppercase; letter-spacing:1px; }
    .sax-summary-total strong { color:#fff; font-family:Georgia,serif; font-size:21px; font-weight:400; }
    .sax-personal-care { display:flex; gap:15px; margin-top:auto; padding-top:32px; }
    .sax-care-monogram { display:grid; place-items:center; width:42px; height:42px; flex:0 0 42px; border:1px solid rgba(181,138,71,.7); border-radius:50%; color:#d0aa6b; font-family:Georgia,serif; font-size:20px; }
    .sax-personal-care strong { display:block; color:#fff; font-family:Georgia,serif; font-size:16px; font-weight:400; }
    .sax-personal-care p { margin:8px 0 13px; color:#aeb7c4; font-size:11px; line-height:1.6; }
    .sax-personal-care a { color:#d0aa6b; font-size:10px; font-weight:700; letter-spacing:1px; text-decoration:none; text-transform:uppercase; }
    .sax-personal-care a i { margin-left:6px; }
    @media(max-width:900px) { .sax-care-page{padding:38px 18px 60px}.sax-care-grid{grid-template-columns:1fr}.sax-cart-summary{min-height:auto}.sax-personal-care{margin-top:28px} }
    @media(max-width:580px) { .sax-care-page{padding:28px 0 44px}.sax-care-shell{width:100%}.sax-care-intro{margin:0 18px 24px}.sax-care-intro h1{font-size:35px;letter-spacing:-.6px}.sax-care-intro p{font-size:14px}.sax-care-grid{border-left:0;border-right:0;box-shadow:0 16px 42px rgba(17,23,34,.08)}.sax-care-form-card{padding:26px 18px 30px}.sax-card-heading{gap:12px;padding-bottom:22px}.sax-card-heading>span{width:36px;height:36px;flex-basis:36px}.sax-card-heading h2{font-size:22px}.sax-reason-list{grid-template-columns:1fr;gap:8px}.sax-reason-option{min-height:62px}.sax-message-field textarea{min-height:115px}.sax-cart-summary{padding:30px 20px}.sax-summary-top{align-items:flex-start}.sax-cart-summary h2{font-size:22px;margin:15px 0 20px}.sax-summary-total{align-items:center}.sax-summary-total strong{font-size:19px}.sax-item-image{width:60px;height:68px;flex-basis:60px} }
</style>
@endsection
