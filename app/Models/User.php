<?php

namespace App\Models;

use App\Notifications\WelcomeAndVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

/**
 * @property \Illuminate\Database\Eloquent\Collection $favoriteProducts
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    public const TYPE_ADMIN_MASTER = 1;
    public const TYPE_CUSTOMER = 2;
    public const TYPE_COURSE = 3;
    public const TYPE_ADMIN_EDITOR = 4;

    public const USER_TYPES = [
        self::TYPE_ADMIN_MASTER,
        self::TYPE_CUSTOMER,
        self::TYPE_COURSE,
        self::TYPE_ADMIN_EDITOR,
    ];

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
        'document_type',
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

    // Master e Editor usam o painel administrativo. Apenas o Master possui
    // acesso às áreas sensíveis (Vendas, Sistema e gestão de usuários).
    public function isAdmin(): bool
    {
        return in_array((int) $this->user_type, [self::TYPE_ADMIN_MASTER, self::TYPE_ADMIN_EDITOR], true);
    }

    public function isMasterAdmin(): bool
    {
        return (int) $this->user_type === self::TYPE_ADMIN_MASTER;
    }

    public function isAdminEditor(): bool
    {
        return (int) $this->user_type === self::TYPE_ADMIN_EDITOR;
    }

    public function canAccessAdminRoute(?string $routeName): bool
    {
        if ($this->isMasterAdmin()) {
            return true;
        }

        if (! $this->isAdminEditor() || ! $routeName) {
            return false;
        }

        return Str::is([
            'admin.index',
            'admin.overview',
            'admin.reports.download',
            'admin.products.*',
            'admin.produto.*',
            'admin.brands.*',
            'admin.categories.*',
            'admin.subcategories.*',
            'admin.categorias-filhas.*',
            'admin.blogs.*',
            'admin.blog-categories.*',
            'admin.contatos.*',
            'admin.contacts.*',
            'admin.policies.*',
            'admin.palace.*',
            'admin.bridal.*',
            'admin.cafe_bistro.*',
            'admin.institucional.*',
            'admin.notifications.*',
            'admin.image.*',
        ], $routeName);
    }

    public function getUserRoleAttribute()
    {
        return match ((int) $this->user_type) {
            self::TYPE_ADMIN_MASTER => __('messages.admin_master'),
            self::TYPE_ADMIN_EDITOR => __('messages.admin_editor'),
            self::TYPE_CUSTOMER => __('messages.usuario_comum'),
            self::TYPE_COURSE => __('messages.usuario_curso'),
            default => __('messages.usuario_desconhecido'),
        };
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class)->orderByDesc('is_default')->latest();
    }

    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true);
    }

    public function adminNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function cart()
    {
        return $this->hasMany(\App\Models\Cart::class);
    }

    public function abandonedCarts()
    {
        return $this->hasMany(\App\Models\AbandonedCart::class);
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
