<?php

namespace App\Console\Commands;

use App\Mail\AbandonedCartHelpMail;
use App\Models\AbandonedCart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SendAbandonedCartHelpEmails extends Command
{
    protected $signature = 'carts:send-help {--limit=50}';
    protected $description = 'Envia mensagens de ajuda para carrinhos abandonados ainda não contatados';

    public function handle(): int
    {
        $sent = 0;
        AbandonedCart::with('user')->where('status', 'abandoned')->whereNull('help_email_sent_at')
            ->oldest('abandoned_at')->limit(max(1, (int) $this->option('limit')))->get()
            ->each(function (AbandonedCart $cart) use (&$sent) {
                if (!$cart->user?->email) return;
                try {
                    if (!$cart->recovery_token) $cart->update(['recovery_token' => Str::random(64)]);
                    Mail::to($cart->user->email)->send(new AbandonedCartHelpMail($cart->fresh('user')));
                    $cart->update(['help_email_sent_at' => now()]);
                    $sent++;
                } catch (\Throwable $e) {
                    Log::error('Falha no lembrete de carrinho abandonado', ['cart_id' => $cart->id, 'message' => $e->getMessage()]);
                    $this->warn("Carrinho #{$cart->id}: e-mail não enviado.");
                }
            });
        $this->info("{$sent} e-mail(s) enviado(s).");
        return self::SUCCESS;
    }
}
