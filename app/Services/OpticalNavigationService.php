<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class OpticalNavigationService
{
    public const CACHE_KEY = 'storefront.optical_navigation.v4';

    public function __construct(private readonly StoreTaxonomyService $taxonomy)
    {
    }

    public function tree(): Collection
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(15), function (): Collection {
            $categories = $this->taxonomy->applyOpticalCategoryConstraint(Category::query())
                ->where('status', 1)
                ->with([
                    'subcategories' => fn ($query) => $query
                        ->orderBy('name')
                        ->with(['categoriasfilhas' => fn ($childQuery) => $childQuery->orderBy('name')]),
                ])
                ->orderBy('name')
                ->get();

            $categoryIds = $categories->pluck('id');
            $products = $categoryIds->isEmpty()
                ? collect()
                : VisibleCatalogProductsService::builder()
                    ->select([
                        'products.id',
                        'products.category_id',
                        'products.subcategory_id',
                        'products.childcategory_id',
                        'products.photo',
                        'products.updated_at',
                    ])
                    ->whereIn('products.category_id', $categoryIds)
                    ->latest('products.updated_at')
                    ->get();

            $categoryProductImages = $products->groupBy('category_id')
                ->map(fn (Collection $items): ?string => ($items->skip(1)->first() ?: $items->first())?->photo_url);
            $subcategoryProductImages = $products->whereNotNull('subcategory_id')->groupBy('subcategory_id')
                ->map(fn (Collection $items): ?string => $items->first()?->photo_url);
            $childCategoryProductImages = $products->whereNotNull('childcategory_id')->groupBy('childcategory_id')
                ->map(fn (Collection $items): ?string => $items->first()?->photo_url);

            return $categories->map(function (Category $category) use ($categoryProductImages, $subcategoryProductImages, $childCategoryProductImages): array {
                $subcategories = $category->subcategories
                    ->filter(fn ($subcategory): bool => filled($subcategory->name) && filled($subcategory->slug))
                    ->map(function ($subcategory) use ($subcategoryProductImages, $childCategoryProductImages): array {
                        $children = $subcategory->categoriasfilhas
                            ->filter(fn ($child): bool => filled($child->name) && filled($child->slug))
                            ->map(fn ($child): array => [
                                'type' => 'childcategory',
                                'id' => $child->id,
                                'label' => $child->name,
                                'url' => route('categorias-filhas.show', $child->slug),
                                'photo' => $childCategoryProductImages->get($child->id) ?: $this->imageUrl($child->photo),
                                'banner' => $this->imageUrl($child->banner)
                                    ?: $childCategoryProductImages->get($child->id)
                                    ?: $this->imageUrl($child->photo),
                                'children' => collect(),
                            ])
                            ->values();

                        return [
                            'type' => 'subcategory',
                            'id' => $subcategory->id,
                            'label' => $subcategory->name,
                            'url' => route('subcategories.show', $subcategory->slug),
                            'photo' => $subcategoryProductImages->get($subcategory->id) ?: $this->imageUrl($subcategory->photo),
                            'banner' => $this->imageUrl($subcategory->banner)
                                ?: $subcategoryProductImages->get($subcategory->id)
                                ?: $this->imageUrl($subcategory->photo),
                            'children' => $children,
                        ];
                    })
                    ->values();

                return [
                    'type' => 'category',
                    'id' => $category->id,
                    'label' => $category->name,
                    'url' => route('categories.show', $category->slug ?: $category->id),
                    'photo' => $categoryProductImages->get($category->id) ?: $this->imageUrl($category->photo),
                    'banner' => $this->imageUrl($category->banner)
                        ?: $categoryProductImages->get($category->id)
                        ?: $this->imageUrl($category->photo),
                    'children' => $subcategories,
                ];
            })->values();
        });
    }

    public function items(): Collection
    {
        return $this->tree()->flatMap(function (array $category): Collection {
            $subcategories = collect($category['children']);
            $children = $subcategories->flatMap(fn (array $subcategory) => $subcategory['children']);

            return collect([$category])
                ->concat($subcategories)
                ->concat($children);
        })->map(function (array $item): array {
            unset($item['children']);

            return $item;
        })->unique(fn (array $item): string => $item['type'].'-'.$item['id'])->values();
    }

    public function clear(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function imageUrl(?string $photo): ?string
    {
        if (! filled($photo)) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $photo)) {
            return $photo;
        }

        return asset('storage/'.preg_replace('#^storage/#i', '', ltrim($photo, '/')));
    }
}
