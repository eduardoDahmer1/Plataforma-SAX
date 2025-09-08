<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $page   = $request->get('page', 1);
        $search = $request->get('search', '');

        $cacheKey = "subcategories_index_{$page}_" . md5($search);

        $subcategories = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search) {
            $query = Subcategory::with('category')->orderBy('name');

            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            return $query->paginate(20)->withQueryString();
        });

        return view('subcategories.index', compact('subcategories'));
    }

    public function show($identifier)
    {
        $cacheKey = "subcategory_show_{$identifier}";

        $subcategory = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($identifier) {
            return Subcategory::with(['category', 'childcategories'])
                ->when(is_numeric($identifier), fn($q) => $q->where('id', $identifier))
                ->when(!is_numeric($identifier), fn($q) => $q->where('slug', $identifier))
                ->firstOrFail();
        });

        return view('subcategories.show', compact('subcategory'));
    }
}
