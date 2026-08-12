<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderNote extends Model
{
    use HasFactory;

    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'order_id',
        'created_by',
        'type',
        'message',
    ];

    public static function presets(): array
    {
        return [
            'order_review' => [
                'key' => 'order_note_preset_order_review',
                'default' => 'Pedido em análise pela nossa equipe.',
            ],
            'payment_review' => [
                'key' => 'order_note_preset_payment_review',
                'default' => 'Pagamento em análise.',
            ],
            'payment_confirmed' => [
                'key' => 'order_note_preset_payment_confirmed',
                'default' => 'Pagamento confirmado.',
            ],
            'picking' => [
                'key' => 'order_note_preset_picking',
                'default' => 'Pedido em separação.',
            ],
            'stock_confirmation' => [
                'key' => 'order_note_preset_stock_confirmation',
                'default' => 'Aguardando confirmação de estoque.',
            ],
            'customer_information' => [
                'key' => 'order_note_preset_customer_information',
                'default' => 'Aguardando informações ou documentos do cliente.',
            ],
            'shipping_preparation' => [
                'key' => 'order_note_preset_shipping_preparation',
                'default' => 'Pedido em preparação para envio.',
            ],
            'shipped' => [
                'key' => 'order_note_preset_shipped',
                'default' => 'Pedido enviado.',
            ],
            'ready_for_pickup' => [
                'key' => 'order_note_preset_ready_for_pickup',
                'default' => 'Pedido disponível para retirada.',
            ],
            'customer_contact' => [
                'key' => 'order_note_preset_customer_contact',
                'default' => 'Nossa equipe entrará em contato com o cliente.',
            ],
        ];
    }

    public static function presetOptions(): array
    {
        return collect(self::presets())
            ->mapWithKeys(fn (array $preset, string $type) => [
                $type => self::translate($preset['key'], $preset['default']),
            ])
            ->all();
    }

    public static function messageForType(string $type): ?string
    {
        return self::presets()[$type]['default'] ?? null;
    }

    public function displayMessage(): string
    {
        $preset = self::presets()[$this->type] ?? null;

        if (! $preset) {
            return (string) $this->message;
        }

        return self::translate($preset['key'], (string) $this->message);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    private static function translate(string $key, string $fallback): string
    {
        $translated = __("messages.{$key}");

        return $translated === "messages.{$key}" ? $fallback : $translated;
    }
}
