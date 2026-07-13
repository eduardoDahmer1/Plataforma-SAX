<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCupon extends Model
{
    protected $fillable = [
        'user_id',
        'cupon_id',
        'order_id',
        'desconto',
        'usado_em',
    ];

    protected $casts = [
        'desconto' => 'float',
        'usado_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cupon()
    {
        return $this->belongsTo(Cupon::class, 'cupon_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Só contam como uso os cupons já consumidos em um pedido.
    public function scopeConsumidos($query)
    {
        return $query->whereNotNull('usado_em');
    }
}
