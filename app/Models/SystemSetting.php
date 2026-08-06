<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'maintenance',
        'cart_enabled',
        'checkout_enabled',
        'add_to_cart_enabled',
        'deposit_enabled',
        'bancard_enabled',
        'pix_enabled',
        'whatsapp_enabled',
        'geonames_enabled',
    ];

    protected $casts = [
        'cart_enabled' => 'boolean',
        'checkout_enabled' => 'boolean',
        'add_to_cart_enabled' => 'boolean',
        'deposit_enabled' => 'boolean',
        'bancard_enabled' => 'boolean',
        'pix_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'geonames_enabled' => 'boolean',
    ];
}
