<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCupon extends Model
{
    protected $fillable = [
        'user_id',
        'cupon_id',
        'desconto',
    ];

    /**
     * Relacionamento com o usuário dono do cupom
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com o cupom aplicado
     */
    public function cupon()
    {
        return $this->belongsTo(Cupon::class);
    }
}
