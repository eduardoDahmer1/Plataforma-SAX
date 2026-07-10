<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlugRedirect extends Model
{
    protected $fillable = ['model', 'old_slug', 'entity_id'];

    // Resolve a URL atual pra onde uma slug antiga (que já não existe mais) deveria redirecionar.
    public static function resolveUrl(string $model, string $oldSlug): ?string
    {
        $redirect = static::where('model', $model)->where('old_slug', $oldSlug)->first();

        if (!$redirect) {
            return null;
        }

        $entity = match ($model) {
            'category' => Category::find($redirect->entity_id),
            'subcategory' => Subcategory::find($redirect->entity_id),
            'categoria_filha' => CategoriasFilhas::find($redirect->entity_id),
            'brand' => Brand::find($redirect->entity_id),
            default => null,
        };

        if (!$entity) {
            return null;
        }

        return match ($model) {
            'category' => route('categories.show', $entity->slug),
            'subcategory' => route('subcategories.show', $entity->slug),
            'categoria_filha' => route('categorias-filhas.show', $entity->slug),
            'brand' => route('brands.show', $entity->slug),
            default => null,
        };
    }
}
