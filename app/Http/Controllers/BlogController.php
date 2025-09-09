<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Attribute;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $category = $request->input('category', '');
        $page = $request->input('page', 1);

        $cacheKey = "blogs_index_{$page}_" . md5($search . $category);

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search, $category) {
            $categories = BlogCategory::orderBy('name')->get();

            $query = Blog::with('category')
                         ->where('is_active', true)
                         ->whereNotNull('published_at')
                         ->latest();

            if ($category) {
                $query->where('category_id', $category);
            }

            if ($search) {
                $query->where('title', 'like', "%{$search}%");
            }

            $blogs = $query->paginate(10)->withQueryString();

            return [
                'blogs' => $blogs,
                'categories' => $categories,
            ];
        });

        // Pega banner do banco, usando a tabela attributes
        $attribute = Attribute::first();
        $blogBanner = null;
        if ($attribute?->banner1 && Storage::disk('public')->exists($attribute->banner1)) {
            $blogBanner = asset('storage/' . $attribute->banner1);
        }

        return view('blogs.index', [
            'blogs' => $data['blogs'],
            'categories' => $data['categories'],
            'currentCategory' => $category,
            'search' => $search,
            'blogBanner' => $blogBanner, // já como URL completa
        ]);
    } 

    // Mostra blog específico com cache
    public function show($slug)
    {
        $cacheKey = "blog_show_{$slug}";

        $blog = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($slug) {
            return Blog::with('category')
                       ->where('slug', $slug)
                       ->where('is_active', true)
                       ->firstOrFail();
        });

        return view('blogs.show', compact('blog'));
    }
}
