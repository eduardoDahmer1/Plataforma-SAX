<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;
use App\Services\StoreTaxonomyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductAiTaxonomyController extends Controller
{
    public function store(Request $request, StoreTaxonomyService $taxonomy)
    {
        $data = $request->validate([
            'type' => 'required|in:subcategory,childcategory',
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'subcategory_id' => 'nullable|integer|required_if:type,childcategory',
        ]);
        $data['name'] = trim($data['name']);
        if ($data['name'] === '' || Str::slug($data['name']) === '') {
            throw ValidationException::withMessages(['name' => 'Ingresá un nombre válido.']);
        }
        $taxonomy->categories(Category::query())->findOrFail($data['category_id']);
        if ($data['type'] === 'childcategory') {
            $taxonomy->subcategories(Subcategory::query())->where('category_id', $data['category_id'])->findOrFail($data['subcategory_id']);
        }
        return DB::transaction(function () use ($data) {
            // Serialize creation under this parent, including duplicate checks.
            Category::whereKey($data['category_id'])->lockForUpdate()->firstOrFail();
            $model = $data['type'] === 'subcategory' ? new Subcategory : new CategoriasFilhas;
            $query = $model->newQuery()->where('category_id', $data['category_id']);
            if ($data['type'] === 'childcategory') $query->where('subcategory_id', $data['subcategory_id']);
            $base = Str::slug($data['name']);
            $existing = $query->get()->first(fn ($item) => Str::slug($item->getRawOriginal('name')) === $base || $item->slug === $base);
            if ($existing) return response()->json(['item' => $existing, 'type' => $data['type'], 'created' => false]);
            $slug = $base;
            for ($suffix = 2; $model->newQuery()->where('slug', $slug)->exists(); $suffix++) $slug = $base.'-'.$suffix;
            $attributes = ['name' => $data['name'], 'slug' => $slug, 'category_id' => $data['category_id']];
            if ($data['type'] === 'childcategory') $attributes['subcategory_id'] = $data['subcategory_id'];
            $item = $model->newQuery()->create($attributes);
            return response()->json(['item' => $item, 'type' => $data['type'], 'created' => true], 201);
        });
    }
}
