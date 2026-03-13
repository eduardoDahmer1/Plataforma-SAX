<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CafeBistro extends Model
{
    use HasFactory;

    protected $fillable = [
        // General
        'is_active',
        'whatsapp',
        'meta_title',
        'meta_description',

        // Hero
        'hero_imagen',
        'hero_titulo',
        'hero_subtitulo',

        // Sobre Nós
        'sobre_imagen',
        'sobre_titulo',
        'sobre_texto',

        // Cardápio
        'cardapio_titulo',
        'cardapio_subtitulo',
        'cardapio_pdf',
        'cardapio_galeria',

        // Eventos
        'eventos_titulo',
        'eventos_subtitulo',
        'eventos_texto',
        'eventos_tipos',
        'eventos_galeria',

        // Horários
        'horarios',

        // Contacto
        'direccion',
        'telefono',
        'instagram_url',
        'facebook_url',
        'mapa_embed',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'eventos_tipos'   => 'array',
        'eventos_galeria' => 'array',
        'cardapio_galeria' => 'array',
        'horarios'        => 'array',
    ];

    // Genera el link de WhatsApp listo para usar en href
    public function getWhatsappLinkAttribute(): string
    {
        return 'https://wa.me/' . preg_replace('/\D/', '', $this->whatsapp);
    }

    // Verifica si hay un embed de mapa cargado
    public function getHasMapaAttribute(): bool
    {
        return !empty($this->mapa_embed);
    }
}
