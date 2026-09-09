<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'ai_enabled',
        'ai_key_source',
        'ai_api_key',
        'maintenance',
        'store_profile',
        'cart_enabled',
        'checkout_enabled',
        'add_to_cart_enabled',
        'deposit_enabled',
        'bancard_enabled',
        'pix_enabled',
        'whatsapp_enabled',
        'geonames_enabled',
        'header_categories_enabled',
        'header_institucional_enabled',
        'header_bridal_enabled',
        'header_palace_enabled',
        'header_cafe_enabled',
        'header_blog_enabled',
        'header_contact_enabled',
        'header_guide_enabled',
        'footer_categories_enabled',
        'footer_institucional_enabled',
        'footer_bridal_enabled',
        'footer_palace_enabled',
        'footer_cafe_enabled',
        'footer_blog_enabled',
        'footer_contact_enabled',
        'footer_guide_enabled',
    ];

    protected $casts = [
        'ai_enabled' => 'boolean',
        'ai_api_key' => 'encrypted',
        'store_profile' => 'string',
        'cart_enabled' => 'boolean',
        'checkout_enabled' => 'boolean',
        'add_to_cart_enabled' => 'boolean',
        'deposit_enabled' => 'boolean',
        'bancard_enabled' => 'boolean',
        'pix_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'geonames_enabled' => 'boolean',
        'header_categories_enabled' => 'boolean',
        'header_institucional_enabled' => 'boolean',
        'header_bridal_enabled' => 'boolean',
        'header_palace_enabled' => 'boolean',
        'header_cafe_enabled' => 'boolean',
        'header_blog_enabled' => 'boolean',
        'header_contact_enabled' => 'boolean',
        'header_guide_enabled' => 'boolean',
        'footer_categories_enabled' => 'boolean',
        'footer_institucional_enabled' => 'boolean',
        'footer_bridal_enabled' => 'boolean',
        'footer_palace_enabled' => 'boolean',
        'footer_cafe_enabled' => 'boolean',
        'footer_blog_enabled' => 'boolean',
        'footer_contact_enabled' => 'boolean',
        'footer_guide_enabled' => 'boolean',
    ];

    protected $hidden = ['ai_api_key'];
}
