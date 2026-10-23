<?php

namespace App\Console\Commands;

use App\Services\CheckoutStockReservationService;
use Illuminate\Console\Command;

class ReleaseExpiredCheckoutStock extends Command
{
    protected $signature = 'checkout:release-expired-stock {--limit=200}';
    protected $description = 'Libera reservas de estoque de checkouts e pagamentos expirados';

    public function handle(CheckoutStockReservationService $reservations): int
    {
        $count = $reservations->expireDueReservations((int) $this->option('limit'));
        $this->components->info("{$count} grupo(s) de reserva processado(s).");

        return self::SUCCESS;
    }
}
