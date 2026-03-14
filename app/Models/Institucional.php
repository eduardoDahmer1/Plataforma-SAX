<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institucional extends Model
{
    use HasFactory;

    /**
     * Define explicitamente a tabela associada à model.
     * Necessário porque o nome da tabela é singular ('institucional').
     */
    protected $table = 'institucional';

    /**
     * Atributos que podem ser preenchidos em massa (Mass Assignment).
     * Inclui todos os campos originais e os novos campos de iframes.
     */
    protected $fillable = [
        'top_sliders',
        'section_one_title',
        'section_one_content',
        'section_one_image',
        'brand_logos',
        'gallery_images',
        'text_section_one_title',
        'text_section_one_body',
        'text_section_two_title',
        'text_section_two_body',
        'text_section_three_title',
        'text_section_three_body',
        'stat_brands_count',
        'stat_sqm_count',
        'stat_employees_count',
        // Novos campos adicionados via migration
        'iframe_tour_360',
        'iframe_ponte_amizade',
        'iframe_centro_cde',
    ];

    /**
     * Conversão de tipos (Casting).
     * Transforma automaticamente as strings JSON do banco em Arrays do PHP.
     */
    protected $casts = [
        'top_sliders'    => 'array',
        'brand_logos'    => 'array',
        'gallery_images' => 'array',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];
}