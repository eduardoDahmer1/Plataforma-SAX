<?php

namespace App\Models;

use App\Notifications\WelcomeAndVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => mb_strtolower(trim((string) $value))
        );
    }

    protected function phoneCountry(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => preg_replace('/\D/', '', (string) $value)
        );
    }

    protected function phoneNumber(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => preg_replace('/\D/', '', (string) $value)
        );
    }

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
    
    // user_type 1 é o administrador: é o que o middleware CheckAdmin exige e é
    // justamente quem o carrinho impede de comprar. O 2 é o cliente da loja.
    public function isAdmin(): bool
    {
        return (int) $this->user_type === 1;
    }

    public function getUserRoleAttribute()
    {
        return match ((int) $this->user_type) {
            1 => 'Admin',
            2 => 'Cliente',
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