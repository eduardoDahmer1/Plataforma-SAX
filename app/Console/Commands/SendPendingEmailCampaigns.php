<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailCampaign;
use App\Models\EmailCampaign;
use Illuminate\Console\Command;

class SendPendingEmailCampaigns extends Command
{
    protected $signature = 'emails:send-pending {--limit=3 : Quantidade máxima de campanhas por execução}';

    protected $description = 'Envia campanhas pendentes e retoma campanhas interrompidas';

    public function handle(): int
    {
        $limit = max(1, min(20, (int) $this->option('limit')));
        $campaigns = EmailCampaign::query()
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere(function ($stale) {
                        $stale->where('status', 'processing')
                            ->where(fn ($time) => $time->whereNull('started_at')->orWhere('started_at', '<=', now()->subMinutes(10)));
                    });
            })
            ->oldest()
            ->limit($limit)
            ->pluck('id');

        foreach ($campaigns as $campaignId) {
            (new SendEmailCampaign($campaignId))->handle();
        }

        $this->info("Campanhas processadas: {$campaigns->count()}");

        return self::SUCCESS;
    }
}
