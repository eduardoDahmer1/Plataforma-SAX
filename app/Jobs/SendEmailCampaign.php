<?php

namespace App\Jobs;

use App\Mail\MarketingCampaignMail;
use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const RECIPIENTS_PER_JOB = 5;

    public int $timeout = 130;

    public int $tries = 2;

    public array $backoff = [30, 120];

    public function __construct(public int $campaignId)
    {
        $this->onConnection('product-ai');
        $this->onQueue('product-ai');
    }

    public function handle(): void
    {
        $lock = Cache::lock("email_campaign:{$this->campaignId}:sending", $this->timeout + 30);
        if (! $lock->get()) {
            return;
        }

        try {
            $this->processBatch();
        } finally {
            $lock->release();
        }
    }

    private function processBatch(): void
    {
        $campaign = EmailCampaign::query()->find($this->campaignId);
        if (! $campaign || $campaign->status === 'completed') {
            return;
        }

        if ($campaign->status === 'pending') {
            $claimed = EmailCampaign::query()
                ->whereKey($campaign->id)
                ->where('status', 'pending')
                ->update(['status' => 'processing', 'started_at' => now(), 'updated_at' => now()]);
            if (! $claimed) {
                return;
            }
            $campaign->refresh();
        } elseif ($campaign->status === 'processing') {
            $campaign->update(['started_at' => now()]);
        }

        $recipients = $campaign->recipients()
            ->where('status', 'pending')
            ->orderBy('id')
            ->limit(self::RECIPIENTS_PER_JOB)
            ->get();

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email, $recipient->name)->send(new MarketingCampaignMail($campaign, $recipient));
                $recipient->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);
                $campaign->increment('sent_count');
            } catch (Throwable $exception) {
                report($exception);
                $recipient->update([
                    'status' => 'failed',
                    'error' => mb_substr($exception->getMessage(), 0, 1000),
                ]);
                $campaign->increment('failed_count');
            }
        }

        if ($campaign->recipients()->where('status', 'pending')->exists()) {
            $campaign->update(['started_at' => now()]);
            self::dispatch($campaign->id)->delay(now()->addSeconds(10));

            return;
        }

        $campaign->update(['status' => 'completed', 'finished_at' => now()]);
    }
}
