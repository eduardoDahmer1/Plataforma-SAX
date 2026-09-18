<?php

namespace App\Jobs;

use App\Mail\MarketingCampaignMail;
use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public function __construct(public int $campaignId) {}

    public function handle(): void
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
            if ($campaign->started_at?->isAfter(now()->subMinutes(10))) {
                return;
            }
            $campaign->update(['started_at' => now()]);
        }

        $campaign->recipients()->where('status', 'pending')->orderBy('id')->chunkById(50, function ($recipients) use ($campaign) {
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
        });

        $campaign->update(['status' => 'completed', 'finished_at' => now()]);
    }
}
