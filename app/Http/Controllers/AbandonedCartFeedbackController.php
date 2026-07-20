<?php

namespace App\Http\Controllers;

use App\Models\AbandonedCart;
use App\Services\BusinessEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbandonedCartFeedbackController extends Controller
{
    public function show(Request $request, string $token): View|RedirectResponse
    {
        if (!$request->user()) {
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('home')
                ->with('cart_feedback_notice', 'login_required');
        }

        $cart = AbandonedCart::where('recovery_token', $token)
            ->with(['user', 'items.product'])
            ->firstOrFail();

        if ((int) $cart->user_id !== (int) $request->user()->id) {
            return redirect()->route('home')
                ->with('cart_feedback_notice', 'wrong_account');
        }

        $requestedReason = (string) $request->query('reason', '');
        $reason = array_key_exists($requestedReason, $this->reasons()) ? $requestedReason : null;

        return view('abandoned-carts.feedback', ['cart' => $cart, 'reason' => $reason, 'reasons' => $this->reasons()]);
    }

    public function store(Request $request, string $token, BusinessEventService $events)
    {
        if (!$request->user()) {
            $request->session()->put('url.intended', route('abandoned-cart.feedback', ['token' => $token]));

            return redirect()->route('home')
                ->with('cart_feedback_notice', 'login_required');
        }

        $cart = AbandonedCart::where('recovery_token', $token)
            ->with(['user', 'items.product'])
            ->firstOrFail();

        if ((int) $cart->user_id !== (int) $request->user()->id) {
            return redirect()->route('home')
                ->with('cart_feedback_notice', 'wrong_account');
        }

        $validated = $request->validate([
            'reason' => ['required', 'in:' . implode(',', array_keys($this->reasons()))],
            'message' => ['nullable', 'string', 'max:1500'],
        ]);

        $cart->update([
            'feedback_reason' => $validated['reason'],
            'feedback_message' => $validated['message'] ?? null,
            'feedback_at' => now(),
        ]);
        $events->record('cart', 'Cliente respondeu sobre o carrinho', $this->reasons()[$validated['reason']], 'info', $cart->user_id, null, 'Carrinho #' . $cart->id);

        return back()->with('success', __('messages.cart_feedback_success'));
    }

    private function reasons(): array
    {
        return [
            'later' => __('messages.cart_feedback_reason_later'),
            'payment' => __('messages.cart_feedback_reason_payment'),
            'help' => __('messages.cart_feedback_reason_help'),
            'gave_up' => __('messages.cart_feedback_reason_gave_up'),
            'other' => __('messages.cart_feedback_reason_other'),
        ];
    }
}
