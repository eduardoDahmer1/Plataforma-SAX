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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function cupon()
    {
        return $this->belongsTo(Cupon::class, 'cupon_id');
    }
}
