<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'provider',
        'environment',
        'external_id',
        'control_number',
        'status',
        'provider_status',
        'foreign_currency',
        'foreign_amount',
        'national_currency',
        'national_amount',
        'exchange_rate',
        'pix_copy_paste',
        'qr_code_base64',
        'expires_at',
        'paid_at',
        'refunded_at',
        'failure_code',
        'failure_message',
        'provider_payload',
    ];

    protected $casts = [
        'foreign_amount' => 'decimal:2',
        'national_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'provider_payload' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function isPayable(): bool
    {
        return in_array($this->status, ['created', 'pending'], true)
            && (!$this->expires_at || $this->expires_at->isFuture());
    }
}
