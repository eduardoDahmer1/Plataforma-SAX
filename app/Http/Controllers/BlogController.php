<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Blog;
use App\Models\BlogCategory;

class BlogController extends Controller
{
    // Lista blogs ativos com filtro, busca, categorias e cache
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $category = $request->input('category', '');
        $page = $request->input('page', 1);

        $cacheKey = "blogs_index_{$page}_" . md5($search . $category);

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search, $category) {
            $categories = BlogCategory::orderBy('name')->get();

            $query = Blog::where('is_active', true)
                         ->whereNotNull('published_at')
                         ->latest();

            if (!empty($category)) {
                $query->where('category_id', $category);
            }

            if (!empty($search)) {
                $query->where('title', 'like', "%{$search}%");
            }

            $blogs = $query->paginate(10)->withQueryString();

            return compact('blogs', 'categories');
        });

        return view('blogs.index', $data);
    }

    // Mostra blog específico com cache
    public function show($slug)
    {
        $cacheKey = "blog_show_{$slug}";

        $blog = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($slug) {
            return Blog::where('slug', $slug)
                       ->where('is_active', true)
                       ->firstOrFail();
        });

        return view('blogs.show', compact('blog'));
    }
}
