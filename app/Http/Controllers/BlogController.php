<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Blog;
use App\Models\BlogCategory;

class BlogController extends Controller
{
    // Lista blogs ativos com filtro, busca e categorias
    public function index(Request $request)
    {
        $categories = BlogCategory::orderBy('name')->get();

        $query = Blog::where('is_active', true)
                     ->whereNotNull('published_at')
                     ->latest();

        // Filtra por categoria
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Busca por título
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $blogs = $query->paginate(10)->withQueryString();

        return view('blogs.index', compact('blogs', 'categories'));
    }

    // Mostra blog específico
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)
                    ->where('is_active', true)
                    ->firstOrFail();

        return view('blogs.show', compact('blog'));
    }
}
