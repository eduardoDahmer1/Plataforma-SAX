<?php

namespace App\Services;

use App\Models\WhatsappContact;
use App\Models\Order;
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

    public function url(Collection $cart, Order $order, WhatsappContact $contact): string
    {
        $lines = [__('messages.checkout_pause_intro'), ''];
        $subtotal = 0;
        foreach ($cart as $item) {
            $product = $item->product;
            $lines[] = $product->external_name ?: $product->name;
            $lines[] = 'Produto: '.route('produto.show', $product->slug ?: $product->id);
            $lines[] = 'SKU: '.($product->sku ?: '-');
            $lines[] = __('messages.checkout_pause_quantity').': '.$item->quantity;
            $lines[] = __('messages.checkout_pause_unit_price').': '.currency($product->price);
            $lines[] = '------------------------';
            $subtotal += $product->price * $item->quantity;
        }
        $lines[] = __('messages.checkout_pause_subtotal').': '.currency($subtotal);
        $lines[] = '';
        $lines[] = 'Pedido #'.$order->id.': '.route('admin.orders.show', $order->id);
        $lines[] = '';
        $lines[] = __('messages.checkout_pause_reference');

        return 'https://wa.me/'.preg_replace('/\D+/', '', $contact->phone).'?text='.rawurlencode(implode("\n", $lines));
    }
}
