<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class ProductAiCatalogFilters
{
    public static function rules(array $input): array
    {
        return [
            'publication' => ['nullable', 'in:active,inactive'],
            'stock_filter' => ['nullable', 'in:in_stock,out_of_stock'],
            'stock_min' => ['nullable', 'integer', 'min:0'],
            'stock_max' => ['nullable', 'integer', 'min:0', ...(filled($input['stock_min'] ?? null) ? ['gte:stock_min'] : [])],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0', ...(filled($input['price_min'] ?? null) ? ['gte:price_min'] : [])],
            'description_filter' => ['nullable', 'in:with,without'],
            'reference_filter' => ['nullable', 'in:with,without'],
            'editing_filter' => ['nullable', 'in:never_edited,edited'],
            'image_filter' => ['nullable', 'in:with,without'],
            'content_filter' => ['nullable', 'in:complete,incomplete'],
            'outlet_filter' => ['nullable', 'in:outlet,regular'],
            'product_type' => ['nullable', 'in:parent,child'],
            'views_min' => ['nullable', 'integer', 'min:0'],
            'created_from' => ['nullable', 'date_format:Y-m-d'],
            'created_to' => ['nullable', 'date_format:Y-m-d', ...(filled($input['created_from'] ?? null) ? ['after_or_equal:created_from'] : [])],
            'sort_by' => ['nullable', 'in:priority,unedited_latest,most_viewed,latest,oldest,price_low,price_high,name_az,name_za,stock_low,stock_high'],
        ];
    }

    public function apply(Builder $query, array $filters): void
    {
        if ($value = $filters['publication'] ?? null) {
            $query->where('status', $value === 'active' ? 1 : 0);
        }
        if ($value = $filters['stock_filter'] ?? null) {
            $value === 'in_stock'
                ? $query->where('stock', '>', 0)
                : $query->where(fn ($q) => $q->whereNull('stock')->orWhere('stock', '<=', 0));
        }
        foreach (['stock', 'price'] as $column) {
            foreach (['min' => '>=', 'max' => '<='] as $suffix => $operator) {
                if (isset($filters[$column.'_'.$suffix])) {
                    $query->where($column, $operator, $filters[$column.'_'.$suffix]);
                }
            }
        }
        foreach (['description_filter' => 'description', 'reference_filter' => 'ref_code'] as $filter => $column) {
            if ($value = $filters[$filter] ?? null) {
                $value === 'with'
                    ? $query->whereNotNull($column)->whereRaw("TRIM({$column}) != ''")
                    : $query->where(fn ($q) => $q->whereNull($column)->orWhereRaw("TRIM({$column}) = ''"));
            }
        }
        if ($value = $filters['editing_filter'] ?? null) {
            $value === 'never_edited'
                ? $query->whereNull('admin_edited_at')
                : $query->whereNotNull('admin_edited_at');
        }
        if ($value = $filters['image_filter'] ?? null) {
            $value === 'without'
                ? $this->whereMissingImage($query)
                : $this->whereHasImageReference($query);
        }
        if ($value = $filters['content_filter'] ?? null) {
            $value === 'incomplete'
                ? $query->where(function (Builder $incomplete) {
                    $incomplete
                        ->where(function (Builder $name) {
                            $name->where(fn (Builder $q) => $q->whereNull('name')->orWhereRaw("TRIM(name) = ''"))
                                ->where(fn (Builder $q) => $q->whereNull('external_name')->orWhereRaw("TRIM(external_name) = ''"));
                        })
                        ->orWhere(fn (Builder $q) => $q->whereNull('description')->orWhereRaw("TRIM(description) = ''"))
                        ->orWhere(fn (Builder $q) => $this->whereMissingImage($q));
                })
                : $query
                    ->where(function (Builder $name) {
                        $name->whereNotNull('name')->whereRaw("TRIM(name) != ''")
                            ->orWhere(fn (Builder $q) => $q->whereNotNull('external_name')->whereRaw("TRIM(external_name) != ''"));
                    })
                    ->whereNotNull('description')->whereRaw("TRIM(description) != ''")
                    ->where(fn (Builder $q) => $this->whereHasImageReference($q));
        }
        if ($value = $filters['outlet_filter'] ?? null) {
            $query->where('is_outlet', $value === 'outlet');
        }
        if ($value = $filters['product_type'] ?? null) {
            $value === 'child' ? $query->whereNotNull('parent_id') : $query->whereNull('parent_id');
        }
        if (isset($filters['views_min'])) {
            $query->where('views', '>=', $filters['views_min']);
        }
        if ($value = $filters['created_from'] ?? null) {
            $query->where('created_at', '>=', $value.' 00:00:00');
        }
        if ($value = $filters['created_to'] ?? null) {
            $query->where('created_at', '<', \Carbon\Carbon::parse($value)->addDay()->startOfDay());
        }

        $sort = $filters['sort_by'] ?? 'priority';
        match ($sort) {
            'price_low', 'price_high' => $query->orderBy('price', $sort === 'price_low' ? 'asc' : 'desc'),
            'stock_low', 'stock_high' => $query->orderBy('stock', $sort === 'stock_low' ? 'asc' : 'desc'),
            'name_az', 'name_za' => $query->orderByRaw("COALESCE(NULLIF(name, ''), external_name) ".($sort === 'name_az' ? 'asc' : 'desc')),
            'most_viewed' => $query->orderByDesc('views')->orderByDesc('created_at'),
            'unedited_latest' => $query->orderByRaw('admin_edited_at IS NULL DESC')->orderByDesc('created_at'),
            'priority' => $query
                ->orderByRaw('admin_edited_at IS NULL DESC')
                ->orderByDesc('views')
                ->orderByDesc('created_at'),
            default => $query->orderByRaw('created_at IS NULL ASC')->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc'),
        };
        $query->orderBy('id', $sort === 'oldest' ? 'asc' : 'desc');
    }

    private function whereMissingImage(Builder $query): Builder
    {
        return $query
            ->where(fn (Builder $photo) => $photo->whereNull('photo')->orWhereRaw("TRIM(photo) = ''"))
            ->where(fn (Builder $gallery) => $gallery->whereNull('gallery')->orWhereRaw("TRIM(gallery) IN ('', '[]', 'null')"));
    }

    private function whereHasImageReference(Builder $query): Builder
    {
        return $query->where(function (Builder $image) {
            $image
                ->where(fn (Builder $photo) => $photo->whereNotNull('photo')->whereRaw("TRIM(photo) != ''"))
                ->orWhere(fn (Builder $gallery) => $gallery->whereNotNull('gallery')->whereRaw("TRIM(gallery) NOT IN ('', '[]', 'null')"));
        });
    }
}
