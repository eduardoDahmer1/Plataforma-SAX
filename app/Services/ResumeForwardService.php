<?php

namespace App\Services;

use App\Mail\ResumeForwardMail;
use App\Jobs\SendResumeToHr;
use App\Models\Contact;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ResumeForwardService
{
    public function queue(Contact $contact): bool
    {
        if ((int) $contact->contact_type !== 2) {
            return false;
        }

        $claimed = Contact::query()
            ->whereKey($contact->id)
            ->whereNull('hr_sent_at')
            ->where(function ($query) {
                $query->whereNull('hr_attempted_at')
                    ->orWhereNotNull('hr_last_error')
                    ->orWhere('hr_attempted_at', '<', now()->subMinutes(30));
            })
            ->update(['hr_attempted_at' => now(), 'hr_last_error' => null]);

        if (! $claimed) {
            return false;
        }

        try {
            SendResumeToHr::dispatch($contact->id);
            return true;
        } catch (Throwable $exception) {
            $contact->update(['hr_last_error' => Str::limit($exception->getMessage(), 500, '')]);
            Log::error('Falha ao colocar currículo na fila do RH', [
                'contact_id' => $contact->id,
                'error' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    public function destinationFor(?string $storeName): ?string
    {
        $name = Str::lower(Str::ascii(trim((string) $storeName)));

        $store = match ($name) {
            'cde', 'ciudad del este' => 'cde',
            'asu', 'asuncion' => 'asuncion',
            'pjc', 'pedro juan caballero' => 'pjc',
            default => null,
        };

        return $store ? Contact::HR_EMAILS[$store] : null;
    }

    public function send(Contact $contact): bool
    {
        if ((int) $contact->contact_type !== 2 || $contact->hr_sent_at) {
            return false;
        }

        $destination = $this->destinationFor($contact->store_name);

        try {
            if (! $destination) {
                throw new RuntimeException('Loja do currículo não reconhecida.');
            }
            if (! $contact->attachment || ! Storage::disk('public')->exists($contact->attachment)) {
                throw new RuntimeException('Arquivo do currículo não encontrado.');
            }

            Mail::to($destination)->send(new ResumeForwardMail($contact));
            $contact->update([
                'hr_sent_at' => now(),
                'hr_sent_to' => $destination,
                'hr_attempted_at' => now(),
                'hr_last_error' => null,
            ]);

            return true;
        } catch (Throwable $exception) {
            $contact->update([
                'hr_attempted_at' => now(),
                'hr_last_error' => Str::limit($exception->getMessage(), 500, ''),
            ]);
            Log::error('Falha ao encaminhar currículo ao RH', [
                'contact_id' => $contact->id,
                'destination' => $destination,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
