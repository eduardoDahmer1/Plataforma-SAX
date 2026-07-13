<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PageTranslation extends Model
{
    use HasFactory;

    /**
     * A tabela associada à model.
     *
     * @var string
     */
    protected $table = 'page_translations';

    /**
     * Os atributos que podem ser atribuídos em massa (Mass Assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_type',
        'page_id',
        'locale',

        // --- PALACES ---
        'palace_hero_titulo',
        'palace_hero_descricao',
        'palace_bar_titulo',
        'palace_bar_descricao',
        'palace_eventos_titulo',
        'palace_eventos_descricao',
        'palace_tematica_tag',
        'palace_tematica_titulo',
        'palace_tematica_descricao',
        'palace_tematica_preco',
        'palace_gastronomia_titulo',
        'palace_gastronomia_cafe_desc',
        'palace_gastronomia_almoco_desc',
        'palace_gastronomia_jantar_desc',
        'palace_contato_endereco',
        'palace_contato_horario_segunda',
        'palace_contato_horario_sabado',
        'palace_contato_horario_domingo',

        // --- BRIDALS ---
        'bridal_title',
        'bridal_meta_title',
        'bridal_meta_description',
        'bridal_hero_title',
        'bridal_hero_subtitle',
        'bridal_hero_description',
        'bridal_services_label',
        'bridal_services_title',
        'bridal_services_cta_text',
        'bridal_services',
        'bridal_promos',
        'bridal_palace_subtitle',
        'bridal_palace_title',
        'bridal_palace_description',
        'bridal_testimonials_label',
        'bridal_testimonials_title',
        'bridal_testimonials',
        'bridal_locations',

        // --- INSTITUCIONAL ---
        'inst_section_one_title',
        'inst_section_one_content',
        'inst_text_section_one_title',
        'inst_text_section_one_body',
        'inst_text_section_two_title',
        'inst_text_section_two_body',
        'inst_text_section_three_title',
        'inst_text_section_three_body',

        // --- CAFE_BISTROS ---
        'cafe_meta_title',
        'cafe_meta_description',
        'cafe_hero_titulo',
        'cafe_hero_subtitulo',
        'cafe_sobre_titulo',
        'cafe_sobre_texto',
        'cafe_cardapio_titulo',
        'cafe_cardapio_subtitulo',
        'cafe_eventos_titulo',
        'cafe_eventos_subtitulo',
        'cafe_eventos_texto',
        'cafe_eventos_tipos',
        'cafe_horarios',
        'cafe_direccion',
    ];

    public function page(): MorphTo
    {
        return $this->morphTo(null, 'page_type', 'page_id');
    }
}