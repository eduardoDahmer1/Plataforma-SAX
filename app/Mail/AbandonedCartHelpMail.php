<?php

namespace App\Mail;

use App\Models\AbandonedCart;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbandonedCartHelpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AbandonedCart $cart) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Podemos ajudar com sua compra na SAX?');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.abandoned_cart_help');
    }

    public function attachments(): array { return []; }
}
