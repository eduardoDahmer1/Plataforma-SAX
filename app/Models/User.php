<?php

namespace App\Models;

use App\Notifications\WelcomeAndVerifyEmail;
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
        'number',       // Adicionado
        'district',     // Adicionado
        'complement',   // Adicionado
        'country',      // Adicionado/Ajustado
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Sobrescreve o envio da notificação de verificação de e-mail.
     * Isso garante que o usuário receba o e-mail de boas-vindas personalizado.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new WelcomeAndVerifyEmail());
    }

    /**
     * Accessor para retornar o papel do usuário de forma legível.
     */

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
    
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