<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class StoreTaxonomyService
{
    private const OPTICAL_TERMS = [
        'optico',
        'otico',
        'otica',
        'oculo',
        'lente',
        'gafa',
        'eyewear',
        'optical',
    ];

    public function __construct(private readonly StoreControlService $storeControl)
    {
    }

    public function categories(Builder $query): Builder
    {
        if (! $this->storeControl->isOtica()) {
            return $query;
        }

        return $this->applyOpticalCategoryConstraint($query);
    }

    public function subcategories(Builder $query): Builder
    {
        if (! $this->storeControl->isOtica()) {
            return $query;
        }

        return $query->whereHas('category', fn (Builder $category) =>
            $this->applyOpticalCategoryConstraint($category)
        );
    }

    public function childCategories(Builder $query): Builder
    {
        if (! $this->storeControl->isOtica()) {
            return $query;
        }

        return $query->where(function (Builder $scope): void {
            $scope
                ->whereHas('category', fn (Builder $category) =>
                    $this->applyOpticalCategoryConstraint($category)
                )
                ->orWhereHas('subcategory.category', fn (Builder $category) =>
                    $this->applyOpticalCategoryConstraint($category)
                );
        });
    }

    public function applyOpticalCategoryConstraint(Builder $query): Builder
    {
        return $query->where(function (Builder $scope): void {
            $nameColumn = $scope->getModel()->qualifyColumn('name');
            $slugColumn = $scope->getModel()->qualifyColumn('slug');

            foreach (self::OPTICAL_TERMS as $term) {
                $like = '%' . $term . '%';
                $scope->orWhereRaw("LOWER({$nameColumn}) LIKE ?", [$like])
                    ->orWhereRaw("LOWER({$slugColumn}) LIKE ?", [$like]);
            }
        });
    }

    /**
     * Produtos ópticos são definidos pela mesma regra exata usada pelo
     * integrador. Evita dezenas de LIKE/EXISTS em toda consulta de produto da
     * Ótica e impede que palavras soltas no nome liberem itens de outra área.
     */
    public function applyOpticalProductConstraint(Builder $query): Builder
    {
        $reference = trim((string) config('catalog-sync.optical_category_ref_code'));
        $slugs = array_values(array_filter(array_map(
            static fn ($slug): string => mb_strtolower(trim((string) $slug)),
            (array) config('catalog-sync.optical_category_slugs', []),
        )));

        return $query->whereHas('category', function (Builder $category) use ($reference, $slugs): void {
            $category->where(function (Builder $identity) use ($reference, $slugs): void {
                if ($reference !== '') {
                    $identity->where('ref_code', $reference);
                }

                if ($slugs !== []) {
                    $method = $reference !== '' ? 'orWhereIn' : 'whereIn';
                    $identity->{$method}(\DB::raw('LOWER(slug)'), $slugs);
                }
            });
        });
    }
}
