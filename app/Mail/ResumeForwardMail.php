<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResumeForwardMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contact $contact) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Novo currículo: '.$this->contact->name.' — '.$this->contact->store_name);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.resume_forward');
    }

    public function attachments(): array
    {
        return [Attachment::fromStorageDisk('public', $this->contact->attachment)
            ->as('curriculo.'.pathinfo($this->contact->attachment, PATHINFO_EXTENSION))];
    }
}
