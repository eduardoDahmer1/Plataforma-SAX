<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IntegrationAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $type,
        public string $title,
        public string $alertMessage,
        public ?string $actionUrl = null,
        public array $details = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title.' - '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.integration_alert',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
