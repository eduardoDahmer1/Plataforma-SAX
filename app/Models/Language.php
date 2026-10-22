<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        $used = static function (Language $language): bool {
            return \Illuminate\Support\Facades\Schema::hasTable('catalog_attributes') &&
                (\Illuminate\Support\Facades\DB::table('catalog_attributes')->where('language_id', $language->id)->exists()
                || \Illuminate\Support\Facades\DB::table('catalog_attribute_options')->where('language_id', $language->id)->exists());
        };
        static::updating(function (Language $language) use ($used) {
            if ($used($language) && ($language->isDirty('key') || blank($language->pt) || blank($language->es) || blank($language->en))) {
                throw \Illuminate\Validation\ValidationException::withMessages(['key' => 'Esta traducción pertenece al diccionario: conservá la clave y los tres idiomas.']);
            }
        });
        static::deleting(function (Language $language) use ($used) {
            if ($used($language)) throw \Illuminate\Validation\ValidationException::withMessages(['key' => 'Desactivá el atributo u opción; su traducción está en uso.']);
        });
        static::updated(function (Language $language) use ($used) {
            if ($used($language)) {
                foreach (['catalog_attributes', 'catalog_attribute_options'] as $table) {
                    \Illuminate\Support\Facades\DB::table($table)->where('language_id', $language->id)->update(['revision' => (string) \Illuminate\Support\Str::uuid(), 'updated_at' => now()->format('Y-m-d H:i:s.u')]);
                }
            }
        });
    }

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
