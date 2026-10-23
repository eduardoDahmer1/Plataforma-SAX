<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('carts:send-help --limit=50')->hourly()->withoutOverlapping();
        $schedule->command('rendix:reconcile-pix --limit=50')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('bancard:reconcile-payments --limit=50')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('checkout:release-expired-stock --limit=500')->everyMinute()->withoutOverlapping();
        $schedule->command('integration:check-health')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('products:refresh-feed')->everyMinute()->withoutOverlapping();
        $schedule->command('catalog:sync-attributes')->everyFiveMinutes()->withoutOverlapping(10);
        $schedule->command('catalog:sync-pending --max-families=1 --max-seconds=45')
            ->everyMinute()
            ->withoutOverlapping(10);
        $schedule->command('emails:send-pending --limit=3')->everyMinute()->withoutOverlapping();
        // Rede de segurança para alterações externas que não disparam eventos Eloquent.
        $schedule->command('products:refresh-feed --force')->hourly()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
