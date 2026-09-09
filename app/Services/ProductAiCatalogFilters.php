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
            'outlet_filter' => ['nullable', 'in:outlet,regular'],
            'product_type' => ['nullable', 'in:parent,child'],
            'created_from' => ['nullable', 'date_format:Y-m-d'],
            'created_to' => ['nullable', 'date_format:Y-m-d', ...(filled($input['created_from'] ?? null) ? ['after_or_equal:created_from'] : [])],
            'sort_by' => ['nullable', 'in:latest,oldest,price_low,price_high,name_az,name_za,stock_low,stock_high'],
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
        if ($value = $filters['outlet_filter'] ?? null) {
            $query->where('is_outlet', $value === 'outlet');
        }
        if ($value = $filters['product_type'] ?? null) {
            $value === 'child' ? $query->whereNotNull('parent_id') : $query->whereNull('parent_id');
        }
        if ($value = $filters['created_from'] ?? null) {
            $query->where('created_at', '>=', $value.' 00:00:00');
        }
        if ($value = $filters['created_to'] ?? null) {
            $query->where('created_at', '<', \Carbon\Carbon::parse($value)->addDay()->startOfDay());
        }

        $sort = $filters['sort_by'] ?? 'latest';
        match ($sort) {
            'price_low', 'price_high' => $query->orderBy('price', $sort === 'price_low' ? 'asc' : 'desc'),
            'stock_low', 'stock_high' => $query->orderBy('stock', $sort === 'stock_low' ? 'asc' : 'desc'),
            'name_az', 'name_za' => $query->orderByRaw("COALESCE(NULLIF(name, ''), external_name) ".($sort === 'name_az' ? 'asc' : 'desc')),
            default => $query->orderByRaw('created_at IS NULL ASC')->orderBy('created_at', $sort === 'oldest' ? 'asc' : 'desc'),
        };
        $query->orderBy('id', $sort === 'oldest' ? 'asc' : 'desc');
    }
}
