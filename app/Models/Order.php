<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total',
        'discount',
        'cupon_id',
        'payment_method',
        'deposit_receipt',
        'name',
        'document',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'cep',
        'street',
        'number',
        'observations',
        'shipping',
        'store',

        // Novos campos compatíveis com a outra plataforma
        'order_number',
        'txnid',
        'charge_id',
        'pay_id',
        'payment_status',

        'shipping_cost',
        'packing_cost',
        'tax',

        'currency_sign',
        'currency_value',

        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_country',
        'shipping_state',
        'shipping_city',
        'shipping_zip',
        'shipping_address',
        'shipping_address_number',
        'shipping_complement',
        'shipping_district',
        'shipping_document',

        'order_note',
        'internal_note',

        'affilate_user',
        'affilate_charge',

        'location',
        'delivery_method',
        'description',
        'payment',
    ];

    // Relacionamento com itens do pedido
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relacionamento com o usuário
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento com o cupom
    public function cupon()
    {
        return $this->belongsTo(Cupon::class);
    }
}
