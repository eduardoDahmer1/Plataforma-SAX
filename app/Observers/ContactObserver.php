<?php

namespace App\Observers;

use App\Models\Contact;
use App\Services\AdminNotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ContactObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private AdminNotificationService $notifications) {}

    public function created(Contact $contact): void
    {
        $contactType = (int) $contact->contact_type;

        if ($contactType === 3) {
            return;
        }

        $isResume = $contactType === 2;

        $this->notifications->notifyAdmins(
            type: $isResume ? 'new_resume' : 'new_contact',
            title: $isResume ? 'Novo currículo' : 'Novo contato',
            message: $isResume
                ? "{$contact->name} enviou um currículo."
                : "{$contact->name} enviou uma mensagem pelo formulário.",
            actionUrl: '/admin/contatos',
            data: [
                'contact_id' => $contact->getKey(),
                'contact_type' => $contactType,
                'translation_params' => ['name' => $contact->name],
            ],
        );
    }
}
