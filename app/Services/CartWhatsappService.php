<?php

namespace App\Services;

use App\Models\WhatsappContact;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CartWhatsappService
{
    public function __construct(private WhatsappWidgetService $widget)
    {
    }

    public function contact(Request $request): ?WhatsappContact
    {
        // Use the first active cart contact in the store's configured display order.
        return $this->widget->configuration($request, 'cart')['contacts']
            ->first(fn (WhatsappContact $contact) => preg_match('/^[1-9][0-9]{7,14}$/', preg_replace('/\D+/', '', $contact->phone ?? '')));
    }

    public function url(Request $request, Collection $cart): ?string
    {
        $contact = $this->contact($request);
        if (! $contact) {
            return null;
        }

        $lines = [__('messages.checkout_pause_intro'), ''];
        $subtotal = 0;
        foreach ($cart as $item) {
            $product = $item->product;
            $lines[] = $product->external_name ?: $product->name;
            $lines[] = 'SKU: '.($product->sku ?: '-');
            $lines[] = __('messages.checkout_pause_quantity').': '.$item->quantity;
            $lines[] = __('messages.checkout_pause_unit_price').': '.currency($product->price);
            $lines[] = '------------------------';
            $subtotal += $product->price * $item->quantity;
        }
        $lines[] = __('messages.checkout_pause_subtotal').': '.currency($subtotal);
        $lines[] = '';
        $lines[] = __('messages.checkout_pause_reference');

        return 'https://wa.me/'.preg_replace('/\D+/', '', $contact->phone).'?text='.rawurlencode(implode("\n", $lines));
    }
}
