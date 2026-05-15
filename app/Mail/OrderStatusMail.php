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

    /**
     * @param Order $order
     * @param string $messageCustom
     */
    public function __construct(Order $order, $messageCustom)
    {
        $this->order = $order;
        $this->messageCustom = $messageCustom;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pedido #' . $this->order->order_number . ' - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_status', // Certifique-se de criar este arquivo
        );
    }

    public function attachments(): array
    {
        return [];
    }
}