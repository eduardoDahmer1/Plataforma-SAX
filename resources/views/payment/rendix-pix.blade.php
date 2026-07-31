@extends('layout.layout')

@section('content')
@php
    $pixText = fn (string $key) => app('translator')->get("messages.{$key}");
@endphp
<section class="pix-page py-4 py-lg-5">
    <div class="container">
        <div class="pix-panel mx-auto">
            <div class="pix-heading text-center">
                <div class="pix-mark"><i class="fa-brands fa-pix"></i></div>
                <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap">
                    <span class="pix-kicker">{{ $pixText('rendix_pix_secure_payment') }}</span>
                    @if ($sandbox)
                        <span class="badge text-bg-warning">{{ $pixText('rendix_pix_sandbox_notice') }}</span>
                    @endif
                </div>
                <h1>{{ $pixText('rendix_pix_title') }}</h1>
                <p>{{ $pixText('rendix_pix_instructions') }}</p>
            </div>

            <div class="pix-summary">
                <div><span>{{ $pixText('rendix_pix_order') }}</span><strong>#{{ $order->order_number ?: $order->id }}</strong></div>
                <div><span>{{ $pixText('rendix_pix_purchase_amount') }}</span><strong>US$ {{ number_format((float) $order->total, 2, ',', '.') }}</strong></div>
                <div class="is-highlight">
                    <span>{{ $pixText('rendix_pix_amount') }}</span>
                    <strong>
                        {{ $transaction->national_amount !== null ? 'R$ ' . number_format((float) $transaction->national_amount, 2, ',', '.') : $pixText('rendix_pix_calculating') }}
                    </strong>
                </div>
                <div><span>{{ $pixText('rendix_pix_expires') }}</span><strong id="pixCountdown">05:00</strong></div>
            </div>

            <div class="pix-content">
                <div class="pix-qr-column">
                    <div class="pix-qr-wrap">
                        <img src="data:image/png;base64,{{ $transaction->qr_code_base64 }}"
                             alt="{{ $pixText('rendix_pix_qr_alt') }}" width="280" height="280">
                    </div>
                    <span class="pix-sale-id">{{ $pixText('rendix_pix_sale') }} #{{ $transaction->external_id }}</span>
                </div>

                <div class="pix-copy-column">
                    <span class="pix-label">{{ $pixText('rendix_pix_copy_paste') }}</span>
                    <textarea id="pixCopyPaste" readonly rows="5">{{ $transaction->pix_copy_paste }}</textarea>
                    <button type="button" class="btn pix-copy-button" id="copyPixButton">
                        <i class="far fa-copy me-2"></i><span>{{ $pixText('rendix_pix_copy_code') }}</span>
                    </button>

                    <div class="pix-status" id="pixStatusBox">
                        <span class="pix-status-dot"></span>
                        <div>
                            <strong id="pixStatusTitle">{{ $pixText('rendix_pix_waiting_payment') }}</strong>
                            <small id="pixStatusMessage">{{ $pixText('rendix_pix_waiting_message') }}</small>
                        </div>
                    </div>

                    <ol class="pix-steps">
                        <li><span>1</span>{{ $pixText('rendix_pix_step_bank') }}</li>
                        <li><span>2</span>{{ $pixText('rendix_pix_step_choose') }}</li>
                        <li><span>3</span>{{ $pixText('rendix_pix_step_confirm') }}</li>
                    </ol>
                </div>
            </div>

            <div class="pix-expired d-none" id="pixExpiredBox">
                <strong>{{ $pixText('rendix_pix_expired_title') }}</strong>
                <span>{{ $pixText('rendix_pix_expired_message') }}</span>
                <form method="POST" action="{{ route('checkout.rendix.pix.renew', $order) }}">
                    @csrf
                    <button type="submit" class="btn btn-dark"><i class="fas fa-rotate me-2"></i>{{ $pixText('rendix_pix_generate_new') }}</button>
                </form>
            </div>

            <div class="pix-footer">
                <a href="{{ route('user.orders.show', $order) }}"><i class="fas fa-arrow-left me-2"></i>{{ $pixText('rendix_pix_back_order') }}</a>
                <span><i class="fas fa-lock me-1"></i> {{ $pixText('rendix_pix_validated') }}</span>
            </div>
        </div>
    </div>
