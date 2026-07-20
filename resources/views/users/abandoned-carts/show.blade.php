@extends('layout.dashboard')

@section('content')
<div class="sax-dashboard-wrapper">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div><h1 class="sax-title mb-2">{{ __('messages.user_cart_number', ['id' => $abandonedCart->id]) }}</h1><p class="sax-subtitle mb-0">{{ __('messages.user_saved_at', ['date' => $abandonedCart->abandoned_at->format('d/m/Y H:i')]) }}</p></div>
        <a href="{{ route('user.abandoned-carts.index') }}" class="btn btn-outline-dark btn-sm">{{ __('messages.voltar') }}</a>
    </div>
    @if(session('success') || session('error'))<div class="alert {{ session('error') ? 'alert-danger' : 'alert-success' }}">{{ session('error') ?? session('success') }}</div>@endif

    <div class="border rounded-3 bg-white shadow-sm overflow-hidden">
        @foreach($abandonedCart->items as $item)
            <div class="d-flex align-items-center gap-3 p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                <img src="{{ $item->product?->photo_url ?? ($item->image ? asset('storage/uploads/'.$item->image) : asset('storage/uploads/noimage.webp')) }}" alt="" class="rounded border object-fit-cover" style="width:72px;height:72px">
                <div class="flex-grow-1"><strong class="d-block">{{ $item->product_name }}</strong><small class="text-muted">{{ __('messages.user_sku_quantity', ['sku' => $item->sku ?? '—', 'quantity' => $item->quantity]) }}</small></div>
                <strong>{{ $abandonedCart->currency_sign }} {{ number_format($item->unit_price * $item->quantity * $abandonedCart->currency_value, 2, '.', ',') }}</strong>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center border rounded-3 bg-white p-4 mt-3">
        <div><small class="text-muted text-uppercase fw-bold">{{ __('messages.user_saved_total') }}</small><div class="h4 fw-bold mb-0">{{ $abandonedCart->currency_sign }} {{ number_format($abandonedCart->total * $abandonedCart->currency_value, 2, '.', ',') }}</div></div>
        @if($abandonedCart->status !== 'restored')
            <form action="{{ route('user.abandoned-carts.restore', $abandonedCart) }}" method="POST">@csrf<button class="btn btn-dark px-4"><i class="fas fa-undo me-2"></i>{{ __('messages.user_restore_cart') }}</button></form>
        @else
            <span class="badge bg-success p-2">{{ __('messages.user_cart_restored') }}</span>
        @endif
    </div>

    @if($abandonedCart->feedback_at)
        @php
            $feedbackLabels = [
                'payment' => __('messages.cart_abandon_reason_payment_title'),
                'shipping_price' => __('messages.cart_abandon_reason_shipping_title'),
                'later' => __('messages.cart_abandon_reason_later_title'),
                'help' => __('messages.cart_abandon_reason_help_title'),
                'no_answer' => __('messages.cart_abandon_reason_no_answer'),
                'gave_up' => __('messages.cart_feedback_reason_gave_up'),
                'other' => __('messages.cart_feedback_reason_other'),
            ];
        @endphp
        <div class="border rounded-3 bg-white p-4 mt-3">
            <small class="text-muted text-uppercase fw-bold">{{ __('messages.user_abandon_response') }}</small>
            <p class="fw-bold mb-1 mt-2">{{ $feedbackLabels[$abandonedCart->feedback_reason] ?? __('messages.user_reason_reported') }}</p>
            @if($abandonedCart->feedback_message)
                <p class="mb-1 text-muted" style="white-space:pre-line">{{ $abandonedCart->feedback_message }}</p>
            @else
                <p class="mb-1 text-muted">{{ __('messages.user_no_feedback_comment') }}</p>
            @endif
            <small class="text-muted">{{ __('messages.user_feedback_sent_at', ['date' => $abandonedCart->feedback_at->format('d/m/Y H:i')]) }}</small>
        </div>
    @endif
</div>
@endsection
