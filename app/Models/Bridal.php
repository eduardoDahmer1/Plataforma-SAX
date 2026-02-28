<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bridal extends Model
{
    use HasFactory;

    protected $fillable = [
        // Básicos
        'title', 'is_active',

        // SEO
        'meta_title', 'meta_description',

        // Sección 01: Hero
        'hero_title', 'hero_subtitle', 'hero_description', 'hero_image',

        // Sección 02: Brand Ticker
        'brands',

        // Sección 03: Promo Carousel
        'promos',

        // Sección 04: Servicios
        'services_label', 'services_title',
        'services_cta_text', 'services_cta_link',
        'services',

        // Sección 05: Palace Banner
        'palace_image', 'palace_subtitle', 'palace_title',
        'palace_description', 'palace_link',

        // Sección 06: Testimonios
        'testimonials_label', 'testimonials_title', 'testimonials',

        // Sección 07: Instagram CTA
        'social_instagram',

        // Sección 08: Contacto / Sucursales
        'locations', 
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'brands'       => 'array',
        'promos'       => 'array',
        'services'     => 'array',
        'testimonials' => 'array',
        'locations'    => 'array',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];
}
