<?php

namespace App\Services;

use Illuminate\Support\Str;

class CategoryDisplayService
{
    // Há registros com nome nulo no banco; sem isso qualquer tela que os liste quebra.
    public function formatName(?string $name, ?string $slug = null): string
    {
        $name = (string) $name;
        $checkSlug = $slug ?? Str::slug($name);

        if ($checkSlug === 'perfumes-and-cosmeticos') {
            return 'Perfumes';
        }

        if ($checkSlug === 'edition-privee') {
            return mb_strtolower($name, 'UTF-8');
        }

        return $name;
    }

    public function format($entity)
    {
        if (!$entity) {
            return $entity;
        }

        $entity->name = $this->formatName($entity->name, $entity->slug ?? null);

        return $entity;
    }

    public function formatTree($entity)
    {
        if (!$entity) {
            return $entity;
        }

        $this->format($entity);

        if ($entity->relationLoaded('subcategories')) {
            foreach ($entity->subcategories as $sub) {
                $this->format($sub);
                
                if ($sub->relationLoaded('categoriasfilhas')) {
                    foreach ($sub->categoriasfilhas as $child) {
                        $this->format($child);
                    }
                }
            }
        }

        if ($entity->relationLoaded('category') && $entity->category) {
            $this->format($entity->category);
        }

        if ($entity->relationLoaded('subcategory') && $entity->subcategory) {
            $this->format($entity->subcategory);
            if ($entity->subcategory->relationLoaded('category') && $entity->subcategory->category) {
                $this->format($entity->subcategory->category);
            }
        }

        return $entity;
    }

    public function formatPagination($paginator)
    {
        $paginator->getCollection()->transform(function ($item) {
            return $this->formatTree($item);
        });

        return $paginator;
    }
}