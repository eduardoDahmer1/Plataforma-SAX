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
        'discount',          // novo campo para desconto do cupom
        'cupon_id',          // novo campo para referência ao cupom
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
