<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Services\ResumeForwardService;
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

    public function __construct(public int $contactId)
    {
        $this->onConnection('product-ai');
        $this->onQueue('product-ai');
    }

    public function handle(ResumeForwardService $resumes): void
    {
        $contact = Contact::query()->find($this->contactId);
        if ($contact) {
            $resumes->send($contact);
        }
    }
}
