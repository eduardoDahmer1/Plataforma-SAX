<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Attribute;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public ?string $logoUrl;
    public string $emailLocale;

    public function __construct(Order $order)
    {
        $this->order = $order;

        $this->logoUrl = Attribute::logoUrl();
        $this->emailLocale = $this->resolveLocaleFromOrder($order);
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->emailLocale) {
            'en' => 'Payment confirmed! Order #',
            'es' => 'Pago confirmado! Pedido #',
            default => 'Pagamento confirmado! Pedido #',
        };

        return new Envelope(
            subject: $subject . ($this->order->order_number ?? $this->order->id) . ' - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_paid',
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
