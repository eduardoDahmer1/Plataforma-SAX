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
}
