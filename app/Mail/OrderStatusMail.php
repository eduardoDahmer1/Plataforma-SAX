<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $messageCustom;
    public ?string $logoUrl;
    public string $emailLocale;

    /**
     * @param Order $order
     * @param string $messageCustom
     */
    public function __construct(Order $order, $messageCustom)
    {
        $this->order = $order;
        $this->messageCustom = $messageCustom;
        $this->logoUrl = \App\Models\Attribute::logoUrl();
        $this->emailLocale = $this->resolveLocaleFromOrder($order);
    }

    public function envelope(): Envelope
    {
        $subjectPrefix = match ($this->emailLocale) {
            'en' => 'Order #',
            'es' => 'Pedido #',
            default => 'Pedido #',
        };

        return new Envelope(
            subject: $subjectPrefix . $this->order->order_number . ' - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_status',
            with: [
                'emailLocale' => $this->emailLocale,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    private function resolveLocaleFromOrder(Order $order): string
    {
        // Idioma escolhido pelo cliente na compra. Pedidos antigos não têm
        // essa coluna, então caímos no mapeamento pela moeda.
        if (in_array($order->locale, \App\Http\Middleware\SetLocale::LOCALES, true)) {
            return $order->locale;
        }

        $sign = strtoupper(trim((string) ($order->currency_sign ?? '')));

        if ($sign === 'R$') {
            return 'pt_BR';
        }

        if ($sign === 'G$') {
            return 'es';
        }

        return 'en';
    }
}