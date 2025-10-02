<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property \Illuminate\Database\Eloquent\Collection $favoriteProducts
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'phone_country',
        'phone_number',
        'location_country',
        'address',
        'cep',
        'state',
        'city',
        'already_registered',
        'additional_info',
        'document',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getUserRoleAttribute()
    {
        return match ($this->user_type) {
            1 => 'Usuário Comum',
            2 => 'Admin Master',
            default => 'Desconhecido',
        };
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function cart()
    {
        return $this->hasMany(\App\Models\Cart::class);
    }

    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'user_product_preferences')->withTimestamps();
    }

    public function cupons()
    {
        return $this->belongsToMany(
            Cupon::class,
            'user_cupons',
            'user_id',
            'cupon_id'
        )->withPivot('desconto')->withTimestamps();
    }
}
