<?php

namespace App\Http\Controllers;

use App\Models\Blog;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::where('is_active', true)
                    ->whereNotNull('published_at')
                    ->orderByDesc('published_at')
                    ->paginate(10);

        return view('blogs.index', compact('blogs'));
    }

    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return view('blogs.show', compact('blog'));
    }
}
