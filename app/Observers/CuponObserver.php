<?php

namespace App\Observers;

use App\Models\Cupon;
use App\Services\CustomerNotificationService;

class CuponObserver
{
    public function __construct(private CustomerNotificationService $notifications) {}

    public function created(Cupon $cupon): void
    {
        if ($cupon->ativo) $this->notify($cupon);
    }

    public function updated(Cupon $cupon): void
    {
        if ($cupon->wasChanged('ativo') && $cupon->ativo) $this->notify($cupon);
    }

    private function notify(Cupon $cupon): void
    {
        $this->notifications->notifyCustomers('new_coupon', 'Novo cupom disponível', "Use o cupom {$cupon->codigo} e aproveite {$cupon->rotuloDesconto()} de desconto.", '/cupons', ['cupon_id' => $cupon->id, 'code' => $cupon->codigo]);
    }
}
