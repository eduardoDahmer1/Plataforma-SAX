<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    // Define o nome da tabela (opcional, se a migration seguiu o padrão)
    protected $table = 'languages';

    // Campos que podem ser preenchidos em massa (Mass Assignment)
    protected $fillable = [
        'key',
        'pt',
        'en',
        'es'
    ];

    /**
     * Helper para buscar a tradução baseada no idioma ativo no site.
     * Uso: Language::getTranslation('vendas')
     */
    public static function getTranslation($key)
    {
        $locale = app()->getLocale();
        
        // Ajuste caso seu locale padrão seja 'pt_BR' mas a coluna no banco seja 'pt'
        $column = ($locale == 'pt_BR') ? 'pt' : $locale;

        $translation = self::where('key', $key)->first();

        return $translation ? $translation->$column : $key;
    }
}