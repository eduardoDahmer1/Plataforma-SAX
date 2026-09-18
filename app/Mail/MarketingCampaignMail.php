<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarketingCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $renderedSubject;

    public string $renderedBody;

    public function __construct(
        public EmailCampaign $campaign,
        public EmailCampaignRecipient $recipient,
    ) {
        $bodyReplacements = [
            '{{nome}}' => e($recipient->name ?: 'cliente'),
            '{{name}}' => e($recipient->name ?: 'cliente'),
            '{{email}}' => e($recipient->email),
        ];
        $subjectReplacements = [
            '{{nome}}' => $this->subjectValue($recipient->name ?: 'cliente'),
            '{{name}}' => $this->subjectValue($recipient->name ?: 'cliente'),
            '{{email}}' => $this->subjectValue($recipient->email),
        ];
        $this->renderedSubject = strtr($campaign->subject, $subjectReplacements);
        $this->renderedBody = strtr($campaign->body, $bodyReplacements);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->renderedSubject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.marketing_campaign');
    }

    public function attachments(): array
    {
        return [];
    }

    private function subjectValue(string $value): string
    {
        return trim(preg_replace('/[\r\n]+/', ' ', strip_tags($value)) ?? '');
    }
}
