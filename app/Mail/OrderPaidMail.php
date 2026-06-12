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

    public function __construct(Order $order)
    {
        $this->order = $order;

        $this->logoUrl = Attribute::logoUrl();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pagamento confirmado! Pedido #' . ($this->order->order_number ?? $this->order->id) . ' - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_paid',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
