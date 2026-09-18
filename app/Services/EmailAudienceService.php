<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\EmailOptOut;
use App\Models\User;
use Illuminate\Support\Collection;

class EmailAudienceService
{
    public const AUDIENCES = [
        'customers' => 'Todos os clientes cadastrados',
        'buyers' => 'Clientes que já compraram',
        'newsletter' => 'Inscritos na newsletter',
        'contacts' => 'Contatos recebidos pelo formulário',
        'specific' => 'E-mails específicos',
        'selected_contacts' => 'Contatos selecionados na caixa de entrada',
        'reply' => 'Responder a um contato',
    ];

    public function counts(): array
    {
        return [
            'customers' => User::query()->where('user_type', User::TYPE_CUSTOMER)->whereNotNull('email')->count(),
            'buyers' => User::query()->where('user_type', User::TYPE_CUSTOMER)
                ->whereHas('orders', fn ($query) => $query->whereIn('status', ['paid', 'completed', 'processing']))
                ->whereNotNull('email')->count(),
            'newsletter' => Contact::query()->where('contact_type', 3)->whereNotNull('email')->distinct('email')->count('email'),
            'contacts' => Contact::query()->whereIn('contact_type', [1, 4])->whereNotNull('email')->distinct('email')->count('email'),
        ];
    }

    public function resolve(
        string $audience,
        ?Contact $contact = null,
        ?string $specificEmails = null,
        array $contactIds = [],
        bool $respectOptOut = true,
    ): Collection {
        $recipients = match ($audience) {
            'customers' => $this->users(false),
            'buyers' => $this->users(true),
            'newsletter' => $this->contacts([3]),
            'contacts' => $this->contacts([1, 4]),
            'specific' => $this->specific($specificEmails),
            'selected_contacts' => $this->contactsByIds($contactIds),
            'reply' => $contact ? collect([[
                'email' => $contact->email,
                'name' => $contact->name,
                'source' => 'contact',
                'source_id' => $contact->id,
            ]]) : collect(),
            default => collect(),
        };

        $optedOut = $respectOptOut
            ? EmailOptOut::query()
                ->whereIn('email', $recipients->pluck('email')->filter()->map(fn ($email) => mb_strtolower($email)))
                ->pluck('email')->map(fn ($email) => mb_strtolower($email))->flip()
            : collect();

        return $recipients
            ->filter(fn ($recipient) => filter_var($recipient['email'] ?? null, FILTER_VALIDATE_EMAIL))
            ->map(function ($recipient) {
                $recipient['email'] = mb_strtolower(trim($recipient['email']));
                $recipient['name'] = trim((string) ($recipient['name'] ?? '')) ?: null;

                return $recipient;
            })
            ->reject(fn ($recipient) => $optedOut->has($recipient['email']))
            ->unique('email')
            ->values();
    }

    private function users(bool $buyersOnly): Collection
    {
        return User::query()
            ->where('user_type', User::TYPE_CUSTOMER)
            ->whereNotNull('email')
            ->when($buyersOnly, fn ($query) => $query->whereHas(
                'orders', fn ($orders) => $orders->whereIn('status', ['paid', 'completed', 'processing'])
            ))
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user) => [
                'email' => $user->email,
                'name' => $user->name,
                'source' => 'user',
                'source_id' => $user->id,
            ]);
    }

    private function contacts(array $types): Collection
    {
        return Contact::query()
            ->whereIn('contact_type', $types)
            ->whereNotNull('email')
            ->latest('id')
            ->get(['id', 'name', 'email'])
            ->map(fn (Contact $contact) => [
                'email' => $contact->email,
                'name' => $contact->name,
                'source' => 'contact',
                'source_id' => $contact->id,
            ]);
    }

    private function contactsByIds(array $ids): Collection
    {
        return Contact::query()
            ->whereKey($ids)
            ->get(['id', 'name', 'email'])
            ->map(fn (Contact $contact) => [
                'email' => $contact->email,
                'name' => $contact->name,
                'source' => 'contact',
                'source_id' => $contact->id,
            ]);
    }

    private function specific(?string $emails): Collection
    {
        return collect(preg_split('/[\s,;]+/', (string) $emails, -1, PREG_SPLIT_NO_EMPTY))
            ->take(500)
            ->map(fn ($email) => [
                'email' => $email,
                'name' => null,
                'source' => 'manual',
                'source_id' => null,
            ]);
    }
}
