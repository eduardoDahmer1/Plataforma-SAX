<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\ResumeForwardAttempt;
use App\Services\ResumeForwardService;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendResumeToHr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 1;

    public function __construct(public int $contactId, public ?int $attemptId = null)
    {
        $this->onConnection('product-ai');
        $this->onQueue('product-ai');
    }

    public function handle(ResumeForwardService $resumes): void
    {
        if ($this->attemptId === null) {
            // Compatibilidade com trabalhos enfileirados antes do histórico de RH.
            $contact = Contact::query()->find($this->contactId);
            if (! $contact || $contact->hr_sent_at) return;

            $attempt = ResumeForwardAttempt::create([
                'contact_id' => $contact->id,
                'candidate_name' => $contact->name,
                'candidate_email' => $contact->email,
                'store_name' => $contact->store_name,
                'destination' => $resumes->destinationFor($contact->store_name),
                'source' => 'legacy',
                'status' => 'processing',
                'started_at' => now(),
            ]);
            $sent = $resumes->send($contact);
            $attempt->update([
                'status' => $sent ? 'sent' : 'failed',
                'finished_at' => now(),
                'error' => $sent ? null : $contact->fresh()->hr_last_error,
            ]);
            return;
        }

        $claimed = ResumeForwardAttempt::query()->whereKey($this->attemptId)
            ->where('status', 'queued')
            ->update(['status' => 'processing', 'started_at' => now()]);
        if (! $claimed) return;

        $contact = Contact::query()->find($this->contactId);
        $sent = $contact && $resumes->send($contact);
        ResumeForwardAttempt::query()->whereKey($this->attemptId)->update([
            'status' => $sent ? 'sent' : 'failed',
            'finished_at' => now(),
            'error' => $sent ? null : ($contact?->fresh()->hr_last_error ?: 'Currículo indisponível para envio.'),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        if ($this->attemptId === null) return;

        $error = Str::limit($exception->getMessage(), 500, '');
        ResumeForwardAttempt::query()->whereKey($this->attemptId)
            ->whereIn('status', ['queued', 'processing'])
            ->update(['status' => 'failed', 'finished_at' => now(), 'error' => $error]);
        Contact::query()->whereKey($this->contactId)->whereNull('hr_sent_at')
            ->update(['hr_last_error' => $error]);
    }
}
