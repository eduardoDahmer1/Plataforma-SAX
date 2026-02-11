<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Palace extends Model
{
    use HasFactory;

    // Nome da tabela (opcional se seguir o padrão Laravel, mas bom garantir)
    protected $table = 'palaces';

    protected $fillable = [
        // SEÇÃO 01: HERO
        'hero_titulo',
        'hero_descricao',
        'hero_imagem',

        // SEÇÃO 02: SOMMELIER / BAR
        'bar_titulo',
        'bar_descricao',
        'bar_imagem_1',
        'bar_imagem_2',
        'bar_imagem_3',

        // SEÇÃO 03: EVENTOS
        'eventos_titulo',
        'eventos_descricao',
        'eventos_galeria',

        // SEÇÃO 04: NOITE ÁRABE
        'tematica_tag',
        'tematica_titulo',
        'tematica_descricao',
        'tematica_preco',
        'tematica_imagem',

        // SEÇÃO 05: GASTRONOMIA
        'gastronomia_titulo',
        'gastronomia_cafe_desc',
        'gastronomia_almoco_desc',
        'gastronomia_jantar_desc',

        // SEÇÃO 06: LOCALIZAÇÃO
        'contato_endereco',
        'contato_horario_segunda',
        'contato_horario_sabado',
        'contato_horario_domingo',
        'contato_whatsapp',
        'contato_mapa_iframe',
    ];

    /**
     * Casts automáticos: 
     * O 'array' faz o Laravel transformar o JSON do banco em Array no PHP automaticamente.
     */
    protected $casts = [
        'eventos_galeria' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Helper para limpar o número do WhatsApp (Remove espaços, +, parênteses)
     * Uso no Blade: {{ $palace->whatsapp_link }}
     */
    public function getWhatsappLinkLabelAttribute()
    {
        return preg_replace('/\D/', '', $this->contato_whatsapp);
    }

    /**
     * Helper para garantir que o Mapa nunca venha quebrado se estiver nulo
     */
    public function getHasMapaAttribute()
    {
        return !empty($this->contato_mapa_iframe) && str_contains($this->contato_mapa_iframe, '<iframe');
    }

    /**
     * Opcional: Se você quiser que o Laravel delete as fotos antigas do storage 
     * quando atualizar por novas, isso seria feito no Controller.
     */
}