</section>

<style>
    .pix-page{background:linear-gradient(180deg,#f3faf8,#fafafa);min-height:80vh}.pix-panel{max-width:920px;background:#fff;border:1px solid #dfeae7;border-radius:20px;padding:clamp(20px,4vw,42px);box-shadow:0 18px 46px rgba(14,82,69,.08)}.pix-heading{max-width:650px;margin:0 auto 28px}.pix-mark{width:58px;height:58px;border-radius:18px;display:grid;place-items:center;background:#e8f8f4;color:#16a085;font-size:30px;margin:0 auto 14px}.pix-kicker{font-size:.7rem;font-weight:800;letter-spacing:.13em;text-transform:uppercase;color:#6d817d}.pix-heading h1{font-size:clamp(1.8rem,4vw,2.6rem);font-weight:800;margin:8px 0}.pix-heading p{color:#6d7775;margin:0}.pix-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:28px}.pix-summary>div{padding:14px;background:#f7f9f9;border:1px solid #edf0ef;border-radius:12px}.pix-summary span,.pix-label{display:block;color:#84908e;text-transform:uppercase;font-size:.62rem;letter-spacing:.08em;font-weight:800;margin-bottom:5px}.pix-summary strong{font-size:.96rem}.pix-summary .is-highlight{background:#eaf8f5;border-color:#ccebe4}.pix-summary .is-highlight strong{color:#087965}.pix-content{display:grid;grid-template-columns:minmax(280px,.8fr) minmax(0,1.2fr);gap:38px;align-items:center}.pix-qr-column{text-align:center}.pix-qr-wrap{display:inline-flex;padding:12px;background:white;border:1px solid #dbe7e4;border-radius:18px;box-shadow:0 10px 30px rgba(0,0,0,.06)}.pix-qr-wrap img{max-width:100%;height:auto}.pix-sale-id{display:block;color:#8a9290;font-size:.68rem;margin-top:10px}.pix-copy-column textarea{width:100%;resize:none;border:1px solid #dce5e3;background:#f7f9f9;border-radius:12px;padding:13px;font:11px/1.5 monospace;word-break:break-all}.pix-copy-button{width:100%;background:#08a88a;color:#fff;font-weight:800;border-radius:11px;padding:12px;margin-top:9px}.pix-copy-button:hover{background:#078771;color:#fff}.pix-status{display:flex;align-items:center;gap:12px;margin:16px 0;padding:13px;border-radius:12px;background:#fff9e8;border:1px solid #f4e5b6}.pix-status-dot{width:11px;height:11px;border-radius:50%;background:#d59d00;box-shadow:0 0 0 5px rgba(213,157,0,.12);animation:pixPulse 1.7s infinite}.pix-status strong,.pix-status small{display:block}.pix-status small{color:#716b5d;font-size:.72rem;margin-top:2px}.pix-status.is-paid{background:#eaf8f5;border-color:#bfe7dd}.pix-status.is-paid .pix-status-dot{background:#078771;animation:none}.pix-status.is-error{background:#fff1f1;border-color:#f0cccc}.pix-status.is-error .pix-status-dot{background:#be3030;animation:none}.pix-steps{list-style:none;padding:0;margin:16px 0 0;display:grid;gap:9px;font-size:.78rem;color:#535c5a}.pix-steps li{display:flex;align-items:center;gap:9px}.pix-steps li span{width:22px;height:22px;flex:0 0 22px;border-radius:50%;display:grid;place-items:center;background:#edf5f3;color:#087965;font-weight:800;font-size:.66rem}.pix-expired{margin-top:24px;padding:18px;border:1px solid #efd4d4;background:#fff6f6;border-radius:12px;text-align:center}.pix-expired strong,.pix-expired span{display:block}.pix-expired span{color:#746767;font-size:.82rem;margin:4px 0 12px}.pix-footer{display:flex;justify-content:space-between;gap:12px;align-items:center;border-top:1px solid #edf0ef;margin-top:28px;padding-top:20px;font-size:.75rem;color:#77827f}.pix-footer a{color:#22302d;font-weight:700;text-decoration:none}@keyframes pixPulse{50%{opacity:.5;transform:scale(.85)}}@media(max-width:768px){.pix-summary{grid-template-columns:repeat(2,1fr)}.pix-content{grid-template-columns:1fr;gap:24px}.pix-footer{flex-direction:column}.pix-panel{border-radius:14px}.pix-qr-wrap img{width:240px}}
</style>

<script>
const pixText = @json([
    'copySuccess' => $pixText('rendix_pix_copy_success'),
    'copyCode' => $pixText('rendix_pix_copy_code'),
    'expiredStatus' => $pixText('rendix_pix_expired_status'),
    'expiredStatusMessage' => $pixText('rendix_pix_expired_status_message'),
    'paidStatus' => $pixText('rendix_pix_paid_status'),
    'paidMessage' => $pixText('rendix_pix_paid_message'),
    'paidCountdown' => $pixText('rendix_pix_paid_countdown'),
    'failedStatus' => $pixText('rendix_pix_failed_status'),
    'reconnecting' => $pixText('rendix_pix_reconnecting'),
]);
document.addEventListener('DOMContentLoaded', function () {
    const expiresAt = new Date(@json($transaction->expires_at?->toIso8601String()));
    const countdown = document.getElementById('pixCountdown');
    const expiredBox = document.getElementById('pixExpiredBox');
    const statusBox = document.getElementById('pixStatusBox');
    const statusTitle = document.getElementById('pixStatusTitle');
    const statusMessage = document.getElementById('pixStatusMessage');
    let finished = false;

    document.getElementById('copyPixButton')?.addEventListener('click', async function () {
        const code = document.getElementById('pixCopyPaste').value;
        try {
            await navigator.clipboard.writeText(code);
        } catch (_) {
            document.getElementById('pixCopyPaste').select();
            document.execCommand('copy');
        }
        this.querySelector('span').textContent = pixText.copySuccess;
        setTimeout(() => this.querySelector('span').textContent = pixText.copyCode, 2200);
    });

    function updateCountdown() {
        if (finished) return;
        const seconds = Math.max(0, Math.floor((expiresAt.getTime() - Date.now()) / 1000));
        countdown.textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
        if (seconds === 0) {
            expiredBox.classList.remove('d-none');
            statusBox.classList.add('is-error');
            statusTitle.textContent = pixText.expiredStatus;
            statusMessage.textContent = pixText.expiredStatusMessage;
        }
    }

    async function checkStatus() {
        if (finished) return;
        try {
            const response = await fetch(@json(route('checkout.rendix.pix.status', $order)), {
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
            });
            if (!response.ok) return;
            const data = await response.json();
            if (data.status === 'paid') {
                finished = true;
                statusBox.classList.remove('is-error');
                statusBox.classList.add('is-paid');
                statusTitle.textContent = pixText.paidStatus;
                statusMessage.textContent = pixText.paidMessage;
                countdown.textContent = pixText.paidCountdown;
                setTimeout(() => window.location.href = data.redirect_url, 1200);
            } else if (['expired', 'failed', 'verification_failed'].includes(data.status)) {
                statusBox.classList.add('is-error');
                statusTitle.textContent = data.status === 'expired' ? pixText.expiredStatus : pixText.failedStatus;
                statusMessage.textContent = data.message;
                expiredBox.classList.remove('d-none');
            }
        } catch (_) {
            statusMessage.textContent = pixText.reconnecting;
        }
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
    setInterval(checkStatus, 7000);
    checkStatus();
});
</script>
@endsection
