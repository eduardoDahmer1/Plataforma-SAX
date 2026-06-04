<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Institucional extends Model
{
    use HasFactory;

    protected $table = 'institucional';

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
        'iframe_tour_360',
        'iframe_ponte_amizade',
        'iframe_centro_cde',
    ];

    protected $casts = [
        'top_sliders'    => 'array',
        'brand_logos'    => 'array',
        'gallery_images' => 'array',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    /**
     * Relação polimórfica com a tabela de traduções.
     * Certifique-se de que o segundo parâmetro 'page' (o nome da relação) 
     * bata com a lógica de salvamento.
     */
    public function translations(): MorphMany
    {
        // O Laravel espera que, ao usar 'page', exista uma relação 'pageable' ou similar.
        // Como no seu banco a coluna se chama 'page_type' e 'page_id', 
        // usamos 'page' para que o Laravel busque por 'page_type' e 'page_id' automaticamente.
        return $this->morphMany(PageTranslation::class, 'page', 'page_type', 'page_id');
    }
}