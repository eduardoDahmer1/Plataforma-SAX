<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Blog;
use App\Models\BlogCategory;

class BlogController extends Controller
{
    private function weekSeed(): string
    {
        return now()->startOfWeek()->format('oW');
    }

    private function cacheVersion(): int
    {
        return (int) Cache::get('blogs_cache_version', 1);
    }

    private function blogsIndexQuery(string $search, $category)
    {
        $weekSeed = $this->weekSeed();

        return Blog::query()
            ->select('id', 'title', 'subtitle', 'slug', 'image', 'read_time', 'featured', 'published_at', 'category_id')
            ->with('category:id,name,slug')
            ->published()
            ->when($category, fn ($query) => $query->where('category_id', $category))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%");
                });
            })
            ->when($search === '' && !$category, function ($query) use ($weekSeed) {
                $query->reorder()
                    ->orderByDesc('featured')
                    ->orderByRaw('CRC32(CONCAT(id, ?))', [$weekSeed])
                    ->orderByDesc('published_at');
            }, function ($query) {
                $query->reorder()->orderByDesc('published_at');
            });
    }

    private function featuredFromPaginator($blogs, string $search, $category)
    {
        if ($blogs->currentPage() !== 1 || $search !== '' || $category) {
            return null;
        }

        return $blogs->getCollection()->firstWhere('featured', true)
            ?? $blogs->getCollection()->first();
    }

    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->input('search', '')), 0, 120);
        $category = ctype_digit((string) $request->input('category'))
            ? (int) $request->input('category')
            : null;
        $page = max(1, min(500, (int) $request->input('page', 1)));

        $categories = Cache::remember('blog_categories_list', now()->addHours(6), function () {
            return BlogCategory::query()
                ->select('id', 'name', 'slug', 'banner')
                ->orderBy('name')
                ->get();
        });

        $cacheKey = 'blogs_index_'.$this->cacheVersion().'_'.sha1((string) json_encode([
            $search, $category, $page, $this->weekSeed(),
        ]));

        $blogs = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($search, $category, $page) {
            return $this->blogsIndexQuery($search, $category)
                ->paginate(9, ['*'], 'page', $page)
                ->withQueryString();
        });

        $featuredBlog = $this->featuredFromPaginator($blogs, $search, $category);

        return view('blogs.index', [
            'blogs' => $blogs,
            'categories' => $categories,
            'currentCategory' => $category,
            'search' => $search,
            'featuredBlog' => $featuredBlog,
        ]);
    }

    public function ajaxSearch(Request $request)
    {
        $search = mb_substr(trim((string) $request->input('search', '')), 0, 120);
        $category = ctype_digit((string) $request->input('category'))
            ? (int) $request->input('category')
            : null;
        $page = max(1, min(500, (int) $request->input('page', 1)));

        $cacheKey = 'blogs_ajax_'.$this->cacheVersion().'_'.sha1((string) json_encode([
            $search, $category, $page, $this->weekSeed(),
        ]));

        $blogs = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($search, $category, $page) {
            return $this->blogsIndexQuery($search, $category)
                ->paginate(9, ['*'], 'page', $page)
                ->withQueryString();
        });

        $featuredBlog = $this->featuredFromPaginator($blogs, $search, $category);
        $hasFilters = $search !== '' || filled($category);

        return response()->json([
            'featured' => view('blogs.partials.featured', compact('featuredBlog', 'hasFilters'))->render(),
            'cards' => view('blogs.partials.cards', compact('blogs', 'featuredBlog', 'hasFilters'))->render(),
            'pagination' => view('blogs.partials.pagination', compact('blogs'))->render(),
            'total' => $blogs->total(),
            'search' => $search,
        ]);
    }

    public function show(string $slug)
    {
        $blog = Cache::remember("blog_show_{$slug}", now()->addMinutes(30), function () use ($slug) {
            return Blog::query()
                ->select('id', 'title', 'subtitle', 'slug', 'image', 'image_caption', 'gallery', 'content', 'meta_description', 'read_time', 'featured', 'published_at', 'author', 'category_id')
                ->with('category:id,name,slug')
                ->published()
                ->where('slug', $slug)
                ->firstOrFail();
        });

        $relatedPosts = Cache::remember("blog_related_{$blog->id}", now()->addMinutes(20), function () use ($blog) {
            $related = Blog::query()
                ->select('id', 'title', 'subtitle', 'slug', 'image', 'read_time', 'published_at', 'category_id')
                ->with('category:id,name,slug')
                ->published()
                ->whereKeyNot($blog->id)
                ->where('category_id', $blog->category_id)
                ->latest('published_at')
                ->limit(3)
                ->get();

            if ($related->count() < 3) {
                $more = Blog::query()
                    ->select('id', 'title', 'subtitle', 'slug', 'image', 'read_time', 'published_at', 'category_id')
                    ->with('category:id,name,slug')
                    ->published()
                    ->whereKeyNot($blog->id)
                    ->whereNotIn('id', $related->pluck('id'))
                    ->latest('published_at')
                    ->limit(3 - $related->count())
                    ->get();

                $related = $related->concat($more);
            }

            return $related;
        });

        $latestPosts = Cache::remember("blog_latest_posts_article_{$blog->id}", now()->addMinutes(15), function () use ($blog) {
            return Blog::query()
                ->select('id', 'title', 'subtitle', 'slug', 'image', 'read_time', 'published_at', 'category_id')
                ->with('category:id,name,slug')
                ->published()
                ->whereKeyNot($blog->id)
                ->latest('published_at')
                ->limit(5)
                ->get();
        });

        $articleContent = app(\App\Services\BlogProductCarouselService::class)->render($blog->content);

        return view('blogs.show', compact('blog', 'relatedPosts', 'latestPosts', 'articleContent'));
    }
}
